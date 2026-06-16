<?php

namespace Repat\CliCrud\Fields\Relations;

class MorphMany extends Relation
{
    public function getRelationType(): string
    {
        return 'morphMany';
    }
}
