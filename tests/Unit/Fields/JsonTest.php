<?php

namespace Repat\CliCrud\Tests\Unit\Fields;

use Repat\CliCrud\Fields\Json;
use Repat\CliCrud\Tests\TestCase;

class JsonTest extends TestCase
{
    public function test_get_prompt_component_returns_textarea(): void
    {
        $field = Json::make('Settings');
        $this->assertEquals('textarea', $field->getPromptComponent());
    }

    public function test_get_rules_includes_json(): void
    {
        $field = Json::make('Settings');
        $rules = $field->getRules();
        $this->assertContains('json', $rules);
    }

    public function test_get_rules_includes_required_when_required(): void
    {
        $field = Json::make('Settings')->required();
        $rules = $field->getRules();
        $this->assertContains('required', $rules);
        $this->assertContains('json', $rules);
    }

    public function test_highlight_is_enabled_by_default(): void
    {
        $field = Json::make('Settings');
        $this->assertTrue($field->isHighlighted());
    }

    public function test_highlight_can_be_disabled(): void
    {
        $field = Json::make('Settings')->highlight(false);
        $this->assertFalse($field->isHighlighted());
    }

    public function test_highlight_can_be_re_enabled(): void
    {
        $field = Json::make('Settings')->highlight(false)->highlight(true);
        $this->assertTrue($field->isHighlighted());
    }

    public function test_highlight_returns_static_for_chaining(): void
    {
        $field = Json::make('Settings');
        $result = $field->highlight(false);
        $this->assertSame($field, $result);
    }

    public function test_get_prompt_options_has_validate_callback(): void
    {
        $field = Json::make('Settings');
        $options = $field->getPromptOptions();
        $this->assertArrayHasKey('validate', $options);
        $this->assertIsCallable($options['validate']);
    }

    public function test_validate_callback_accepts_valid_json_object(): void
    {
        $field = Json::make('Settings');
        $options = $field->getPromptOptions();
        $result = $options['validate']('{"key": "value"}');
        $this->assertNull($result);
    }

    public function test_validate_callback_accepts_valid_json_array(): void
    {
        $field = Json::make('Settings');
        $options = $field->getPromptOptions();
        $result = $options['validate']('[1, 2, 3]');
        $this->assertNull($result);
    }

    public function test_validate_callback_accepts_valid_json_string(): void
    {
        $field = Json::make('Settings');
        $options = $field->getPromptOptions();
        $result = $options['validate']('"hello"');
        $this->assertNull($result);
    }

    public function test_validate_callback_accepts_valid_json_number(): void
    {
        $field = Json::make('Settings');
        $options = $field->getPromptOptions();
        $result = $options['validate']('42');
        $this->assertNull($result);
    }

    public function test_validate_callback_accepts_valid_json_boolean(): void
    {
        $field = Json::make('Settings');
        $options = $field->getPromptOptions();
        $result = $options['validate']('true');
        $this->assertNull($result);
    }

    public function test_validate_callback_accepts_valid_json_null(): void
    {
        $field = Json::make('Settings');
        $options = $field->getPromptOptions();
        $result = $options['validate']('null');
        $this->assertNull($result);
    }

    public function test_validate_callback_rejects_invalid_json(): void
    {
        $field = Json::make('Settings');
        $options = $field->getPromptOptions();
        $result = $options['validate']('{invalid}');
        $this->assertNotNull($result);
        $this->assertStringContainsString('valid JSON', $result);
    }

    public function test_validate_callback_rejects_empty_string_when_required(): void
    {
        $field = Json::make('Settings');
        $options = $field->getPromptOptions();
        $result = $options['validate']('');
        $this->assertNotNull($result);
    }

    public function test_validate_callback_accepts_empty_string_when_nullable(): void
    {
        $field = Json::make('Settings')->nullable();
        $options = $field->getPromptOptions();
        $result = $options['validate']('');
        $this->assertNull($result);
    }

    public function test_validate_callback_still_rejects_invalid_json_when_nullable(): void
    {
        $field = Json::make('Settings')->nullable();
        $options = $field->getPromptOptions();
        $result = $options['validate']('{invalid}');
        $this->assertNotNull($result);
        $this->assertStringContainsString('valid JSON', $result);
    }

    public function test_validate_callback_accepts_valid_json_when_nullable(): void
    {
        $field = Json::make('Settings')->nullable();
        $options = $field->getPromptOptions();
        $result = $options['validate']('{"key": "value"}');
        $this->assertNull($result);
    }

    public function test_get_name_derived_from_label(): void
    {
        $field = Json::make('User Settings');
        $this->assertEquals('user_settings', $field->getName());
    }

    public function test_get_name_explicit(): void
    {
        $field = Json::make('User Settings', 'config');
        $this->assertEquals('config', $field->getName());
    }

    public function test_get_label(): void
    {
        $field = Json::make('User Settings');
        $this->assertEquals('User Settings', $field->getLabel());
    }
}
