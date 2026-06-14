<?php

namespace Repat\CliCrud\Tests\Unit\Fields;

use Repat\CliCrud\Fields\Textarea;
use Repat\CliCrud\Tests\TestCase;

class TextareaTest extends TestCase
{
    public function test_markdown_disabled_by_default(): void
    {
        $field = Textarea::make('Content', 'content');

        $this->assertFalse($field->isMarkdown());
    }

    public function test_markdown_enables_flag(): void
    {
        $field = Textarea::make('Content', 'content')->markdown();

        $this->assertTrue($field->isMarkdown());
    }

    public function test_markdown_can_be_disabled(): void
    {
        $field = Textarea::make('Content', 'content')->markdown()->markdown(false);

        $this->assertFalse($field->isMarkdown());
    }

    public function test_markdown_returns_static_for_chaining(): void
    {
        $field = Textarea::make('Content', 'content');
        $result = $field->markdown();

        $this->assertSame($field, $result);
    }

    public function test_get_prompt_component_returns_textarea(): void
    {
        $field = Textarea::make('Content', 'content');

        $this->assertEquals('textarea', $field->getPromptComponent());
    }
}
