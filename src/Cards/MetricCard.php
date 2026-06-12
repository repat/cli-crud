<?php

namespace Repat\CliCrud\Cards;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Repat\CliCrud\Resources\Resource;

class MetricCard extends Card
{
    public function __construct(
        string $title,
        protected Closure $valueResolver
    ) {
        parent::__construct($title);
    }

    public function render(Model $model, Resource $resource): string
    {
        $value = ($this->valueResolver)($model, $resource);

        return $this->renderBox((string) $value);
    }
}
