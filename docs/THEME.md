# Themes

The package includes two built-in color presets. Switch between them in `config/cli-crud.php`:

```php
'themes' => [
    'preset' => 'light', // 'dark' (default) or 'light'
],
```

The **dark preset** (default) is optimized for terminals with dark backgrounds (white text on black). The **light preset** adjusts colors that are hard to read on white backgrounds (yellow, cyan, faint) to visible alternatives.

Individual ANSI codes can be overridden by setting the corresponding key in the `themes` array. Any key set explicitly overrides the preset:

```php
'themes' => [
    'preset' => 'dark',
    'true' => "\e[92m",  // bright green for checkmarks
    'false' => "\e[91m", // bright red for X marks
],
```

Available theme keys: `null`, `true`, `false`, `enum`, `json_key`, `json_string`, `json_number`, `json_keyword`, `error`, `heading`, `invalid_json`, `code`, `blockquote`, `hr`, `chart`.

## Terminal Color Coding

The package uses ANSI escape codes and Unicode characters to distinguish data types in the terminal output:

| Value | Rendering | Example |
|-------|-----------|---------|
| **Null** | `—` (em dash) in the list view; `NULL` in gray in the detail view | `—` / `NULL` |
| **Boolean** | `✓` (green) or `✗` (red) in the detail and ANSI table views; plain `✓` / `✗` in the datatable | `✓` / `✗` |
| **Enum (PHP backed/unit)** | Faint gray with brackets: `[Draft]` | `[Draft]` |
| **DateTime** | Formatted per `config('cli-crud.display.date_format')` (default `Y-m-d H:i:s`) | `2024-01-15 10:30:00` |
| **JSON** | Syntax-highlighted: keys in cyan, strings in green, numbers in yellow, booleans/null in magenta | colored output |