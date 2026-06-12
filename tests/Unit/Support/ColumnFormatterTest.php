<?php

namespace Repat\CliCrud\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Repat\CliCrud\Support\ColumnFormatter;

class ColumnFormatterTest extends TestCase
{
    public function test_formats_id_as_uppercase(): void
    {
        $this->assertEquals('ID', ColumnFormatter::format('id'));
    }

    public function test_formats_uuid_as_uppercase(): void
    {
        $this->assertEquals('UUID', ColumnFormatter::format('uuid'));
    }

    public function test_formats_api_key_with_uppercase_api(): void
    {
        $this->assertEquals('API Key', ColumnFormatter::format('api_key'));
    }

    public function test_formats_url_as_uppercase(): void
    {
        $this->assertEquals('URL', ColumnFormatter::format('url'));
    }

    public function test_formats_regular_column_with_ucfirst(): void
    {
        $this->assertEquals('Name', ColumnFormatter::format('name'));
    }

    public function test_formats_underscored_column_with_spaces(): void
    {
        $this->assertEquals('Created at', ColumnFormatter::format('created_at'));
    }

    public function test_formats_multiple_underscores(): void
    {
        $this->assertEquals('First name field', ColumnFormatter::format('first_name_field'));
    }

    public function test_formats_already_formatted_column(): void
    {
        $this->assertEquals('Email', ColumnFormatter::format('email'));
    }
}
