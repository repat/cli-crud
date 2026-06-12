<?php

namespace Repat\CliCrud\Fields\Relations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as EloquentBelongsTo;

class BelongsTo extends Relation
{
    protected ?string $displayField = null;

    protected bool $required = false;

    protected ?string $foreignKey = null;

    public function displayField(string $field): static
    {
        $this->displayField = $field;

        return $this;
    }

    public function getDisplayField(): ?string
    {
        return $this->displayField;
    }

    public function required(): static
    {
        $this->required = true;

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getForeignKey(Model $model): string
    {
        // Return cached value if available
        if ($this->foreignKey !== null) {
            return $this->foreignKey;
        }

        // Check if relationship method exists - throw exception if not
        if (! method_exists($model, $this->name)) {
            throw new \InvalidArgumentException(
                "Relationship method '{$this->name}' does not exist on model ".get_class($model).
                '. Please check your BelongsTo field configuration.'
            );
        }

        // Get relationship instance
        $relationship = $model->{$this->name}();

        // Verify it's a BelongsTo relationship
        if (! $relationship instanceof EloquentBelongsTo) {
            throw new \InvalidArgumentException(
                "Method '{$this->name}' on model ".get_class($model).
                ' is not a BelongsTo relationship.'
            );
        }

        // Cache and return the foreign key name
        $this->foreignKey = $relationship->getForeignKeyName();

        return $this->foreignKey;
    }

    public function getRelationType(): string
    {
        return 'belongsTo';
    }
}
