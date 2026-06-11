<?php

namespace Repat\CliCrud\Fields\Relations;

class HasOne extends Relation
{
    public function getRelationType(): string
    {
        return 'hasOne';
    }
}
