<?php

namespace Repat\CliCrud\Fields\Relations;

use Repat\CliCrud\Resources\Resource;

abstract class Relation
{
    protected string $label;

    protected string $name;

    protected string $resourceClass;

    public function __construct(string $label, string $name, string $resourceClass)
    {
        $this->label = $label;
        $this->name = $name;
        $this->resourceClass = $resourceClass;
    }

    public static function make(string $label, string $name, string $resourceClass): static
    {
        return new static($label, $name, $resourceClass);
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

    /**
     * @return class-string<resource>
     */
    public function getResource(): Resource
    {
        return new $this->resourceClass;
    }

    abstract public function getRelationType(): string;
}
