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
        if (isset(self::$specialCases[$column])) {
            return self::$specialCases[$column];
        }

        return ucfirst(str_replace('_', ' ', $column));
    }
}
