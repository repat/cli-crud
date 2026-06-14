<?php

namespace Repat\CliCrud\Tests\Unit\Actions;

use Illuminate\Database\Eloquent\Collection;
use Repat\CliCrud\Actions\Action;
use Repat\CliCrud\Actions\ActionFields;
use Repat\CliCrud\Actions\ActionResponse;
use Repat\CliCrud\Tests\TestCase;

class ActionTest extends TestCase
{
    public function test_default_name_derives_from_class_basename(): void
    {
        $this->assertEquals('Activate Foo Bar', (new ActivateFooBarAction)->getName());
    }

    public function test_name_override(): void
    {
        $action = (new FooBarAction)->name('Custom Label');

        $this->assertEquals('Custom Label', $action->getName());
    }

    public function test_name_returns_static_for_chaining(): void
    {
        $action = new FooBarAction;

        $this->assertSame($action, $action->name('X'));
    }

    public function test_is_not_destructive_by_default(): void
    {
        $this->assertFalse((new FooBarAction)->isDestructive());
    }

    public function test_destructive_flips_flag(): void
    {
        $action = (new FooBarAction)->destructive();

        $this->assertTrue($action->isDestructive());
    }

    public function test_destructive_off_resets(): void
    {
        $action = (new FooBarAction)->destructive()->destructive(false);

        $this->assertFalse($action->isDestructive());
    }

    public function test_destructive_returns_static(): void
    {
        $action = new FooBarAction;

        $this->assertSame($action, $action->destructive());
    }

    public function test_requires_confirmation_by_default(): void
    {
        $this->assertTrue((new FooBarAction)->requiresConfirmation());
    }

    public function test_without_confirmation_disables(): void
    {
        $action = (new FooBarAction)->withoutConfirmation();

        $this->assertFalse($action->requiresConfirmation());
    }

    public function test_without_confirmation_returns_static(): void
    {
        $action = new FooBarAction;

        $this->assertSame($action, $action->withoutConfirmation());
    }

    public function test_confirm_text_default_is_null(): void
    {
        $this->assertNull((new FooBarAction)->getConfirmText());
    }

    public function test_confirm_text_setter(): void
    {
        $action = (new FooBarAction)->confirmText('Are you sure?');

        $this->assertEquals('Are you sure?', $action->getConfirmText());
    }

    public function test_confirm_button_text_returns_static(): void
    {
        $action = new FooBarAction;

        $this->assertSame($action, $action->confirmButtonText('Go'));
    }

    public function test_cancel_button_text_returns_static(): void
    {
        $action = new FooBarAction;

        $this->assertSame($action, $action->cancelButtonText('Stop'));
    }

    public function test_on_connection_setter(): void
    {
        $action = (new FooBarAction)->onConnection('redis');

        $this->assertEquals('redis', $action->connection);
    }

    public function test_on_queue_setter(): void
    {
        $action = (new FooBarAction)->onQueue('high');

        $this->assertEquals('high', $action->queue);
    }

    public function test_fields_default_is_empty_array(): void
    {
        $this->assertSame([], (new FooBarAction)->fields());
    }

    public function test_authorize_default_is_true(): void
    {
        $this->assertTrue((new FooBarAction)->authorize());
    }

    public function test_make_static_constructor(): void
    {
        $action = FooBarAction::make();

        $this->assertInstanceOf(FooBarAction::class, $action);
    }

    public function test_ask_for_fields_with_no_fields_returns_empty(): void
    {
        $fields = (new FooBarAction)->askForFields();

        $this->assertInstanceOf(ActionFields::class, $fields);
        $this->assertSame([], $fields->toArray());
    }

    public function test_models_property_is_public(): void
    {
        $reflection = new \ReflectionClass(Action::class);
        $prop = $reflection->getProperty('models');

        $this->assertTrue($prop->isPublic());
    }

    public function test_fields_property_is_public(): void
    {
        $reflection = new \ReflectionClass(Action::class);
        $prop = $reflection->getProperty('fields');

        $this->assertTrue($prop->isPublic());
    }
}

class FooBarAction extends Action
{
    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        return ActionResponse::message('ok');
    }
}

class ActivateFooBarAction extends Action
{
    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        return ActionResponse::message('ok');
    }
}
