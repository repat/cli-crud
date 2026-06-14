<?php

namespace Repat\CliCrud\Actions;

abstract class DestructiveAction extends Action
{
    protected bool $destructive = true;
}
