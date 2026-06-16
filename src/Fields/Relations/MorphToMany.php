<?php

namespace Repat\CliCrud\Fields\Relations;

class MorphToMany extends Relation
{
    public function getRelationType(): string
    {
        return 'morphToMany';
    }
}
