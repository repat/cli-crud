<?php

namespace Repat\CliCrud\Fields\Relations;

class BelongsToMany extends Relation
{
    public function getRelationType(): string
    {
        return 'belongsToMany';
    }
}
