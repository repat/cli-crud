<?php

namespace Repat\CliCrud\Fields\Relations;

use Repat\CliCrud\Concerns\DerivesName;
use Repat\CliCrud\Resources\Resource;

abstract class Relation
{
    use DerivesName;

    protected string $label;

    protected string $name;

    protected string $resourceClass;

    public function __construct(string $label, string $nameOrResource, ?string $resourceClass = null)
    {
        $this->label = $label;

        if ($resourceClass === null) {
            $this->resourceClass = $nameOrResource;
            $this->name = $this->deriveName($label);
        } else {
            $this->name = $nameOrResource;
            $this->resourceClass = $resourceClass;
        }
    }

    public static function make(string $label, string $nameOrResource, ?string $resourceClass = null): static
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
        return new $this->resourceClass;
    }

    abstract public function getRelationType(): string;
}
