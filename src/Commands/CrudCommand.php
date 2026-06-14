<?php

namespace Repat\CliCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Repat\CliCrud\Authorization\Authorizer;
use Repat\CliCrud\Fields\Relations\BelongsTo;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Forms\FormBuilder;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Resources\ResourceRegistrar;
use Repat\CliCrud\Support\ColumnFormatter;
use Repat\CliCrud\Support\ColumnTypeMapper;
use Repat\CliCrud\Tables\TableRenderer;
use Repat\CliCrud\Views\AsciiArt;
use Repat\CliCrud\Views\DetailViewRenderer;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\datatable;
use function Laravel\Prompts\select;

class CrudCommand extends Command
{
    protected $signature = 'cli-crud';

    protected $description = 'Interactive CLI CRUD admin panel';

    protected ResourceRegistrar $registrar;

    protected Authorizer $authorizer;

    protected TableRenderer $tableRenderer;

    protected FormBuilder $formBuilder;

    protected DetailViewRenderer $detailViewRenderer;

    public function __construct(
        ResourceRegistrar $registrar,
        Authorizer $authorizer,
        TableRenderer $tableRenderer,
        FormBuilder $formBuilder,
        DetailViewRenderer $detailViewRenderer
    ) {
        parent::__construct();
        $this->registrar = $registrar;
        $this->authorizer = $authorizer;
        $this->tableRenderer = $tableRenderer;
        $this->formBuilder = $formBuilder;
        $this->detailViewRenderer = $detailViewRenderer;
    }

    public function handle(): int
    {
        $this->line(AsciiArt::render(config('app.name')));
        $this->line('');

        $resources = $this->getAuthorizedResources();

        if (empty($resources)) {
            $this->error('No resources available or you are not authorized to view any resources.');

            return self::FAILURE;
        }

        $this->showMainMenu($resources);

        return self::SUCCESS;
    }

    protected function getAuthorizedResources(): array
    {
        $resources = $this->registrar->getResources();
        $authorized = [];

        foreach ($resources as $resource) {
            if ($this->authorizer->viewAny(new $resource)) {
                $authorized[] = $resource;
            }
        }

        return $authorized;
    }

    protected function showMainMenu(array $resources): void
    {
        $options = [];
        foreach ($resources as $resource) {
            $options[$resource] = $resource::getLabel();
        }
        $options['quit'] = 'Quit';

        $selected = select(
            label: 'Select a resource',
            options: $options
        );

        if ($selected === 'quit') {
            exit(0);
        }

        $this->showResourceMenu($selected);
    }

    protected function showResourceMenu(string $resourceClass): void
    {
        $resource = new $resourceClass;

        $options = [
            'list' => "List {$resource::getLabel()}",
            'create' => "Create new {$resource::getSingularLabel()}",
            'back' => 'Back to main menu',
            'quit' => 'Quit',
        ];

        $action = select(
            label: "What would you like to do with {$resource::getLabel()}?",
            options: $options
        );

        match ($action) {
            'list' => $this->showListView($resourceClass, 1),
            'create' => $this->showCreateForm($resourceClass),
            'back' => $this->handle(),
            'quit' => exit(0),
        };
    }

    protected function showListView(string $resourceClass, int $page, bool $showTrashed = false): void
    {
        $resource = new $resourceClass;
        $modelClass = $resource::getModel();
        $query = $modelClass::query();

        if ($showTrashed && $resource::usesSoftDeletes()) {
            $query->onlyTrashed();
        }

        $perPage = config('cli-crud.pagination.per_page', 15);
        $total = $query->count();
        $totalPages = max(1, ceil($total / $perPage));
        $page = max(1, min($page, $totalPages));

        $items = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        $this->info("\n{$resource::getLabel()}\n");

        if ($items->isEmpty()) {
            $this->showResourceMenu($resourceClass);

            return;
        }

        $this->showListActions($resourceClass, $items, $page, $totalPages, $showTrashed);
    }

    protected function showListActions(string $resourceClass, Collection $items, int $page, int $totalPages, bool $showTrashed): void
    {
        $resource = new $resourceClass;
        $columns = $resource::tableColumns();
        $modelInstance = $resource::getModelInstance();
        $casts = $modelInstance->getCasts();

        $headers = array_map(fn ($col) => ColumnFormatter::format($col), $columns);
        $headerWidths = array_map(fn ($header) => mb_strlen($header), $headers);

        $rows = [];
        foreach ($items as $index => $item) {
            $row = [];
            foreach ($columns as $colIndex => $column) {
                $value = data_get($item, $column);
                $formatted = $this->formatTableValueForDatatable($value);

                if (isset($casts[$column]) && $casts[$column] === 'boolean') {
                    $formatted = $this->centerPad($formatted, $headerWidths[$colIndex]);
                }

                $row[] = $formatted;
            }
            $rows[$index] = $row;
        }

        $selectedIndex = datatable(
            headers: $headers,
            rows: $rows,
            scroll: 10,
            label: 'Select a record (↑/↓ navigate, / search, Enter select)',
        );

        if ($selectedIndex === null) {
            $this->showResourceMenu($resourceClass);

            return;
        }

        $selectedItem = $items[$selectedIndex];
        $this->showRecordActionMenu($resourceClass, $selectedItem, $page, $totalPages, $showTrashed);
    }

