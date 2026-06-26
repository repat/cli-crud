<?php

namespace Repat\CliCrud\Actions;

class ActionFields
{
    /**
     * @param  array<string, mixed>  $values
     */
    public function __construct(protected array $values = [])
    {
    }

    public function __get(string $name): mixed
    {
        if (! array_key_exists($name, $this->values)) {
            throw new \OutOfBoundsException(
                "Action field [{$name}] was not provided. Did you declare it in fields()?"
            );
        }

        return $this->values[$name];
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->values);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->values[$name] = $value;
    }

    /**
     * @param  array<int, string>  $keys
     */
    public function only(array $keys): self
    {
        return new self(array_intersect_key($this->values, array_flip($keys)));
    }

    /**
     * @param  array<int, string>  $keys
     */
    public function except(array $keys): self
    {
        return new self(array_diff_key($this->values, array_flip($keys)));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->values;
    }
}
