<?php

namespace Repat\CliCrud\Tests\Unit\Forms;

use Repat\CliCrud\Fields\Json;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Forms\FormBuilder;
use Repat\CliCrud\Tests\TestCase;

class FormBuilderNullableTest extends TestCase
{
    public function test_nullable_text_field_converts_empty_string_to_null(): void
    {
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->onlyMethods(['promptForField'])
            ->getMock();

        $formBuilder->method('promptForField')
            ->willReturn('');

        $fields = [
            Text::make('Name', 'name')->nullable(),
        ];

        $data = $formBuilder->build($fields);

        $this->assertArrayHasKey('name', $data);
        $this->assertNull($data['name']);
    }

    public function test_nullable_json_field_converts_empty_string_to_null(): void
    {
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->onlyMethods(['promptForField'])
            ->getMock();

        $formBuilder->method('promptForField')
            ->willReturn('');

        $fields = [
            Json::make('Metadata', 'metadata')->nullable(),
        ];

        $data = $formBuilder->build($fields);

        $this->assertArrayHasKey('metadata', $data);
        $this->assertNull($data['metadata']);
    }

    public function test_nullable_field_with_value_keeps_value(): void
    {
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->onlyMethods(['promptForField'])
            ->getMock();

        $formBuilder->method('promptForField')
            ->willReturn('some value');

        $fields = [
            Text::make('Name', 'name')->nullable(),
        ];

        $data = $formBuilder->build($fields);

        $this->assertArrayHasKey('name', $data);
        $this->assertSame('some value', $data['name']);
    }

    public function test_not_in_forms_field_is_skipped(): void
    {
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->onlyMethods(['promptForField'])
            ->getMock();

        $formBuilder->method('promptForField')
            ->willReturn('should not be called');

        $fields = [
            Text::make('Included', 'included'),
            Text::make('Skipped', 'skipped')->notInForms(),
        ];

        $data = $formBuilder->build($fields);

        $this->assertArrayHasKey('included', $data);
        $this->assertArrayNotHasKey('skipped', $data);
    }
}
