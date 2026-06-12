<?php

namespace Repat\CliCrud\Validation;

use Illuminate\Support\Facades\Schema;
use Repat\CliCrud\Exceptions\FieldMismatchException;
use Repat\CliCrud\Fields\Boolean;
use Repat\CliCrud\Fields\DateTime;
use Repat\CliCrud\Fields\Field;
use Repat\CliCrud\Fields\Json;
use Repat\CliCrud\Fields\Number;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Fields\Textarea;
use Repat\CliCrud\Resources\Resource;

class FieldValidator
{
    protected array $typeMap = [
        'string' => [Text::class, Textarea::class, Json::class],
        'varchar' => [Text::class, Textarea::class, Json::class],
        'text' => [Text::class, Textarea::class, Json::class],
        'mediumtext' => [Text::class, Textarea::class, Json::class],
        'longtext' => [Text::class, Textarea::class, Json::class],
        'integer' => [Number::class],
        'bigint' => [Number::class],
        'smallint' => [Number::class],
        'tinyint' => [Number::class, Boolean::class],
        'boolean' => [Boolean::class],
        'bool' => [Boolean::class],
        'datetime' => [DateTime::class],
        'timestamp' => [DateTime::class],
        'date' => [DateTime::class],
        'decimal' => [Number::class],
        'float' => [Number::class],
        'double' => [Number::class],
        'json' => [Text::class, Textarea::class, Json::class],
        'jsonb' => [Text::class, Textarea::class, Json::class],
    ];

    public function validate(Resource $resource): void
    {
        $model = $resource::getModelInstance();
        $table = $model->getTable();
        $columns = Schema::getColumns($table);
        $columnMap = collect($columns)->keyBy('name');

        $fields = $resource::getFields();

        foreach ($fields as $field) {
            $this->validateField($field, $columnMap, $table);
        }
    }

    protected function validateField(Field $field, $columnMap, string $table): void
    {
        $fieldName = $field->getName();

        if (! $columnMap->has($fieldName)) {
            throw FieldMismatchException::columnNotFound($fieldName, $table);
        }

        $column = $columnMap->get($fieldName);
        $columnType = $this->normalizeColumnType($column['type_name'] ?? $column['type']);
        $fieldClass = get_class($field);

        $allowedTypes = $this->typeMap[$columnType] ?? [];

        if (empty($allowedTypes)) {
            return;
        }

        if (! in_array($fieldClass, $allowedTypes)) {
            $expectedType = class_basename($fieldClass);
            throw FieldMismatchException::forField($fieldName, $expectedType, $columnType);
        }
    }

    protected function normalizeColumnType(string $type): string
    {
        $type = strtolower($type);

        if (str_contains($type, '(')) {
            $type = substr($type, 0, strpos($type, '('));
        }

        return trim($type);
    }
}
