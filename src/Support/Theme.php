<?php

namespace Repat\CliCrud\Support;

class Theme
{
    protected static array $darkDefaults = [
        'null' => "\e[90m",
        'true' => "\e[32m",
        'false' => "\e[31m",
        'enum' => "\e[2m",
        'json_key' => "\e[36m",
        'json_string' => "\e[32m",
        'json_number' => "\e[33m",
        'json_keyword' => "\e[35m",
        'error' => "\e[31m",
        'heading' => "\e[1;33m",
        'invalid_json' => "\e[31m",
        'code' => "\e[38;5;244m",
        'blockquote' => "\e[38;5;242m",
        'hr' => "\e[90m",
        'relation_value' => "\e[36m",
        'chart' => [
            "\e[32m", "\e[34m", "\e[33m", "\e[35m", "\e[36m",
            "\e[91m", "\e[92m", "\e[93m", "\e[94m", "\e[95m",
        ],
    ];

    protected static array $lightDefaults = [
        'null' => "\e[90m",
        'true' => "\e[32m",
        'false' => "\e[31m",
        'enum' => "\e[38;5;245m",
        'json_key' => "\e[34m",
        'json_string' => "\e[32m",
        'json_number' => "\e[38;5;130m",
        'json_keyword' => "\e[35m",
        'error' => "\e[31m",
        'heading' => "\e[1;38;5;130m",
        'invalid_json' => "\e[31m",
        'code' => "\e[38;5;240m",
        'blockquote' => "\e[38;5;242m",
        'hr' => "\e[90m",
        'relation_value' => "\e[34m",
        'chart' => [
            "\e[32m", "\e[34m", "\e[38;5;130m", "\e[35m", "\e[36m",
            "\e[91m", "\e[92m", "\e[38;5;136m", "\e[94m", "\e[95m",
        ],
    ];

    public static function preset(): string
    {
        return config('cli-crud.themes.preset', 'dark');
    }

    public static function null(): string
    {
        return static::value('null');
    }

    public static function true(): string
    {
        return static::value('true');
    }

    public static function false(): string
    {
        return static::value('false');
    }

    public static function enum(): string
    {
        return static::value('enum');
    }

    public static function jsonKey(): string
    {
        return static::value('json_key');
    }

    public static function jsonString(): string
    {
        return static::value('json_string');
    }

    public static function jsonNumber(): string
    {
        return static::value('json_number');
    }

    public static function jsonKeyword(): string
    {
        return static::value('json_keyword');
    }

    public static function error(): string
    {
        return static::value('error');
    }

    public static function heading(): string
    {
        return static::value('heading');
    }

    public static function invalidJson(): string
    {
        return static::value('invalid_json');
    }

    public static function code(): string
    {
        return static::value('code');
    }

    public static function blockquote(): string
    {
        return static::value('blockquote');
    }

    public static function hr(): string
    {
        return static::value('hr');
    }

    public static function relationValue(): string
    {
        return static::value('relation_value');
    }

    public static function chartColors(): array
    {
        return static::value('chart');
    }

    public static function resetAll(): string
    {
        return "\e[0m";
    }

    public static function resetFg(): string
    {
        return "\e[39m";
    }

    public static function resetBold(): string
    {
        return "\e[22m";
    }

    public static function resetItalic(): string
    {
        return "\e[23m";
    }

    public static function bold(): string
    {
        return "\e[1m";
    }

    public static function italic(): string
    {
        return "\e[3m";
    }

    public static function underline(): string
    {
        return "\e[4m";
    }

    public static function linkUrl(): string
    {
        return "\e[4m";
    }

    public static function resetUnderline(): string
    {
        return "\e[24m";
    }

    protected static function value(string $key): mixed
    {
        $override = config('cli-crud.themes.'.$key);

        if ($override !== null) {
            return $override;
        }

        $preset = static::preset();

        return match ($preset) {
            'light' => static::$lightDefaults[$key] ?? static::$darkDefaults[$key],
            default => static::$darkDefaults[$key],
        };
    }
}
