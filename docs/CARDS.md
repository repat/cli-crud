# Cards

Cards are supplementary panels displayed in the detail view, either before or after the relation tables. Three card types are available via static factory methods on the base `Card` class.

All card closures receive `(Model $model, Resource $resource)` and are called once per detail view render.

### Custom Card

Displays arbitrary content (single value or multi-line) in a box. The closure should return a string, number, or PHP enum.

```php
use Repat\CliCrud\Cards\Card;

public static function cards(): array
{
    return [
        Card::custom('Total Orders', fn ($model, $resource) => $model->orders()->count()),
    ];
}
```

Multi-line content is supported via newlines in the returned string:

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

### Chart Card

Renders an ASCII bar or horizontal bar chart. The closure must return an associative array of `['label' => value, …]`.

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
        }),
    ],
}
```

Chart types: `bar()` (default), `horizontalBar()`, `scatter()`.

To show percentages of the total instead of raw values, chain `->percentage()`:

```php
Card::chart('Orders per Month', fn ($model, $resource) => [
    'Jan' => 42, 'Feb' => 38, 'Mar' => 55, 'Apr' => 61, 'May' => 48,
])->bar()->percentage(),
```

### Scatter Chart

Plots `[x, y]` coordinate pairs on a 2D grid. The closure must return an associative array where each key is a label and each value is a `[x, y]` array.

```php
Card::chart('Temperature vs Sales', function ($model, $resource) {
    return [
        'Jan' => [10, 200], 'Feb' => [15, 150], 'Mar' => [20, 300],
        'Apr' => [22, 350], 'May' => [25, 400],
    ];
})->scatter(),
```

### Position

Cards render after relations by default. Use `->before()` to render them before relations (between the field values and the relation tables).

```php
Card::custom('Quick Stats', fn ($m, $r) => '…')->before(),
```

### Multiple cards

Return multiple cards from `cards()` — they are rendered in the order returned.

```php
public static function cards(): array
{
    return [
        Card::custom('Posts', fn ($m, $r) => $m->posts()->count()),
        Card::chart('Views per Day', fn ($m, $r) => [
            'Mon' => $m->views()->whereDay('created_at', 1)->count(),
        ])->horizontalBar(),
    ];
}
```

[← Back to README](../README.md)
