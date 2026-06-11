<?php

namespace Repat\CliCrud\Fields;

class Boolean extends Field
{
    public function getPromptComponent(): string
    {
        return 'confirm';
    }

    public function getRules(): array
    {
        return ['boolean'];
    }
}
