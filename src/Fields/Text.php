<?php

namespace Repat\CliCrud\Fields;

class Text extends Field
{
    protected bool $email = false;

    public function email(): static
    {
        $this->email = true;
        return $this;
    }

    public function getPromptComponent(): string
    {
        return 'text';
    }

    public function getPromptOptions(): array
    {
        $options = [];

        if ($this->email) {
            $options['validate'] = fn($value) => filter_var($value, FILTER_VALIDATE_EMAIL)
                ? null
                : 'Please enter a valid email address.';
        }

        return $options;
    }

    public function getRules(): array
    {
        $rules = parent::getRules();

        if ($this->email) {
            $rules[] = 'email';
        }

        return $rules;
    }
}
