<?php

namespace Repat\CliCrud\Fields\Relations;

class HasManyThrough extends Relation
{
    public function getRelationType(): string
    {
        return 'hasManyThrough';
    }
}
