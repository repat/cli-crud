<?php

namespace Repat\CliCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Repat\CliCrud\Support\ColumnTypeMapper;

class MakeCliResourceCommand extends Command
{
    protected $signature = 'make:cli-resource {name : The name of the resource} {--model= : The Eloquent model class to derive fields from}';

    protected $description = 'Create a new CLI CRUD resource class';

    public function handle(Filesystem $files): int
    {
        $name = $this->argument('name');
        $className = Str::studly($name);

        if (! Str::endsWith($className, 'Resource')) {
            $className .= 'Resource';
        }

        $path = $this->getPath($className);

        if ($files->exists($path)) {
            $this->error("Resource [{$className}] already exists!");

            return self::FAILURE;
        }

        $modelClass = $this->resolveModelClass($className);

        if ($this->option('model') && ! class_exists($modelClass)) {
            $this->error("Model class [{$modelClass}] not found.");

            return self::FAILURE;
        }

        $files->ensureDirectoryExists(dirname($path));
        $files->put($path, $this->buildClass($className, $modelClass));

        $this->info("Resource [{$className}] created successfully.");
        $this->line("Path: {$path}");

        return self::SUCCESS;
    }

    protected function getPath(string $className): string
    {
        $path = config('cli-crud.resources.path', app_path('CliCrud/Resources'));

        return $path.DIRECTORY_SEPARATOR.$className.'.php';
    }

    protected function resolveModelClass(string $className): string
    {
        $modelOption = $this->option('model');

        if ($modelOption) {
            return $modelOption;
        }

        $modelName = Str::beforeLast($className, 'Resource');

        return "App\\Models\\{$modelName}";
    }

    protected function buildClass(string $className, string $modelClass): string
    {
        $stub = $this->getStub();

        $namespace = config('cli-crud.resources.namespace', 'App\\CliCrud\\Resources');
        $modelName = class_basename($modelClass);

        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ model }}', '{{ label }}', '{{ singularLabel }}', '{{ fields }}', '{{ tableColumns }}'],
            [
                $namespace,
                $className,
                $modelClass,
                Str::plural($modelName),
                $modelName,
                $this->generateFieldsCode($modelClass),
                $this->generateTableColumnsCode($modelClass),
            ],
            $stub
        );

        return $stub;
    }

    protected function getStub(): string
    {
        $customStub = base_path('stubs/cli-crud/cli-resource.stub');

        if (file_exists($customStub)) {
            return file_get_contents($customStub);
        }

        return file_get_contents(__DIR__.'/../../stubs/cli-resource.stub');
    }

    protected function generateFieldsCode(string $modelClass): string
    {
        if (! $this->option('model')) {
            return "Text::make('Name', 'name')->required(),";
        }

        $model = new $modelClass;
        $columns = Schema::getColumns($model->getTable());

        $fieldLines = [];

        foreach ($columns as $column) {
            if ($this->shouldSkipColumn($column)) {
                continue;
            }

            $fieldCode = $this->buildFieldCode($column);

            if ($fieldCode !== null) {
                $fieldLines[] = $fieldCode;
            }
        }

        if (empty($fieldLines)) {
            return "Text::make('Name', 'name')->required(),";
        }

        return implode(",\n        ", $fieldLines).',';
    }

    protected function generateTableColumnsCode(string $modelClass): string
    {
        if (! $this->option('model')) {
            return "'id', 'name', 'created_at'";
        }

        $model = new $modelClass;
        $columns = Schema::getColumns($model->getTable());

        $columnNames = [];

        foreach ($columns as $column) {
            $name = $column['name'];

            if ($name === 'id') {
                $columnNames[] = $name;
                continue;
            }

            if ($name === 'created_at') {
                $columnNames[] = $name;
                continue;
            }

            if (in_array($name, ['updated_at', 'deleted_at'])) {
                continue;
            }

            $columnNames[] = $name;
        }

        return "'".implode("', '", $columnNames)."'";
    }

    protected function shouldSkipColumn(array $column): bool
    {
        return in_array($column['name'], ['id', 'created_at', 'updated_at', 'deleted_at']);
    }

    protected function buildFieldCode(array $column): ?string
    {
        $name = $column['name'];
        $typeName = $column['type_name'] ?? $column['type'];
        $nullable = $column['nullable'] ?? false;
        $default = $column['default'] ?? null;

        $fieldClass = ColumnTypeMapper::getBestFieldClass($typeName);

        if ($fieldClass === null) {
            return null;
        }

        $shortClass = class_basename($fieldClass);
        $label = $this->getLabelFromColumnName($name);

        $code = "{$shortClass}::make('{$label}', '{$name}')";

        $code = $this->applyTypeModifiers($code, $fieldClass, $typeName, $name);
        $code = $this->applyNullability($code, $nullable, $default);

        return $code;
    }

    protected function applyTypeModifiers(string $code, string $fieldClass, string $typeName, string $name): string
    {
        if ($fieldClass === \Repat\CliCrud\Fields\Number::class) {
            $normalized = ColumnTypeMapper::normalizeColumnType($typeName);

            if (in_array($normalized, ['decimal', 'float', 'double'])) {
                $code .= '->float()';
            }
        }

        if ($fieldClass === \Repat\CliCrud\Fields\Text::class) {
            if ($name === 'email') {
                $code .= '->email()';
            } elseif ($name === 'password') {
                $code .= '->password()';
            }
        }

        return $code;
    }

    protected function applyNullability(string $code, bool $nullable, mixed $default): string
    {
        if ($nullable) {
            return $code.'->nullable()';
        }

        if ($default !== null) {
            $formatted = $this->formatDefaultValue($default);

            if ($formatted !== null) {
                return $code."->default({$formatted})";
            }
        }

        return $code.'->required()';
    }

    protected function getLabelFromColumnName(string $name): string
    {
        $name = preg_replace('/_id$/', '', $name);

        return Str::title(str_replace('_', ' ', $name));
    }

    protected function formatDefaultValue(mixed $default): ?string
    {
        if ($default === null) {
            return null;
        }

        if (is_string($default)) {
            $upper = strtoupper($default);

            if ($upper === 'CURRENT_TIMESTAMP' || str_starts_with($upper, 'EXPRESSION')) {
                return null;
            }
        }

        return var_export($default, true);
    }
}
