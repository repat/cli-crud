<?php

namespace Repat\CliCrud\Fields;

use Repat\CliCrud\Concerns\DerivesName;

/**
 * @phpstan-consistent-constructor
 */
abstract class Field
{
    use DerivesName;

    protected string $name;

    protected ?string $label = null;

    protected bool $required = false;

    protected bool $nullable = false;

    protected mixed $default = null;

    protected bool $showInForms = true;

    protected array $rules = [];

    protected bool $searchable = false;

    public function __construct(string $label, ?string $name = null)
    {
        $this->label = $label;
        $this->name = $name ?? $this->deriveName($label);
    }

    public static function make(string $label, ?string $name = null): static
    {
        return new static($label, $name);
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

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
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

    public function notInForms(): static
    {
        $this->showInForms = false;

        return $this;
    }

    public function isShownInForms(): bool
    {
        return $this->showInForms;
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
