<?php

namespace Repat\CliCrud\Fields;

class Textarea extends Field
{
    protected bool $markdown = false;

    public function markdown(bool $markdown = true): static
    {
        $this->markdown = $markdown;

        return $this;
    }

    public function isMarkdown(): bool
    {
        return $this->markdown;
    }

    public function getPromptComponent(): string
    {
        return 'textarea';
    }
}
