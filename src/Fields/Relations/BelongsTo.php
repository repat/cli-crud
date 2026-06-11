<?php

namespace Repat\CliCrud\Fields\Relations;

class BelongsTo extends Relation
{
    protected ?string $displayField = null;

    public function displayField(string $field): static
    {
        $this->displayField = $field;

        return $this;
    }

    public function getDisplayField(): ?string
    {
        return $this->displayField;
    }

    public function getRelationType(): string
    {
        return 'belongsTo';
    }
}
