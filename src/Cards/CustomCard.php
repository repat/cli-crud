<?php

namespace Repat\CliCrud\Cards;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Repat\CliCrud\Resources\Resource;

class CustomCard extends Card
{
    public function __construct(
        string $title,
        protected Closure $contentResolver
    ) {
        parent::__construct($title);
    }

    public function render(Model $model, Resource $resource): string
    {
        $content = ($this->contentResolver)($model, $resource);

        if ($content instanceof \UnitEnum) {
            $content = $content->name;
        }

        return $this->renderBox(rtrim((string) $content, "\n"));
    }
}
