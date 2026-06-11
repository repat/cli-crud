# repat/cli-crud

A CLI CRUD admin panel for Laravel 12.x & 13.x, inspired by [Filament](https://filamentphp.com/) and [Laravel Nova](http://nova.laravel.com/). Built with `laravel/prompts`.

## Requirements

- PHP 8.5+
- Laravel 13.x

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
            Text::make('name')->required(),
            Text::make('email')->required()->email(),
            Boolean::make('is_active')->default(true),
            DateTime::make('email_verified_at')->nullable(),
            HasMany::make('posts', PostResource::class),
        ];
    }

    public static function tableColumns(): array
    {
        return ['id', 'name', 'email', 'is_active', 'created_at'];
    }
}
```

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

### Scalar Fields

- `Text::make('name')` - Text input
  - `->email()` - Email validation
  - `->required()` - Required field
  - `->nullable()` - Nullable field
  - `->default('value')` - Default value
  - `->rules(['string', 'max:255'])` - Custom validation rules

- `Number::make('age')` - Numeric input
  - `->float()` - Allow float values
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Boolean::make('is_active')` - Yes/No confirmation
  - `->default(true)`, `->rules()`

- `DateTime::make('created_at')` - Date/time input
  - `->format('Y-m-d H:i:s')` - Custom format
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Select::make('status')` - Dropdown selection
  - `->options(['active' => 'Active', 'inactive' => 'Inactive'])`
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Textarea::make('content')` - Multi-line text input
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

### Relations

- `BelongsTo::make('user_id', UserResource::class)` - Belongs to relationship
  - `->displayField('name')` - Field to display in selection
  - Uses inline search for large datasets

- `HasOne::make('profile', ProfileResource::class)` - Has one relationship
  - Displayed as sub-table in detail view

- `HasMany::make('posts', PostResource::class)` - Has many relationship
  - Displayed as paginated sub-table in detail view

## Authorization

The package integrates with Laravel's Gates and Policies. If a policy exists for a model, the package will check:

- `viewAny` - Can view the resource list
- `view` - Can view a specific record
- `create` - Can create new records
- `delete` - Can delete records
- `forceDelete` - Can permanently delete soft-deleted records
- `restore` - Can restore soft-deleted records

If no policy exists, all actions are allowed by default.

Disable authorization in config:

```php
'authorization' => [
    'enabled' => false,
],
```

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

### Planned (v2)

- BelongsToMany (pivot tables)
- MorphTo/MorphMany relations
- Search functionality
- Edit/Update operations

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
