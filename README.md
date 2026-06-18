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

## Resources

See [docs/RESOURCES.md](docs/RESOURCES.md) for creating resources, the generated structure, auto-generated fields from a model, and available properties.

## Fields

See [docs/FIELDS.md](docs/FIELDS.md) for all field types, relations, and options.

## Search

See [docs/SEARCH.md](docs/SEARCH.md) for declaring searchable fields, the `$search` override, and custom search engine integration.

## Actions

See [docs/ACTIONS.md](docs/ACTIONS.md) for creating and attaching Nova-style actions, including queued and destructive variants.

## Cards

See [docs/CARDS.md](docs/CARDS.md) for Chart, Image and Custom cards in the detail view.

## Authorization

See [docs/AUTHORIZATION.md](docs/AUTHORIZATION.md) for enabling Laravel Gates/Policies integration.

## Features

### Implemented

- ✅ Resource-based architecture
- ✅ Explicit field definitions with database validation
- ✅ Laravel Gates/Policies integration with interactive login prompt
- ✅ Soft delete + force delete + restore
- ✅ All Eloquent relationship types (HasOne, HasMany, BelongsTo, BelongsToMany, HasManyThrough, MorphOne, MorphMany, MorphTo, MorphToMany, MorphedByMany)
- ✅ Paginated list view with page numbers
- ✅ Inline search for BelongsTo selects with "— None —" option for nullable relations
- ✅ Auto-discovery of resources
- ✅ List-view search with configurable searchable fields
- ✅ `searchUsing()` hook for Laravel Scout / Algolia / Meilisearch
- ✅ Edit/Update operations
- ✅ Nova-style Actions with `handle()`, fields, `ActionFields`, `ActionResponse`
- ✅ Destructive action variant with extra confirmation
- ✅ `ShouldQueue` support for background action dispatch
- ✅ `make:cli-action` generator with `--queued` and `--destructive` flags
- ✅ `make:cli-resource --model` for schema-based field generation
- ✅ Markdown rendering for Textarea fields (requires `league/commonmark`)
- ✅ Cards (Metric, Chart, Custom) with before/after positioning
- ✅ Chart types: bar, horizontal bar, and scatter
- ✅ Chart percentage mode (`->percentage()`)
- ✅ Customizable theme presets (dark/light)
- ✅ `->notInForms()` to exclude fields from create/edit forms

### Planned

- Export (CSV, JSON)
- Action log (audit trail)
- Dashboards
- TUI testing
- Code Cleanup

## Testing

```bash
composer test
```

## License

MIT
