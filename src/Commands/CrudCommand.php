<?php

namespace Repat\CliCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Repat\CliCrud\Authorization\Authorizer;
use Repat\CliCrud\Fields\Relations\Relation;
use Repat\CliCrud\Forms\FormBuilder;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Resources\ResourceRegistrar;
use Repat\CliCrud\Tables\TableRenderer;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\datatable;
use function Laravel\Prompts\menu;
use function Laravel\Prompts\select;

class CrudCommand extends Command
{
    protected $signature = 'cli-crud';
    protected $description = 'Interactive CLI CRUD admin panel';

    protected ResourceRegistrar $registrar;
    protected Authorizer $authorizer;
    protected TableRenderer $tableRenderer;
    protected FormBuilder $formBuilder;

    public function __construct(
        ResourceRegistrar $registrar,
        Authorizer $authorizer,
        TableRenderer $tableRenderer,
        FormBuilder $formBuilder
    ) {
        parent::__construct();
        $this->registrar = $registrar;
        $this->authorizer = $authorizer;
        $this->tableRenderer = $tableRenderer;
        $this->formBuilder = $formBuilder;
    }

    public function handle(): int
    {
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
            if ($this->authorizer->viewAny(new $resource())) {
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
            return;
        }

        $this->showResourceMenu($selected);
    }

    protected function showResourceMenu(string $resourceClass): void
    {
        $resource = new $resourceClass();

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
            'quit' => null,
        };
    }

    protected function showListView(string $resourceClass, int $page, bool $showTrashed = false): void
    {
        $resource = new $resourceClass();
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

        $tableOutput = $this->tableRenderer->render($items, $resource::tableColumns(), $page, $totalPages);
        $this->line($tableOutput);

        if ($items->isEmpty()) {
            $this->showResourceMenu($resourceClass);
            return;
        }

        $this->showListActions($resourceClass, $items, $page, $totalPages, $showTrashed);
    }

    protected function showListActions(string $resourceClass, Collection $items, int $page, int $totalPages, bool $showTrashed): void
    {
        $resource = new $resourceClass();
        $columns = $resource::tableColumns();

        $headers = array_map(fn($col) => ucfirst(str_replace('_', ' ', $col)), $columns);

        $rows = [];
        foreach ($items as $index => $item) {
            $row = [];
            foreach ($columns as $column) {
                $value = data_get($item, $column);
                $row[] = $this->formatTableValue($value);
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
        $resource = new $resourceClass();
        $options = [];

        $options['view'] = 'View details';

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
            $this->showListView($resourceClass, 1, !$showTrashed);
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
            return;
        }
    }

    protected function formatTableValue(mixed $value): string
    {
        if (is_null($value)) {
            return 'NULL';
        }

        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }

    protected function askForPageNumber(int $totalPages): int
    {
        $page = (int) $this->ask("Enter page number (1-{$totalPages})", 1);
        return max(1, min($page, $totalPages));
    }

    protected function showDetailView(string $resourceClass, Model $model): void
    {
        $resource = new $resourceClass();

        $this->info("\n{$resource::getSingularLabel()} #{$model->getKey()}\n");

        $fields = $resource::getFields();
        foreach ($fields as $field) {
            $value = $model->{$field->getName()};
            $formattedValue = $this->formatFieldValue($value);
            $this->line("{$field->getLabel()}: {$formattedValue}");
        }

        $relations = $resource::getRelations();
        foreach ($relations as $relation) {
            $this->showRelationTable($model, $relation);
        }

        $this->showDetailActions($resourceClass, $model);
    }

    protected function formatFieldValue(mixed $value): string
    {
        if (is_null($value)) {
            return '<fg=gray>NULL</>';
        }

        if (is_bool($value)) {
            return $value ? '<fg=green>Yes</>' : '<fg=red>No</>';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return (string) $value;
    }

    protected function showRelationTable(Model $model, Relation $relation): void
    {
        $relatedItems = $model->{$relation->getName()}()->get();

        if ($relatedItems->isEmpty()) {
            return;
        }

        $relatedResource = $relation->getResource();
        $relationLabel = ucfirst($relation->getName());

        $this->info("\n{$relationLabel} ({$relatedItems->count()})\n");

        $perPage = config('cli-crud.pagination.relation_per_page', 10);
        $totalPages = max(1, ceil($relatedItems->count() / $perPage));

        $tableOutput = $this->tableRenderer->render(
            $relatedItems->take($perPage),
            $relatedResource::tableColumns(),
            1,
            $totalPages
        );

        $this->line($tableOutput);
    }

    protected function showDetailActions(string $resourceClass, Model $model): void
    {
        $resource = new $resourceClass();
        $options = [];

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

        $options['back'] = 'Back to list';
        $options['quit'] = 'Quit';

        $action = select(
            label: 'Select an action',
            options: $options
        );

        if ($action === 'quit') {
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
        $resource = new $resourceClass();

        if (!$this->authorizer->create($resource)) {
            $this->error('You are not authorized to create this resource.');
            $this->showResourceMenu($resourceClass);
            return;
        }

        $this->info("\nCreate new {$resource::getSingularLabel()}\n");

        $fields = $resource::getFields();
        $relations = $resource::getRelations();

        $belongsToRelations = array_filter($relations, fn($r) => $r instanceof \Repat\CliCrud\Fields\Relations\BelongsTo);

        $allFields = array_merge($fields, $belongsToRelations);

        $data = $this->formBuilder->build($allFields);

        if (confirm("Save this {$resource::getSingularLabel()}?")) {
            $modelClass = $resource::getModel();
            $model = $modelClass::create($data);

            $this->info("{$resource::getSingularLabel()} created successfully!");
            $this->showDetailView($resourceClass, $model);
        } else {
            $this->showResourceMenu($resourceClass);
        }
    }

    protected function deleteModel(string $resourceClass, Model $model): void
    {
        $resource = new $resourceClass();

        if (!$this->authorizer->delete($resource, $model)) {
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
        $resource = new $resourceClass();

        if (!$this->authorizer->forceDelete($resource, $model)) {
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
        $resource = new $resourceClass();

        if (!$this->authorizer->restore($resource, $model)) {
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

        if (!empty($fields)) {
            $firstField = $fields[0];
            $value = $item->{$firstField->getName()};
            if ($value) {
                return (string) $value;
            }
        }

        return "#{$item->getKey()}";
    }
}
