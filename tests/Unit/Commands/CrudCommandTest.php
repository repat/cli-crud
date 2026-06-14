<?php

namespace Repat\CliCrud\Tests\Unit\Commands;

use Repat\CliCrud\Authorization\Authorizer;
use Repat\CliCrud\Commands\CrudCommand;
use Repat\CliCrud\Forms\FormBuilder;
use Repat\CliCrud\Resources\ResourceRegistrar;
use Repat\CliCrud\Tables\TableRenderer;
use Repat\CliCrud\Tests\Fixtures\FormType;
use Repat\CliCrud\Tests\TestCase;
use Repat\CliCrud\Views\DetailViewRenderer;

class CrudCommandTest extends TestCase
{
    protected CrudCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new CrudCommand(
            app(ResourceRegistrar::class),
            app(Authorizer::class),
            app(TableRenderer::class),
            app(FormBuilder::class),
            app(DetailViewRenderer::class)
        );
    }

    public function test_center_pad_with_odd_header_width(): void
    {
        $result = $this->invokeCenterPad('✓', 6);

        $this->assertEquals('  ✓   ', $result);
        $this->assertEquals(6, mb_strlen($result));
    }

    public function test_center_pad_with_even_header_width(): void
    {
        $result = $this->invokeCenterPad('✓', 8);

        $this->assertEquals('   ✓    ', $result);
        $this->assertEquals(8, mb_strlen($result));
    }

    public function test_center_pad_with_value_longer_than_width(): void
    {
        $result = $this->invokeCenterPad('✓', 1);

        $this->assertEquals('✓', $result);
    }

    public function test_center_pad_with_value_equal_to_width(): void
    {
        $result = $this->invokeCenterPad('✓', 1);

        $this->assertEquals('✓', $result);
    }

    public function test_center_pad_with_ansi_codes(): void
    {
        $valueWithAnsi = "\e[32m✓\e[39m";
        $result = $this->invokeCenterPad($valueWithAnsi, 6);

        $this->assertEquals("  \e[32m✓\e[39m   ", $result);
        $this->assertEquals(6, $this->invokeGetVisibleLength($result));
    }

    public function test_center_pad_with_empty_string(): void
    {
        $result = $this->invokeCenterPad('', 4);

        $this->assertEquals('    ', $result);
        $this->assertEquals(4, mb_strlen($result));
    }

    public function test_get_visible_length_strips_ansi_codes(): void
    {
        $result = $this->invokeGetVisibleLength("\e[32m✓\e[39m");

        $this->assertEquals(1, $result);
    }

    public function test_get_visible_length_with_plain_text(): void
    {
        $result = $this->invokeGetVisibleLength('Hello');

        $this->assertEquals(5, $result);
    }

    public function test_get_visible_length_with_multiple_ansi_codes(): void
    {
        $result = $this->invokeGetVisibleLength("\e[32mHello\e[39m \e[31mWorld\e[39m");

        $this->assertEquals(11, $result);
    }

    public function test_get_visible_length_with_empty_string(): void
    {
        $result = $this->invokeGetVisibleLength('');

        $this->assertEquals(0, $result);
    }

    public function test_format_table_value_with_enum_returns_name(): void
    {
        $result = $this->invokeFormatTableValue(FormType::Draft);

        $this->assertEquals('Draft', $result);
    }

    public function test_format_table_value_for_datatable_with_enum_returns_name(): void
    {
        $result = $this->invokeFormatTableValueForDatatable(FormType::Published);

        $this->assertEquals('Published', $result);
    }

    protected function invokeCenterPad(string $value, int $width): string
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('centerPad');
        $method->setAccessible(true);

        return $method->invoke($this->command, $value, $width);
    }

    protected function invokeGetVisibleLength(string $value): int
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getVisibleLength');
        $method->setAccessible(true);

        return $method->invoke($this->command, $value);
    }

    protected function invokeFormatTableValue(mixed $value): string
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('formatTableValue');
        $method->setAccessible(true);

        return $method->invoke($this->command, $value);
    }

    protected function invokeFormatTableValueForDatatable(mixed $value): string
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('formatTableValueForDatatable');
        $method->setAccessible(true);

        return $method->invoke($this->command, $value);
    }
}
