<?php

namespace Repat\CliCrud\Tests\Unit\Support;

use Repat\CliCrud\Support\ColumnTypeMapper;
use Repat\CliCrud\Tests\Fixtures\FormType;
use Repat\CliCrud\Tests\TestCase;

class ColumnTypeMapperTest extends TestCase
{
    public function test_name_for_value_with_backed_enum_returns_name(): void
    {
        $this->assertSame('Draft', ColumnTypeMapper::nameForValue(FormType::Draft));
    }

    public function test_name_for_value_with_null_returns_null_string(): void
    {
        $this->assertSame('', ColumnTypeMapper::nameForValue(null));
    }

    public function test_name_for_value_with_bool_returns_string(): void
    {
        $this->assertSame('1', ColumnTypeMapper::nameForValue(true));
        $this->assertSame('', ColumnTypeMapper::nameForValue(false));
    }

    public function test_name_for_value_with_int_returns_string(): void
    {
        $this->assertSame('42', ColumnTypeMapper::nameForValue(42));
    }

    public function test_name_for_value_with_string_returns_same(): void
    {
        $this->assertSame('hello', ColumnTypeMapper::nameForValue('hello'));
    }

    public function test_scalar_for_value_with_backed_enum_returns_value(): void
    {
        $this->assertSame('draft', ColumnTypeMapper::scalarForValue(FormType::Draft));
    }

    public function test_scalar_for_value_with_null(): void
    {
        $this->assertNull(ColumnTypeMapper::scalarForValue(null));
    }

    public function test_scalar_for_value_with_bool(): void
    {
        $this->assertTrue(ColumnTypeMapper::scalarForValue(true));
    }

    public function test_scalar_for_value_with_int(): void
    {
        $this->assertSame(42, ColumnTypeMapper::scalarForValue(42));
    }

    public function test_scalar_for_value_with_string(): void
    {
        $this->assertSame('hello', ColumnTypeMapper::scalarForValue('hello'));
    }
}
