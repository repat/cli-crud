<?php

namespace Repat\CliCrud\Tests\Fixtures\Actions;

use Illuminate\Database\Eloquent\Collection;
use Repat\CliCrud\Actions\ActionFields;
use Repat\CliCrud\Actions\ActionResponse;
use Repat\CliCrud\Actions\DestructiveAction;

class DestructiveActionFixture extends DestructiveAction
{
    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        return ActionResponse::message('destroyed');
    }
}
