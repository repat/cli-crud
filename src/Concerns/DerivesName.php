<?php

namespace Repat\CliCrud\Concerns;

use Illuminate\Support\Str;

trait DerivesName
{
    protected function deriveName(string $label): string
    {
        // Convert camelCase/PascalCase to snake_case
        $name = Str::snake($label);

        // Remove special characters (keep only alphanumeric, spaces, and underscores)
        $name = preg_replace('/[^a-zA-Z0-9\s_]/', '', $name);

        // Replace spaces with underscores
        $name = Str::replace(' ', '_', $name);

        // Convert to lowercase
        $name = Str::lower($name);

        // Remove multiple consecutive underscores
        $name = preg_replace('/_+/', '_', $name);

        // Trim underscores from start/end
        return trim($name, '_');
    }
}
