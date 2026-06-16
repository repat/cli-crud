# repat/cli-crud

A CLI CRUD admin panel for Laravel, inspired by [Filament](https://filamentphp.com/) and [Laravel Nova](http://nova.laravel.com/). Built with [`laravel/prompts`](https://laravel.com/docs/13.x/prompts) and [`nunomaduro/termwind`](https://github.com/nunomaduro/termwind).

## Requirements

- PHP ^8.2
- Laravel 12.x | 13.x

## Installation

```bash
composer require repat/cli-crud
```

The service provider will be automatically registered.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=cli-crud-config
```

This will create `config/cli-crud.php`:

```php
return [
    'resources' => [
        'path' => app_path('CliCrud/Resources'),
        'namespace' => 'App\\CliCrud\\Resources',
    ],
    'actions' => [
        'path' => app_path('CliCrud/Actions'),
        'namespace' => 'App\\CliCrud\\Actions',
    ],
    'pagination' => [
        'per_page' => 15,
        'relation_per_page' => 10,
    ],
    'authorization' => [
        'enabled' => true,
    ],
];
```

## Themes

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

## Usage

### Running the CLI

```bash
php artisan cli-crud
```

This opens an interactive menu where you can:

- Select a resource
- List records (paginated)
- Search for records
- View record details
- Create new records
- Delete records (soft delete, force delete, restore)
- Run an action for (a) record(s)
- View custom cards & charts for metrics

### Creating Resources

See [docs/RESOURCES.md](docs/RESOURCES.md) for creating resources, the generated structure, auto-generated fields from a model, and available properties.

## Fields

See [docs/FIELDS.md](docs/FIELDS.md) for all field types, relations, and options.

## Search

See [docs/SEARCH.md](docs/SEARCH.md) for declaring searchable fields, the `$search` override, and custom search engine integration.

## Actions

See [docs/ACTIONS.md](docs/ACTIONS.md) for creating and attaching Nova-style actions, including queued and destructive variants.

## Cards

See [docs/CARDS.md](docs/CARDS.md) for Metric, Chart, and Custom cards in the detail view.

## Authorization

See [docs/AUTHORIZATION.md](docs/AUTHORIZATION.md) for enabling Laravel Gates/Policies integration.

## Terminal Color Coding

The package uses ANSI escape codes and Unicode characters to distinguish data types in the terminal output:

| Value | Rendering | Example |
|-------|-----------|---------|
| **Null** | `—` (em dash) in the list view; `NULL` in gray in the detail view | `—` / `NULL` |
| **Boolean** | `✓` (green) or `✗` (red) in the detail and ANSI table views; plain `✓` / `✗` in the datatable | `✓` / `✗` |
| **Enum (PHP backed/unit)** | Faint gray with brackets: `[Draft]` | `[Draft]` |
| **DateTime** | Formatted per `config('cli-crud.display.date_format')` (default `Y-m-d H:i:s`) | `2024-01-15 10:30:00` |
| **JSON** | Syntax-highlighted: keys in cyan, strings in green, numbers in yellow, booleans/null in magenta | colored output |

## Soft Deletes

If your model uses the `SoftDeletes` trait, the package automatically:

- Shows a toggle to view trashed records
- Provides "Restore" and "Force Delete" actions for trashed records
- Performs soft delete by default

## Features

### Implemented

- ✅ Resource-based architecture
- ✅ Explicit field definitions with database validation
- ✅ Laravel Gates/Policies integration
- ✅ Soft delete + force delete + restore
- ✅ Relations (BelongsTo, HasOne, HasMany) in detail view
- ✅ Paginated list view with page numbers
- ✅ Inline search for BelongsTo selects
- ✅ Auto-discovery of resources
- ✅ List-view search with configurable searchable fields
- ✅ `searchUsing()` hook for Laravel Scout / Algolia / Meilisearch
- ✅ Edit/Update operations
- ✅ Nova-style Actions with `handle()`, fields, `ActionFields`, `ActionResponse`
- ✅ Destructive action variant with extra confirmation
- ✅ `ShouldQueue` support for background action dispatch
- ✅ `make:cli-action` generator with `--queued` and `--destructive` flags

### Planned

- BelongsToMany (pivot tables)
- MorphTo/MorphMany relations
- User Login (to use authorization)
- Export (CSV, JSON)
- Action log (audit trail)
- Custom themes

## Testing

```bash
composer test
```

## License

MIT
