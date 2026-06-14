<?php

namespace Repat\CliCrud\Validation;

use Illuminate\Support\Facades\Schema;
use Repat\CliCrud\Exceptions\FieldMismatchException;
use Repat\CliCrud\Fields\Field;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Support\ColumnTypeMapper;

class FieldValidator
{

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
        $columnType = ColumnTypeMapper::normalizeColumnType($column['type_name'] ?? $column['type']);
        $fieldClass = get_class($field);

        $allowedTypes = ColumnTypeMapper::getAllowedFieldClasses($columnType);

        if (empty($allowedTypes)) {
            return;
        }

        if (! in_array($fieldClass, $allowedTypes)) {
            $expectedType = class_basename($fieldClass);
            throw FieldMismatchException::forField($fieldName, $expectedType, $columnType);
        }
    }
}
