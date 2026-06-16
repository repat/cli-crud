<?php

namespace Repat\CliCrud\Tests\Unit\Support;

use Repat\CliCrud\Support\Theme;
use Repat\CliCrud\Tests\TestCase;

class ThemeTest extends TestCase
{
    public function test_null_returns_expected_ansi(): void
    {
        $this->assertSame("\e[90m", Theme::null());
    }

    public function test_true_returns_expected_ansi(): void
    {
        $this->assertSame("\e[32m", Theme::true());
    }

    public function test_false_returns_expected_ansi(): void
    {
        $this->assertSame("\e[31m", Theme::false());
    }

    public function test_reset_fg_returns_expected_ansi(): void
    {
        $this->assertSame("\e[39m", Theme::resetFg());
    }

    public function test_chart_colors_returns_array(): void
    {
        $colors = Theme::chartColors();
        $this->assertIsArray($colors);
        $this->assertCount(10, $colors);
    }

    public function test_preset_defaults_to_dark(): void
    {
        $this->assertSame('dark', Theme::preset());
    }

    public function test_json_key_dark_differs_from_light(): void
    {
        $darkKey = Theme::jsonKey();
        config(['cli-crud.themes.preset' => 'light']);
        $lightKey = Theme::jsonKey();
        $this->assertNotSame($darkKey, $lightKey);
    }

    public function test_individual_override_overrides_preset(): void
    {
        $original = Theme::null();
        config(['cli-crud.themes.null' => "\e[42m"]);
        $overridden = Theme::null();
        $this->assertSame("\e[42m", $overridden);
        config(['cli-crud.themes.null' => null]);
        $restored = Theme::null();
        $this->assertSame($original, $restored);
    }
}
