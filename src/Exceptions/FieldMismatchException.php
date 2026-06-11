<?php

namespace Repat\CliCrud\Exceptions;

use Exception;

class FieldMismatchException extends Exception
{
    public static function forField(string $field, string $expectedType, string $actualType): self
    {
        return new self(
            "Field '{$field}' defined as {$expectedType} in resource, but database column is {$actualType}."
        );
    }

    public static function columnNotFound(string $field, string $table): self
    {
        return new self(
            "Field '{$field}' defined in resource, but column does not exist in table '{$table}'."
        );
    }
}