    protected function showRecordActionMenu(string $resourceClass, Model $item, int $page, int $totalPages, bool $showTrashed): void
    {
        $resource = new $resourceClass;
        $options = [];

        $options['view'] = 'View details';

        if ($this->authorizer->update($resource, $item)) {
            $options['edit'] = 'Edit';
        }

        $isTrashed = $resource::usesSoftDeletes() && $item->trashed();

        if ($isTrashed) {
            if ($this->authorizer->restore($resource, $item)) {
                $options['restore'] = 'Restore';
            }
            if ($this->authorizer->forceDelete($resource, $item)) {
                $options['force_delete'] = 'Force delete';
            }
        } else {
            if ($this->authorizer->delete($resource, $item)) {
                $options['delete'] = 'Delete';
            }
        }

        $options['create'] = "Create new {$resource::getSingularLabel()}";

        if ($resource::usesSoftDeletes()) {
            $options['toggle_trashed'] = $showTrashed ? 'Show active records' : 'Show trashed records';
        }

        if ($totalPages > 1) {
            $options['page'] = 'Go to page...';
        }

        $options['back'] = 'Back to resource menu';
        $options['quit'] = 'Quit';

        $action = select(
            label: "What would you like to do with {$this->getItemLabel($item, $resource)}?",
            options: $options
        );

        $this->handleRecordAction($action, $resourceClass, $item, $page, $totalPages, $showTrashed);
    }

    protected function handleRecordAction(string $action, string $resourceClass, Model $item, int $page, int $totalPages, bool $showTrashed): void
    {
        if ($action === 'view') {
            $this->showDetailView($resourceClass, $item);
            $this->showListView($resourceClass, $page, $showTrashed);

            return;
        }

        if ($action === 'edit') {
            $this->showEditForm($resourceClass, $item);
            $this->showListView($resourceClass, $page, $showTrashed);

            return;
        }

        if ($action === 'delete') {
            $this->deleteModel($resourceClass, $item);
            $this->showListView($resourceClass, $page, $showTrashed);

            return;
        }

        if ($action === 'restore') {
            $this->restoreModel($resourceClass, $item);
            $this->showListView($resourceClass, $page, $showTrashed);

            return;
        }

        if ($action === 'force_delete') {
            $this->forceDeleteModel($resourceClass, $item);
            $this->showListView($resourceClass, $page, $showTrashed);

            return;
        }

        if ($action === 'create') {
            $this->showCreateForm($resourceClass);
            $this->showListView($resourceClass, $page, $showTrashed);

            return;
        }

        if ($action === 'toggle_trashed') {
            $this->showListView($resourceClass, 1, ! $showTrashed);

            return;
        }

        if ($action === 'page') {
            $newPage = $this->askForPageNumber($totalPages);
            $this->showListView($resourceClass, $newPage, $showTrashed);

            return;
        }

        if ($action === 'back') {
            $this->showResourceMenu($resourceClass);

            return;
        }

        if ($action === 'quit') {
            exit(0);
        }
    }

