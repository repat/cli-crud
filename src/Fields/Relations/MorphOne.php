<?php

namespace Repat\CliCrud\Fields\Relations;

class MorphOne extends Relation
{
    public function getRelationType(): string
    {
        return 'morphOne';
    }
}
