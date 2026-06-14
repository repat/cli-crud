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

### Creating Resources

Generate a new resource:

```bash
php artisan make:cli-resource User
```

This creates `app/CliCrud/Resources/UserResource.php`:

```php
<?php

namespace App\CliCrud\Resources;

use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Fields\Boolean;
use Repat\CliCrud\Fields\DateTime;
use Repat\CliCrud\Fields\Relations\HasMany;

class UserResource extends Resource
{
    protected static string $model = \App\Models\User::class;
    protected static string $label = 'Users';
    protected static string $singularLabel = 'User';

    public static function fields(): array
    {
        return [
            Text::make('Name')->required(),
            Text::make('Email')->required()->email(),
            Boolean::make('Is Active')->default(true),
            DateTime::make('Email Verified At')->nullable(),
            HasMany::make('Posts', PostResource::class),
        ];
    }

  public static function tableColumns(): array
  {
      return ['id', 'name', 'email', 'is_active', 'created_at'];
  }
}
```

## Search

When you enter the list view of a resource that has searchable fields, you'll be prompted for a search term. Empty input shows all records. The term is preserved across pagination, soft-delete toggles, detail views, edits, and deletes.

### Declaring searchable fields

Opt in to search per field with `->searchable()`:

```php
public static function fields(): array
{
    return [
        Text::make('Name')->required()->searchable(),
        Text::make('Email')->required()->email()->searchable(),
        Textarea::make('Bio'),
    ];
}
```

You can also override the list explicitly on the resource by setting the `$search` property to an array of column names. This takes precedence over `->searchable()` and is useful when you want to search a column that isn't represented by a `Field` instance:

```php
class UserResource extends Resource
{
    protected static ?array $search = ['name', 'email', 'phone'];

    // ...
}
```

### Custom search engines (Laravel Scout, Algolia, Meilisearch, …)

`searchUsing()` is the override seam for full-text search. The default implementation applies an `OR ... LIKE %term%` over the searchable fields. To integrate with Scout, Algolia, Meilisearch, or any other engine, override `searchUsing()` on the resource and return an Eloquent `Builder`:

```php
use Illuminate\Database\Eloquent\Builder;
use App\Models\Post;

class PostResource extends Resource
{
    public static function searchUsing(Builder $query, string $term): Builder
    {
        if (trim($term) === '') {
            return parent::searchUsing($query, $term);
        }

        $ids = Post::search($term)->keys();

        return $query->whereIn('id', $ids);
    }
}
```

The method is called once per list view render, receives the already-`onlyTrashed`-filtered query, and must return an Eloquent `Builder` so pagination and the displayed rows stay consistent.

### Running the CLI

```bash
php artisan cli-crud
```

This opens an interactive menu where you can:

- Select a resource
- List records (paginated)
- View record details
- Create new records
- Delete records (soft delete, force delete, restore)

## Field Types

All field types use the signature `Field::make(string $label, ?string $name = null)` where `$label` is the display name and `$name` is the optional database column name. If `$name` is omitted, it will be automatically derived from the label (e.g., `'First Name'` → `'first_name'`, `'firstName'` → `'first_name'`).

### Scalar Fields

- `Text::make('Name')` or `Text::make('Name', 'name')` - Text input
  - `->email()` - Email validation
  - `->required()` - Required field
  - `->nullable()` - Nullable field
  - `->default('value')` - Default value
  - `->rules(['string', 'max:255'])` - Custom validation rules

- `Number::make('Age')` or `Number::make('Age', 'age')` - Numeric input
  - `->float()` - Allow float values
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Boolean::make('Is Active')` or `Boolean::make('Is Active', 'is_active')` - Yes/No confirmation
  - `->default(true)`, `->rules()`

- `DateTime::make('Created At')` or `DateTime::make('Created At', 'created_at')` - Date/time input
  - `->format('Y-m-d H:i:s')` - Custom format
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Select::make('Status')` or `Select::make('Status', 'status')` - Dropdown selection
  - `->options(['active' => 'Active', 'inactive' => 'Inactive'])`
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Textarea::make('Content')` or `Textarea::make('Content', 'content')` - Multi-line text input
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

### Relations

All relation types use the signature `Relation::make(string $label, string $resourceClass)` or `Relation::make(string $label, string $name, string $resourceClass)` where `$label` is the display name, `$name` is the optional relationship method name on the model, and `$resourceClass` is the related resource class. If `$name` is omitted, it will be automatically derived from the label.

- `BelongsTo::make('User', UserResource::class)` or `BelongsTo::make('User', 'user', UserResource::class)` - Belongs to relationship
  - `->displayField('name')` - Field to display in selection
  - Uses inline search for large datasets

- `HasOne::make('Profile', ProfileResource::class)` or `HasOne::make('Profile', 'profile', ProfileResource::class)` - Has one relationship
  - Displayed as sub-table in detail view

- `HasMany::make('Posts', PostResource::class)` or `HasMany::make('Posts', 'posts', PostResource::class)` - Has many relationship
  - Displayed as paginated sub-table in detail view

## Authorization

The package integrates with Laravel's Gates and Policies. Authorization is **disabled by default** since this is a CLI tool.

To enable authorization, update `config/cli-crud.php`:

```php
'authorization' => [
    'enabled' => true,
],
```

When enabled, the package checks:

- `viewAny` - Can view the resource list
- `view` - Can view a specific record
- `create` - Can create new records
- `delete` - Can delete records
- `forceDelete` - Can permanently delete soft-deleted records
- `restore` - Can restore soft-deleted records

**Note:** If no user is authenticated (typical in CLI), all actions are allowed regardless of policy settings.

If no policy exists for a model, all actions are allowed by default.

## Field Validation

The package validates that your field definitions match the database schema. If there's a mismatch, a `FieldMismatchException` is thrown.

For example:

- Defining a `Number` field on a `varchar` column will throw an error
- Defining a field for a non-existent column will throw an error

## Soft Deletes

If your model uses the `SoftDeletes` trait, the package automatically:

- Shows a toggle to view trashed records
- Provides "Restore" and "Force Delete" actions for trashed records
- Performs soft delete by default

## Features

### Implemented (v1)

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

### Planned (v2)

- BelongsToMany (pivot tables)
- MorphTo/MorphMany relations

### Planned (v3)

- Export (CSV, JSON)
- Actions/Filters
- Custom themes

## Testing

```bash
composer test
```

## License

MIT
