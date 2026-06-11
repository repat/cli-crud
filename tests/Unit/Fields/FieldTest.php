<?php

namespace Repat\CliCrud\Tests\Unit\Fields;

use Repat\CliCrud\Fields\Boolean;
use Repat\CliCrud\Fields\DateTime;
use Repat\CliCrud\Fields\Number;
use Repat\CliCrud\Fields\Select;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Fields\Textarea;
use Repat\CliCrud\Tests\TestCase;

class FieldTest extends TestCase
{
    public function test_text_field_creation(): void
    {
        $field = Text::make('name');

        $this->assertEquals('name', $field->getName());
        $this->assertEquals('Name', $field->getLabel());
        $this->assertEquals('text', $field->getPromptComponent());
        $this->assertFalse($field->isRequired());
    }

    public function test_text_field_with_email_validation(): void
    {
        $field = Text::make('email')->email();

        $rules = $field->getRules();
        $this->assertContains('email', $rules);
    }

    public function test_text_field_required(): void
    {
        $field = Text::make('name')->required();

        $this->assertTrue($field->isRequired());
        $rules = $field->getRules();
        $this->assertContains('required', $rules);
    }

    public function test_text_field_nullable(): void
    {
        $field = Text::make('name')->nullable();

        $this->assertTrue($field->isNullable());
        $rules = $field->getRules();
        $this->assertContains('nullable', $rules);
    }

    public function test_number_field_creation(): void
    {
        $field = Number::make('age');

        $this->assertEquals('age', $field->getName());
        $this->assertEquals('text', $field->getPromptComponent());
    }

    public function test_number_field_float(): void
    {
        $field = Number::make('price')->float();

        $rules = $field->getRules();
        $this->assertContains('numeric', $rules);
    }

    public function test_boolean_field_creation(): void
    {
        $field = Boolean::make('is_active');

        $this->assertEquals('is_active', $field->getName());
        $this->assertEquals('confirm', $field->getPromptComponent());
    }

    public function test_datetime_field_creation(): void
    {
        $field = DateTime::make('created_at');

        $this->assertEquals('created_at', $field->getName());
        $this->assertEquals('text', $field->getPromptComponent());
    }

    public function test_datetime_field_custom_format(): void
    {
        $field = DateTime::make('created_at')->format('Y-m-d');

        $rules = $field->getRules();
        $this->assertContains('date_format:Y-m-d', $rules);
    }

    public function test_select_field_creation(): void
    {
        $field = Select::make('status')->options([
            'active' => 'Active',
            'inactive' => 'Inactive',
        ]);

        $this->assertEquals('status', $field->getName());
        $this->assertEquals('select', $field->getPromptComponent());

        $options = $field->getPromptOptions();
        $this->assertArrayHasKey('options', $options);
        $this->assertCount(2, $options['options']);
    }

    public function test_textarea_field_creation(): void
    {
        $field = Textarea::make('content');

        $this->assertEquals('content', $field->getName());
        $this->assertEquals('textarea', $field->getPromptComponent());
    }

    public function test_field_custom_label(): void
    {
        $field = Text::make('name')->label('Full Name');

        $this->assertEquals('Full Name', $field->getLabel());
    }

    public function test_field_default_value(): void
    {
        $field = Text::make('status')->default('active');

        $this->assertEquals('active', $field->getDefault());
    }

    public function test_field_custom_rules(): void
    {
        $field = Text::make('name')->rules(['string', 'max:255']);

        $rules = $field->getRules();
        $this->assertContains('string', $rules);
        $this->assertContains('max:255', $rules);
    }
}
