<?php

namespace Repat\CliCrud\Fields\Relations;

use Illuminate\Database\Eloquent\Model;
use Repat\CliCrud\Resources\Resource;

class MorphTo extends Relation
{
    /**
     * @var array<int, class-string<\Repat\CliCrud\Resources\Resource>>
     */
    protected array $resourceClasses = [];

    protected ?string $displayField = null;

    protected bool $required = false;

    public function __construct(string $label, mixed $nameOrResource = null, mixed $resources = [])
    {
        $this->label = $label;
        $this->name = is_string($nameOrResource) ? $nameOrResource : $this->deriveName($label);
        $this->resourceClasses = is_array($resources) ? $resources : [];
        $this->resourceClass = $this->resourceClasses[0] ?? '';
    }

    public static function make(string $label, mixed $nameOrResource = null, mixed $resources = []): static
    {
        return new static($label, $nameOrResource, $resources);
    }

    /**
     * @param  array<int, class-string<\Repat\CliCrud\Resources\Resource>>  $resources
     */
    public function resources(array $resources): static
    {
        $this->resourceClasses = $resources;
        $this->resourceClass = $resources[0] ?? '';

        return $this;
    }

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

    public function getTypeColumn(Model $model): string
    {
        return $this->name.'_type';
    }

    public function getIdColumn(Model $model): string
    {
        return $this->name.'_id';
    }

    /**
     * @return array<int, \Repat\CliCrud\Resources\Resource>
     */
    public function getResources(): array
    {
        return array_map(fn (string $class) => new $class, $this->resourceClasses);
    }

    /**
     * @return array<int, class-string<Model>>
     */
    public function getRelatedModels(): array
    {
        $models = [];

        foreach ($this->resourceClasses as $resourceClass) {
            $models[] = $resourceClass::getModel();
        }

        return $models;
    }

    /**
     * Returns the strings Laravel would write to {name}_type for each configured
     * resource. Respects the user's morphMap (returns the alias if registered,
     * otherwise the FQCN).
     *
     * @return array<int, string>
     */
    public function getMorphClassStrings(): array
    {
        return array_map(
            fn (string $modelClass) => (new $modelClass)->getMorphClass(),
            $this->getRelatedModels()
        );
    }

    public function getRelationType(): string
    {
        return 'morphTo';
    }
}
