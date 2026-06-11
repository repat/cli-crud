<?php

namespace Repat\CliCrud\Exceptions;

use Exception;

class UnauthorizedException extends Exception
{
    public static function forAction(string $action, string $model): self
    {
        return new self(
            "You are not authorized to perform action '{$action}' on model '{$model}'."
        );
    }
}
