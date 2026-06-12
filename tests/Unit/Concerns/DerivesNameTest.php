<?php

namespace Repat\CliCrud\Tests\Unit\Concerns;

use Repat\CliCrud\Concerns\DerivesName;
use Repat\CliCrud\Tests\TestCase;

class DerivesNameTest extends TestCase
{
    use DerivesName;

    public function test_space_separated_words(): void
    {
        $this->assertEquals('first_name', $this->deriveName('First Name'));
    }

    public function test_camel_case(): void
    {
        $this->assertEquals('first_name', $this->deriveName('firstName'));
    }

    public function test_pascal_case(): void
    {
        $this->assertEquals('pascal_case', $this->deriveName('PascalCase'));
    }

    public function test_special_characters(): void
    {
        $this->assertEquals('users_name', $this->deriveName("User's Name"));
    }

    public function test_already_snake_case(): void
    {
        $this->assertEquals('already_snake', $this->deriveName('already_snake'));
    }

    public function test_leading_trailing_spaces(): void
    {
        $this->assertEquals('spaces', $this->deriveName('  spaces  '));
    }

    public function test_multiple_spaces(): void
    {
        $this->assertEquals('multiple_spaces', $this->deriveName('Multiple   Spaces'));
    }

    public function test_special_chars_only(): void
    {
        $this->assertEquals('special_chars', $this->deriveName('Special!@#$%Chars'));
    }

    public function test_leading_underscore(): void
    {
        $this->assertEquals('leading_underscore', $this->deriveName('_leading_underscore'));
    }

    public function test_trailing_underscore(): void
    {
        $this->assertEquals('trailing_underscore', $this->deriveName('trailing_underscore_'));
    }

    public function test_multiple_underscores(): void
    {
        $this->assertEquals('multiple_underscores', $this->deriveName('___multiple___underscores___'));
    }

    public function test_empty_string(): void
    {
        $this->assertEquals('', $this->deriveName(''));
    }

    public function test_single_word(): void
    {
        $this->assertEquals('posts', $this->deriveName('Posts'));
    }

    public function test_is_active(): void
    {
        $this->assertEquals('is_active', $this->deriveName('Is Active'));
    }

    public function test_mixed_case_with_numbers(): void
    {
        $this->assertEquals('field1_name', $this->deriveName('Field1Name'));
    }

    public function test_unicode_characters(): void
    {
        $this->assertEquals('caf', $this->deriveName('Café'));
    }

    public function test_all_special_characters(): void
    {
        $this->assertEquals('', $this->deriveName('!@#$%^&*()'));
    }
}
