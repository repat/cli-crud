<?php

namespace Repat\CliCrud\Tests\Fixtures;

class UserPolicy
{
    public function viewAny($user): bool
    {
        return false;
    }

    public function view($user, $model): bool
    {
        return false;
    }

    public function create($user): bool
    {
        return false;
    }

    public function update($user, $model): bool
    {
        return false;
    }

    public function delete($user, $model): bool
    {
        return false;
    }
}
