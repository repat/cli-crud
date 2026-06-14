<?php

namespace Repat\CliCrud\Tests\Unit\Resources;

use Illuminate\Database\Eloquent\Collection;
use Repat\CliCrud\Actions\Action;
use Repat\CliCrud\Actions\ActionFields;
use Repat\CliCrud\Actions\ActionResponse;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class ResourceActionsTest extends TestCase
{
    public function test_default_actions_returns_empty_array(): void
    {
        $this->assertSame([], BareResource::actions());
    }

    public function test_get_actions_returns_empty_array_for_resource_without_actions(): void
    {
        $this->assertSame([], BareResource::getActions());
    }

    public function test_get_actions_resolves_class_strings_to_instances(): void
    {
        $actions = ActionedResourceWithClasses::getActions();

        $this->assertCount(1, $actions);
        $this->assertInstanceOf(FixtureAction::class, $actions[0]);
    }

    public function test_get_actions_preserves_prebuilt_instances(): void
    {
        $actions = ActionedResourceWithInstances::getActions();

        $this->assertCount(2, $actions);
        $this->assertInstanceOf(FixtureAction::class, $actions[0]);
        $this->assertInstanceOf(DestructiveFixtureAction::class, $actions[1]);
        $this->assertTrue($actions[1]->isDestructive());
    }

    public function test_get_actions_handles_mix_of_classes_and_instances(): void
    {
        $actions = ActionedResourceMixed::getActions();

        $this->assertCount(2, $actions);
        $this->assertInstanceOf(FixtureAction::class, $actions[0]);
        $this->assertSame($actions[1], ActionedResourceMixed::$sharedInstance);
    }
}

class FixtureAction extends Action
{
    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        return ActionResponse::message('ok');
    }
}

class DestructiveFixtureAction extends Action
{
    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        return ActionResponse::message('destroyed');
    }
}

class BareResource extends Resource
{
    protected static string $model = User::class;

    protected static string $label = 'Bare';

    protected static string $singularLabel = 'Bare';

    public static function fields(): array
    {
        return [];
    }

    public static function tableColumns(): array
    {
        return ['id'];
    }
}

class ActionedResourceWithClasses extends Resource
{
    protected static string $model = User::class;

    protected static string $label = 'Actioned';

    protected static string $singularLabel = 'Actioned';

    public static function fields(): array
    {
        return [];
    }

    public static function tableColumns(): array
    {
        return ['id'];
    }

    public static function actions(): array
    {
        return [FixtureAction::class];
    }
}

class ActionedResourceWithInstances extends Resource
{
    protected static string $model = User::class;

    protected static string $label = 'Actioned';

    protected static string $singularLabel = 'Actioned';

    public static function fields(): array
    {
        return [];
    }

    public static function tableColumns(): array
    {
        return ['id'];
    }

    public static function actions(): array
    {
        return [
            FixtureAction::make(),
            DestructiveFixtureAction::make()->destructive(),
        ];
    }
}

class ActionedResourceMixed extends Resource
{
    public static Action $sharedInstance;

    protected static string $model = User::class;

    protected static string $label = 'Actioned';

    protected static string $singularLabel = 'Actioned';

    public static function fields(): array
    {
        return [];
    }

    public static function tableColumns(): array
    {
        return ['id'];
    }

    public static function actions(): array
    {
        self::$sharedInstance = FixtureAction::make();

        return [
            FixtureAction::class,
            self::$sharedInstance,
        ];
    }
}
