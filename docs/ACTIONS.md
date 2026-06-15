# Actions

Actions are Nova-style tasks that can be triggered against the currently selected record from the list or detail view. They support a `handle()` method, optional input fields, a `ShouldQueue` opt-in for background processing, and a destructive variant that requires an extra confirmation.

### Creating Actions

Generate a new action:

```bash
php artisan make:cli-action EmailAccountProfile
```

The destination path and namespace are read from `config/cli-crud.php` (`actions.path` / `actions.namespace`). Use the `--queued` flag to opt into `ShouldQueue`, or `--destructive` to extend `DestructiveAction`:

```bash
php artisan make:cli-action EmailAccountProfile --queued
php artisan make:cli-action DeleteAccount --destructive
```

This creates a class that looks like:

```php
<?php

namespace App\CliCrud\Actions;

use Illuminate\Database\Eloquent\Collection;
use Repat\CliCrud\Actions\Action;
use Repat\CliCrud\Actions\ActionFields;
use Repat\CliCrud\Actions\ActionResponse;

class EmailAccountProfileAction extends Action
{
    protected ?string $name = 'Email Account Profile';

    public function fields(): array
    {
        return [];
    }

    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        foreach ($models as $user) {
            // ...send the email...
        }

        return ActionResponse::message('It worked!');
    }
}
```

### Attaching Actions to a Resource

Declare actions in the resource's `actions()` method. Class strings and pre-built instances are both accepted:

```php
class UserResource extends Resource
{
    public static function actions(): array
    {
        return [
            EmailAccountProfileAction::class,
            BanUserAction::make()->destructive()->confirmText('Ban this user?'),
        ];
    }
}
```

When the resource has at least one action, the list view and detail view will show a `Run action...` sub-menu option. The sub-menu only appears when actions are registered, matching Nova's behavior.

### Action Fields

Declare any of the standard `Field` types (Text, Number, Boolean, Select, Textarea) in `fields()`. The user is prompted for each value before the action runs:

```php
public function fields(): array
{
    return [
        Text::make('Subject')->required(),
        Textarea::make('Note')->nullable(),
    ];
}
```

Inside `handle()` the values are available via dynamic property access on the `ActionFields` wrapper, Nova-style:

```php
public function handle(Collection $models, ActionFields $fields): ActionResponse
{
    foreach ($models as $user) {
        Mail::to($user)->send(new ProfileMail($fields->subject, $fields->note));
    }

    return ActionResponse::message('Sent to '.$models->count().' user(s).');
}
```

### Destructive Actions

Subclass `DestructiveAction` (or call `->destructive()` on an instance) to require an extra confirmation. The CLI marks destructive actions with a `[DESTRUCTIVE]` prefix in the action menu and the confirmation prompt:

```php
class BanUserAction extends DestructiveAction
{
    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        foreach ($models as $user) {
            $user->update(['banned_at' => now()]);
        }

        return ActionResponse::message('Banned '.$models->count().' user(s).');
    }
}
```

### Queued Actions

Implement `Illuminate\Contracts\Queue\ShouldQueue` to dispatch the action in the background. The `ActionDispatcher` injects the models and fields onto the action before it is pushed to the bus, so the queued worker reuses the same `handle()`:

```php
use Illuminate\Contracts\Queue\ShouldQueue;

class EmailAccountProfileAction extends Action implements ShouldQueue
{
    // ...
}
```

When the action is queued, the CLI prints `Action queued for background processing.` instead of the action's own response.

### Response Helpers

- `ActionResponse::message(string $msg)` — success
- `ActionResponse::danger(string $msg)` — failure (printed in red by the CLI)

### Action Options

- `->name(string)` — override the display name in the menu (defaults to a headlined version of the class name with the trailing `Action` stripped)
- `->confirmText(string)` / `->confirmButtonText(string)` / `->cancelButtonText(string)` — customize the confirmation prompt
- `->destructive(bool = true)` / `->withoutConfirmation()` — toggle the prompt
- `->onConnection(string)` / `->onQueue(string)` — target a specific queue
- `->authorize(): bool` — override to gate the action; return `false` to short-circuit with a danger response

[← Back to README](../README.md)
