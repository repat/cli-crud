<?php

namespace Repat\CliCrud\Fields;

class DateTime extends Field
{
    protected string $format = 'Y-m-d H:i:s';

    public function format(string $format): static
    {
        $this->format = $format;

        return $this;
    }

    public function getPromptComponent(): string
    {
        return 'text';
    }

    public function getPromptOptions(): array
    {
        return [
            'placeholder' => $this->format,
            'validate' => fn ($value) => $this->isValidDate($value)
                ? null
                : "Please enter a valid date in format: {$this->format}",
        ];
    }

    public function getRules(): array
    {
        $rules = parent::getRules();
        $rules[] = 'date_format:'.$this->format;

        return $rules;
    }

    protected function isValidDate(string $value): bool
    {
        $date = \DateTime::createFromFormat($this->format, $value);

        return $date && $date->format($this->format) === $value;
    }
}
