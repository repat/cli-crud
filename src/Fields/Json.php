<?php

namespace Repat\CliCrud\Fields;

class Json extends Field
{
    protected bool $highlight = true;

    public function highlight(bool $highlight = true): static
    {
        $this->highlight = $highlight;

        return $this;
    }

    public function isHighlighted(): bool
    {
        return $this->highlight;
    }

    public function getPromptComponent(): string
    {
        return 'textarea';
    }

    public function getRules(): array
    {
        $rules = parent::getRules();
        $rules[] = 'json';

        return $rules;
    }

    public function getPromptOptions(): array
    {
        return [
            'validate' => fn ($value) => json_decode($value) !== null || json_last_error() === JSON_ERROR_NONE
                ? null
                : 'Please enter valid JSON: '.json_last_error_msg(),
        ];
    }
}