    protected function formatTableValue(mixed $value): string
    {
        if (is_null($value)) {
            return "\e[90mNULL\e[39m";
        }

        if (is_bool($value)) {
            return $value ? "\e[32m✓\e[39m" : "\e[31m✗\e[39m";
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(config('cli-crud.display.date_format', 'Y-m-d H:i:s'));
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        return (string) $value;
    }

    protected function formatTableValueForDatatable(mixed $value): string
    {
        if (is_null($value)) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? '✓' : '✗';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(config('cli-crud.display.date_format', 'Y-m-d H:i:s'));
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        return (string) $value;
    }

    protected function centerPad(string $value, int $width): string
    {
        $visibleLength = $this->getVisibleLength($value);
        if ($visibleLength >= $width) {
            return $value;
        }

        $totalPadding = $width - $visibleLength;
        $leftPadding = (int) floor($totalPadding / 2);
        $rightPadding = $totalPadding - $leftPadding;

        return str_repeat(' ', $leftPadding).$value.str_repeat(' ', $rightPadding);
    }

    protected function getVisibleLength(string $value): int
    {
        return mb_strlen(preg_replace('/\e\[[\d;]*m/', '', $value));
    }

    protected function askForPageNumber(int $totalPages): int
    {
        $page = (int) $this->ask("Enter page number (1-{$totalPages})", 1);

        return max(1, min($page, $totalPages));
    }

    protected function showDetailView(string $resourceClass, Model $model): void
    {
        $resource = new $resourceClass;

        $fields = $resource::getFields();
        if (empty($fields)) {
            $this->error("No fields defined for {$resource::getSingularLabel()}.");
            $this->showDetailActions($resourceClass, $model);

            return;
        }

        $this->detailViewRenderer->render($model, $resource);

        $this->showDetailActions($resourceClass, $model);
    }

    protected function showDetailActions(string $resourceClass, Model $model): void
    {
        $resource = new $resourceClass;
        $options = [];

        $options['back'] = 'Back to list';

        if ($this->authorizer->update($resource, $model)) {
            $options['edit'] = 'Edit';
        }

        $isTrashed = $resource::usesSoftDeletes() && $model->trashed();

        if ($isTrashed) {
            if ($this->authorizer->restore($resource, $model)) {
                $options['restore'] = 'Restore';
            }
            if ($this->authorizer->forceDelete($resource, $model)) {
                $options['force_delete'] = 'Force Delete';
            }
        } else {
            if ($this->authorizer->delete($resource, $model)) {
                $options['delete'] = 'Delete';
            }
        }

        $options['quit'] = 'Quit';

        $action = select(
            label: 'Select an action',
            options: $options
        );

        if ($action === 'quit') {
            exit(0);
        }

        if ($action === 'edit') {
            $this->showEditForm($resourceClass, $model);

            return;
        }

        match ($action) {
            'delete' => $this->deleteModel($resourceClass, $model),
            'force_delete' => $this->forceDeleteModel($resourceClass, $model),
            'restore' => $this->restoreModel($resourceClass, $model),
            'back' => null,
        };

        $this->showListView($resourceClass, 1);
    }

    protected function showCreateForm(string $resourceClass): void
    {
        $resource = new $resourceClass;

        if (! $this->authorizer->create($resource)) {
            $this->error('You are not authorized to create this resource.');
            $this->showResourceMenu($resourceClass);

            return;
        }

        $this->info("\nCreate new {$resource::getSingularLabel()}\n");

        $fields = $resource::getFields();
        $relations = $resource::getRelations();

        $belongsToRelations = array_filter($relations, fn ($r) => $r instanceof BelongsTo);

        $allFields = array_merge($fields, $belongsToRelations);

        $data = $this->formBuilder->build($allFields, null, $resource);

        if (confirm("Save this {$resource::getSingularLabel()}?")) {
            $modelClass = $resource::getModel();
            $model = $modelClass::create($data);

            $this->info("{$resource::getSingularLabel()} created successfully!");
            $this->showDetailView($resourceClass, $model);
        } else {
            $this->showResourceMenu($resourceClass);
        }
    }

    protected function showEditForm(string $resourceClass, Model $model): void
    {
        $resource = new $resourceClass;

        if (! $this->authorizer->update($resource, $model)) {
            $this->error('You are not authorized to edit this resource.');

            return;
        }

        $this->info("\nEdit {$resource::getSingularLabel()}\n");

        $fields = $resource::getFields();
        $relations = $resource::getRelations();

        $belongsToRelations = array_filter($relations, fn ($r) => $r instanceof BelongsTo);

        $allFields = array_merge($fields, $belongsToRelations);

        $data = $this->formBuilder->build($allFields, $model, $resource);

        // Preserve existing password if left empty during edit
        foreach ($fields as $field) {
            if ($field instanceof Text && $field->isPassword() && empty($data[$field->getName()])) {
                unset($data[$field->getName()]);
            }
        }

        if (confirm("Save changes to this {$resource::getSingularLabel()}?")) {
            $model->update($data);

            $this->info("{$resource::getSingularLabel()} updated successfully!");
        }

        $this->showDetailView($resourceClass, $model);
    }

    protected function deleteModel(string $resourceClass, Model $model): void
    {
        $resource = new $resourceClass;

        if (! $this->authorizer->delete($resource, $model)) {
            $this->error('You are not authorized to delete this resource.');

            return;
        }

        if (confirm("Are you sure you want to delete this {$resource::getSingularLabel()}?")) {
            $model->delete();
            $this->info("{$resource::getSingularLabel()} deleted successfully!");
        }
    }

    protected function forceDeleteModel(string $resourceClass, Model $model): void
    {
        $resource = new $resourceClass;

        if (! $this->authorizer->forceDelete($resource, $model)) {
            $this->error('You are not authorized to force delete this resource.');

            return;
        }

        if (confirm("Are you sure you want to permanently delete this {$resource::getSingularLabel()}? This cannot be undone.")) {
            $model->forceDelete();
            $this->info("{$resource::getSingularLabel()} permanently deleted!");
        }
    }

    protected function restoreModel(string $resourceClass, Model $model): void
    {
        $resource = new $resourceClass;

        if (! $this->authorizer->restore($resource, $model)) {
            $this->error('You are not authorized to restore this resource.');

            return;
        }

        if (confirm("Restore this {$resource::getSingularLabel()}?")) {
            $model->restore();
            $this->info("{$resource::getSingularLabel()} restored successfully!");
        }
    }

    protected function getItemLabel(Model $item, Resource $resource): string
    {
        $fields = $resource::getFields();

        if (! empty($fields)) {
            $firstField = $fields[0];
            $value = $item->{$firstField->getName()};
            if ($value !== null && $value !== '') {
                return ColumnTypeMapper::nameForValue($value);
            }
        }

        return "#{$item->getKey()}";
    }
}
