<?php

namespace Repat\CliCrud\Fields\Relations;

class MorphedByMany extends Relation
{
    public function getRelationType(): string
    {
        return 'morphedByMany';
    }
}
