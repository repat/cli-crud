# Cards

Cards are supplementary panels displayed in the detail view, either before or after the relation tables. Three card types are available via static factory methods on the base `Card` class.

All card closures receive `(Model $model, Resource $resource)` and are called once per detail view render.

### Metric Card

Displays a single computed value in a box. The closure should return a string or number.

```php
use Repat\CliCrud\Cards\Card;

public static function cards(): array
{
    return [
        Card::metric('Total Orders', fn ($model, $resource) => $model->orders()->count()),
    ];
}
```

### Chart Card

Renders an ASCII bar, pie, or horizontal bar chart. The closure must return an associative array of `['label' => value, …]`.

```php
use Repat\CliCrud\Cards\Card;

public static function cards(): array
{
    return [
        Card::chart('Orders per Month', function ($model, $resource) {
            return [
                'Jan' => 42, 'Feb' => 38, 'Mar' => 55,
                'Apr' => 61, 'May' => 48,
            ];
        })->pie(),
    ],
}
```

Chart types: `bar()` (default), `pie()`, `horizontalBar()`.

To show percentages of the total instead of (or in addition to) raw values, chain `->percentage()`:

```php
Card::chart('Orders per Month', fn ($model, $resource) => [
    'Jan' => 42, 'Feb' => 38, 'Mar' => 55, 'Apr' => 61, 'May' => 48,
])->bar()->percentage(),
```

For `bar()` and `horizontalBar()`, the percentage replaces the raw value cell. For `pie()`, percentages are always shown — `->percentage()` is a no-op there.

### Custom Card

Renders arbitrary multi-line content. The closure should return a string.

```php
use Repat\CliCrud\Cards\Card;

public static function cards(): array
{
    return [
        Card::custom('Server Info', function ($model, $resource) {
            $host = gethostname();
            $date = now()->toDateTimeString();
            return "Host: {$host}\nDate: {$date}";
        }),
    ];
}
```

### Position

Cards render after relations by default. Use `->before()` to render them before relations (between the field values and the relation tables).

```php
Card::metric('Quick Stats', fn ($m, $r) => '…')->before(),
```

### Multiple cards

Return multiple cards from `cards()` — they are rendered in the order returned.

```php
public static function cards(): array
{
    return [
        Card::metric('Posts', fn ($m, $r) => $m->posts()->count()),
        Card::chart('Views per Day', fn ($m, $r) => [
            'Mon' => $m->views()->whereDay('created_at', 1)->count(),
        ])->horizontalBar(),
    ];
}
```

[← Back to README](../README.md)
