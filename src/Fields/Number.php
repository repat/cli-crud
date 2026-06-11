<?php

namespace Repat\CliCrud\Fields;

class Number extends Field
{
    protected bool $integer = true;

    public function float(): static
    {
        $this->integer = false;

        return $this;
    }

    public function getPromptComponent(): string
    {
        return 'text';
    }

    public function getPromptOptions(): array
    {
        return [
            'validate' => fn ($value) => $this->integer
                ? (filter_var($value, FILTER_VALIDATE_INT) !== false ? null : 'Please enter a valid integer.')
                : (is_numeric($value) ? null : 'Please enter a valid number.'),
        ];
    }

    public function getRules(): array
    {
        $rules = parent::getRules();
        $rules[] = $this->integer ? 'integer' : 'numeric';

        return $rules;
    }
}
