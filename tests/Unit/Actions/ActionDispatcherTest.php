<?php

namespace Repat\CliCrud\Tests\Unit\Actions;

use Illuminate\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Bus;
use Repat\CliCrud\Actions\Action;
use Repat\CliCrud\Actions\ActionDispatcher;
use Repat\CliCrud\Actions\ActionFields;
use Repat\CliCrud\Actions\ActionResponse;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class ActionDispatcherTest extends TestCase
{
    public function test_sync_action_calls_handle_with_models_and_fields(): void
    {
        $user = User::create(['name' => 'Alice', 'email' => 'alice@example.com']);
        $action = new SpyAction;
        $fields = new ActionFields(['subject' => 'Hi']);
        $models = new Collection([$user]);

        $response = $this->dispatcher()->dispatch($action, $models, $fields);

        $this->assertInstanceOf(ActionResponse::class, $response);
        $this->assertEquals('ran with Hi', $response->getMessage());
        $this->assertSame($models, $action->calledWithModels);
        $this->assertSame($fields, $action->calledWithFields);
    }

    public function test_models_and_fields_are_set_on_action_before_handle(): void
    {
        $user = User::create(['name' => 'Bob', 'email' => 'bob@example.com']);
        $action = new RecordingSpyAction;
        $models = new Collection([$user]);
        $fields = new ActionFields(['k' => 'v']);

        $this->dispatcher()->dispatch($action, $models, $fields);

        $this->assertSame($models, $action->models);
        $this->assertSame($fields, $action->fields);
    }

    public function test_queueable_action_is_dispatched_via_bus(): void
    {
        Bus::fake();

        $user = User::create(['name' => 'Carol', 'email' => 'carol@example.com']);
        $action = new QueueableSpyAction;
        $models = new Collection([$user]);
        $fields = new ActionFields;

        $response = $this->dispatcher()->dispatch($action, $models, $fields);

        Bus::assertDispatched(QueueableSpyAction::class);
        $this->assertEquals('Action queued for background processing.', $response->getMessage());
    }

    public function test_unauthorized_action_returns_danger_response(): void
    {
        $user = User::create(['name' => 'Dan', 'email' => 'dan@example.com']);
        $action = new UnauthorizedSpyAction;
        $models = new Collection([$user]);
        $fields = new ActionFields;

        $response = $this->dispatcher()->dispatch($action, $models, $fields);

        $this->assertTrue($response->isDanger());
        $this->assertEquals('Not authorized to run this action.', $response->getMessage());
    }

    protected function dispatcher(): ActionDispatcher
    {
        return new ActionDispatcher($this->app->make(Dispatcher::class));
    }
}

class SpyAction extends Action
{
    public ?Collection $calledWithModels = null;

    public ?ActionFields $calledWithFields = null;

    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        $this->calledWithModels = $models;
        $this->calledWithFields = $fields;

        return ActionResponse::message('ran with '.$fields->subject);
    }
}

class RecordingSpyAction extends Action
{
    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        return ActionResponse::message('recorded');
    }
}

class QueueableSpyAction extends Action implements ShouldQueue
{
    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        return ActionResponse::message('ran');
    }
}

class UnauthorizedSpyAction extends Action
{
    public function authorize(): bool
    {
        return false;
    }

    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        return ActionResponse::message('should not run');
    }
}
