<?php

namespace Repat\CliCrud\Fields\Relations;

use Repat\CliCrud\Resources\Resource;

abstract class Relation
{
    protected string $name;
    protected string $resourceClass;

    public function __construct(string $name, string $resourceClass)
    {
        $this->name = $name;
        $this->resourceClass = $resourceClass;
    }

    public static function make(string $name, string $resourceClass): static
    {
        return new static($name, $resourceClass);
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
     * @return class-string<Resource>
     */
    public function getResource(): Resource
    {
        return new $this->resourceClass();
    }

    abstract public function getRelationType(): string;
}
