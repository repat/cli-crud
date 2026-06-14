<?php

namespace Repat\CliCrud\Support;

use Repat\CliCrud\Fields\Boolean;
use Repat\CliCrud\Fields\DateTime;
use Repat\CliCrud\Fields\Field;
use Repat\CliCrud\Fields\Json;
use Repat\CliCrud\Fields\Number;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Fields\Textarea;

class ColumnTypeMapper
{
    protected static array $typeMap = [
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

    protected static array $bestFieldMap = [
        'string' => Text::class,
        'varchar' => Text::class,
        'text' => Textarea::class,
        'mediumtext' => Textarea::class,
        'longtext' => Textarea::class,
        'integer' => Number::class,
        'bigint' => Number::class,
        'smallint' => Number::class,
        'tinyint' => Boolean::class,
        'boolean' => Boolean::class,
        'bool' => Boolean::class,
        'datetime' => DateTime::class,
        'timestamp' => DateTime::class,
        'date' => DateTime::class,
        'decimal' => Number::class,
        'float' => Number::class,
        'double' => Number::class,
        'json' => Json::class,
        'jsonb' => Json::class,
    ];

    public static function getTypeMap(): array
    {
        return static::$typeMap;
    }

    public static function getAllowedFieldClasses(string $columnType): array
    {
        $normalized = static::normalizeColumnType($columnType);

        return static::$typeMap[$normalized] ?? [];
    }

    public static function getBestFieldClass(string $columnType): ?string
    {
        $normalized = static::normalizeColumnType($columnType);

        $class = static::$bestFieldMap[$normalized] ?? null;

        if ($class !== null && is_subclass_of($class, Field::class)) {
            return $class;
        }

        return null;
    }

    public static function normalizeColumnType(string $type): string
    {
        $type = strtolower($type);

        if (str_contains($type, '(')) {
            $type = substr($type, 0, strpos($type, '('));
        }

        return trim($type);
    }

    public static function nameForValue(mixed $value): string
    {
        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        return (string) $value;
    }

    public static function scalarForValue(mixed $value): mixed
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        return $value;
    }
}
