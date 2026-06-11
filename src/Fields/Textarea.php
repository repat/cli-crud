<?php

namespace Repat\CliCrud\Fields;

class Textarea extends Field
{
    public function getPromptComponent(): string
    {
        return 'textarea';
    }
}
