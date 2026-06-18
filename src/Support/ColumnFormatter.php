<?php

namespace Repat\CliCrud\Support;

class ColumnFormatter
{
    protected static array $specialCases = [
        'id' => 'ID',
        'uuid' => 'UUID',
        'api_key' => 'API Key',
        'url' => 'URL',
    ];

    public static function format(string $column): string
    {
        if (str_contains($column, '.')) {
            return implode(' → ', array_map(
                fn ($segment) => self::formatSegment($segment),
                explode('.', $column)
            ));
        }

        return self::formatSegment($column);
    }

    protected static function formatSegment(string $segment): string
    {
        if (isset(self::$specialCases[$segment])) {
            return self::$specialCases[$segment];
        }

        return ucfirst(str_replace('_', ' ', $segment));
    }
}
