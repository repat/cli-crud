# Field Types

All field types use the signature `Field::make(string $label, ?string $name = null)` where `$label` is the display name and `$name` is the optional database column name. If `$name` is omitted, it will be automatically derived from the label (e.g., `'First Name'` → `'first_name'`, `'firstName'` → `'first_name'`).

## Common Methods

These methods are available on every field type:

- `->required()` — Field must be filled in
- `->nullable()` — Field may be left empty (stores `null`)
- `->default(mixed $value)` — Default value when creating
- `->rules(array $rules)` — Custom Laravel validation rules
- `->searchable()` — Include in the list-view search (see [SEARCH.md](SEARCH.md))
- `->notInForms()` — Exclude from create and edit forms (useful for auto-managed columns like `created_at`)

## Scalar Fields

- `Text::make('Name')` or `Text::make('Name', 'name')` — Text input
  - `->email()` — Email validation
  - `->password()` — Mask input (for password fields)
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Number::make('Age')` or `Number::make('Age', 'age')` — Numeric input
  - `->float()` — Allow decimal values
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Boolean::make('Is Active')` or `Boolean::make('Is Active', 'is_active')` — Yes/No confirmation
  - `->default(true)`, `->rules()`

- `DateTime::make('Created At')` or `DateTime::make('Created At', 'created_at')` — Date/time input
  - `->format('Y-m-d H:i:s')` — Custom format
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Select::make('Status')` or `Select::make('Status', 'status')` — Dropdown selection
  - `->options(['active' => 'Active', 'inactive' => 'Inactive'])`
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Textarea::make('Content')` or `Textarea::make('Content', 'content')` — Multi-line text input
  - `->markdown()` — Render content as Markdown in the detail view (requires `league/commonmark`)

  - `->required()`, `->nullable()`, `->default()`, `->rules()`

- `Json::make('Config')` or `Json::make('Config', 'config')` — JSON textarea input
  - `->highlight(bool)` — Toggle ANSI syntax highlighting in the detail view (default: `true`)
  - Validated as valid JSON on input
  - `->required()`, `->nullable()`, `->default()`, `->rules()`

<a href="../img/markdown-in-detail-screen.png"><img src="../img/markdown-in-detail-screen.png" alt="Markdown in detail screen" width="600" /></a>

<a href="../img/json-in-detail-screen.png"><img src="../img/json-in-detail-screen.png" alt="JSON in detail screen" width="600" /></a>

## Relations

See [RELATIONSHIPS.md](RELATIONSHIPS.md) for relation field types (BelongsTo, HasMany, MorphTo, etc.).

## Field Validation

<a href="../img/validation-screen.png"><img src="../img/validation-screen.png" alt="Validation screen" width="600" /></a>

The package validates that your field definitions match the database schema. If there's a mismatch, a `FieldMismatchException` is thrown.

For example:

- Defining a `Number` field on a `varchar` column will throw an error
- Defining a field for a non-existent column will throw an error

[← Back to README](../README.md)
