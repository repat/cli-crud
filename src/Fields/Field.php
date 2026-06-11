<?php

namespace Repat\CliCrud\Fields;

abstract class Field
{
    protected string $name;
    protected ?string $label = null;
    protected bool $required = false;
    protected bool $nullable = false;
    protected mixed $default = null;
    protected array $rules = [];

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function required(): static
    {
        $this->required = true;
        return $this;
    }

    public function nullable(): static
    {
        $this->nullable = true;
        return $this;
    }

    public function default(mixed $value): static
    {
        $this->default = $value;
        return $this;
    }

    public function rules(array $rules): static
    {
        $this->rules = $rules;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label ?? str_replace('_', ' ', ucfirst($this->name));
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function isNullable(): bool
    {
        return $this->nullable;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }

    public function getRules(): array
    {
        $rules = $this->rules;

        if ($this->required) {
            array_unshift($rules, 'required');
        } elseif ($this->nullable) {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }

    abstract public function getPromptComponent(): string;

    public function getPromptOptions(): array
    {
        return [];
    }
}
