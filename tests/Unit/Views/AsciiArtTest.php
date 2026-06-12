<?php

namespace Repat\CliCrud\Tests\Unit\Views;

use Repat\CliCrud\Tests\TestCase;
use Repat\CliCrud\Views\AsciiArt;

class AsciiArtTest extends TestCase
{
    public function test_renders_text_as_ascii_art(): void
    {
        $result = AsciiArt::render('A');

        $this->assertStringContainsString('█████╗', $result);
        $this->assertStringContainsString('██╔══██╗', $result);
        $this->assertStringContainsString('███████║', $result);
    }

    public function test_converts_lowercase_to_uppercase(): void
    {
        $lowercase = AsciiArt::render('abc');
        $uppercase = AsciiArt::render('ABC');

        $this->assertEquals($uppercase, $lowercase);
    }

    public function test_renders_multiple_characters(): void
    {
        $result = AsciiArt::render('AB');

        $lines = explode("\n", $result);
        $this->assertCount(5, $lines);
    }

    public function test_renders_numbers(): void
    {
        $result = AsciiArt::render('123');

        $this->assertStringContainsString('██╗', $result);
        $this->assertStringContainsString('███║', $result);
    }

    public function test_renders_special_characters(): void
    {
        $result = AsciiArt::render('A-B');

        $this->assertStringContainsString('██████', $result);
    }

    public function test_renders_spaces(): void
    {
        $result = AsciiArt::render('A B');

        $lines = explode("\n", $result);
        $this->assertCount(5, $lines);
    }

    public function test_renders_default_text_when_empty(): void
    {
        $result = AsciiArt::render('');

        $this->assertStringContainsString('██████╗', $result);
        $this->assertStringContainsString('██╔════╝', $result);
    }

    public function test_renders_default_text_when_null(): void
    {
        $result = AsciiArt::render(null);

        $this->assertStringContainsString('██████╗', $result);
        $this->assertStringContainsString('██╔════╝', $result);
    }

    public function test_handles_unsupported_characters_as_spaces(): void
    {
        $result = AsciiArt::render('A@B');

        $lines = explode("\n", $result);
        $this->assertCount(5, $lines);
    }

    public function test_output_has_five_lines(): void
    {
        $result = AsciiArt::render('TEST');

        $lines = explode("\n", $result);
        $this->assertCount(5, $lines);
    }
}
