<?php

namespace Repat\CliCrud\Tests\Fixtures\Actions;

use Illuminate\Database\Eloquent\Collection;
use Repat\CliCrud\Actions\Action;
use Repat\CliCrud\Actions\ActionFields;
use Repat\CliCrud\Actions\ActionResponse;

class FooBarAction extends Action
{
    public function handle(Collection $models, ActionFields $fields): ActionResponse
    {
        return ActionResponse::message('ok');
    }
}
