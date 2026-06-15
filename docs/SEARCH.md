# Search

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

[← Back to README](../README.md)
