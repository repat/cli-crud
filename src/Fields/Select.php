<?php

namespace Repat\CliCrud\Fields;

class Select extends Field
{
    protected array $options = [];

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function getPromptComponent(): string
    {
        return 'select';
    }

    public function getPromptOptions(): array
    {
        return [
            'options' => $this->options,
        ];
    }

    public function getRules(): array
    {
        $rules = parent::getRules();

        if (!empty($this->options)) {
            $rules[] = 'in:' . implode(',', array_keys($this->options));
        }

        return $rules;
    }
}
