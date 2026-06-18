<?php

namespace Repat\CliCrud\Fields\Relations;

use Repat\CliCrud\Concerns\DerivesName;
use Repat\CliCrud\Resources\Resource;

/**
 * @phpstan-consistent-constructor
 */
abstract class Relation
{
    use DerivesName;

    protected string $label;

    protected string $name;

    protected string $resourceClass;

    public function __construct(string $label, mixed $nameOrResource, mixed $resourceClass = null)
    {
        $this->label = $label;

        if ($resourceClass === null) {
            $this->resourceClass = is_string($nameOrResource) ? $nameOrResource : '';
            $this->name = $this->deriveName($label);
        } else {
            $this->name = is_string($nameOrResource) ? $nameOrResource : '';
            $this->resourceClass = is_string($resourceClass) ? $resourceClass : (is_array($resourceClass) && isset($resourceClass[0]) ? $resourceClass[0] : '');
        }
    }

    public static function make(string $label, mixed $nameOrResource, mixed $resourceClass = null): static
    {
        return new static($label, $nameOrResource, $resourceClass);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getResourceClass(): string
    {
        return $this->resourceClass;
    }

    public function getResource(): Resource
    {
        if ($this->resourceClass === '') {
            throw new \RuntimeException(
                "No resource class configured for relation '{$this->name}'. ".
                'Provide a class-string to a Resource, or for MorphTo relations call ->resources([...]) instead.'
            );
        }

        return new $this->resourceClass;
    }

    abstract public function getRelationType(): string;
}
