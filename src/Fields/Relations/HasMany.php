<?php

namespace Repat\CliCrud\Fields\Relations;

class HasMany extends Relation
{
    public function getRelationType(): string
    {
        return 'hasMany';
    }
}
