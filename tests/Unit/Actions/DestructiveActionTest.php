<?php

namespace Repat\CliCrud\Tests\Unit\Actions;

use Illuminate\Database\Eloquent\Collection;
use Repat\CliCrud\Actions\Action;
use Repat\CliCrud\Actions\ActionFields;
use Repat\CliCrud\Actions\ActionResponse;
use Repat\CliCrud\Actions\DestructiveAction;
use Repat\CliCrud\Tests\TestCase;

class DestructiveActionTest extends TestCase
{
    public function test_is_destructive_by_default(): void
    {
        $action = new DestructiveActionFixture;

        $this->assertTrue($action->isDestructive());
    }

    public function test_subclass_inherits_from_action(): void
    {
        $action = new DestructiveActionFixture;

        $this->assertInstanceOf(Action::class, $action);
    }

    public function test_destructive_method_can_be_turned_off(): void
    {
        $action = (new DestructiveActionFixture)->destructive(false);

        $this->assertFalse($action->isDestructive());
    }

    public function test_destructive_method_can_be_turned_back_on(): void
    {
        $action = (new DestructiveActionFixture)->destructive(false)->destructive();

        $this->assertTrue($action->isDestructive());
    }
}

class DestructiveActionFixture extends DestructiveAction
{
    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        return ActionResponse::message('destroyed');
    }
}
