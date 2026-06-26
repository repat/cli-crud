<?php

namespace Repat\CliCrud\Actions;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;

class ActionDispatcher
{
    public function __construct(protected Dispatcher $bus)
    {
    }

    public function dispatch(Action $action, Collection $models, ActionFields $fields): ActionResponse
    {
        if (! $action->authorize()) {
            return ActionResponse::danger('Not authorized to run this action.');
        }

        $action->models = $models;
        $action->fields = $fields;

        if ($action instanceof ShouldQueue) {
            $this->bus->dispatch($action);

            return ActionResponse::message('Action queued for background processing.');
        }

        return $action->handle($models, $fields);
    }
}
