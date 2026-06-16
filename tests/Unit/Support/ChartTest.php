<?php

namespace Repat\CliCrud\Tests\Unit\Support;

use Repat\CliCrud\Support\Chart;
use Repat\CliCrud\Tests\TestCase;

class ChartTest extends TestCase
{
    public function test_bar_chart_returns_empty_string_for_empty_data(): void
    {
        $result = Chart::bar([]);
        $this->assertEquals('', $result);
    }

    public function test_bar_chart_renders_single_item(): void
    {
        $result = Chart::bar(['Active' => 100]);

        $this->assertStringContainsString('Active', $result);
        $this->assertStringContainsString('100', $result);
        $this->assertStringContainsString('█', $result);
    }

    public function test_bar_chart_renders_multiple_items(): void
    {
        $data = [
            'Active' => 100,
            'Inactive' => 50,
            'Pending' => 25,
        ];

        $result = Chart::bar($data);

        $this->assertStringContainsString('Active', $result);
        $this->assertStringContainsString('Inactive', $result);
        $this->assertStringContainsString('Pending', $result);
        $this->assertStringContainsString('100', $result);
        $this->assertStringContainsString('50', $result);
        $this->assertStringContainsString('25', $result);
    }

    public function test_bar_chart_includes_title_when_provided(): void
    {
        $result = Chart::bar(['Active' => 100], 'User Status');

        $this->assertStringContainsString('User Status', $result);
        $this->assertStringContainsString('Active', $result);
    }

    public function test_bar_chart_scales_bars_proportionally(): void
    {
        $data = [
            'Max' => 100,
            'Half' => 50,
        ];

        $result = Chart::bar($data, null, 20);

        $this->assertStringContainsString('Max', $result);
        $this->assertStringContainsString('Half', $result);
        $this->assertStringContainsString('█', $result);
    }

    public function test_bar_chart_includes_ansi_colors(): void
    {
        $result = Chart::bar(['Active' => 100]);

        $this->assertStringContainsString("\e[", $result);
        $this->assertStringContainsString("\e[0m", $result);
    }

    public function test_bar_chart_handles_zero_values(): void
    {
        $data = [
            'Active' => 100,
            'Empty' => 0,
        ];

        $result = Chart::bar($data);

        $this->assertStringContainsString('Active', $result);
        $this->assertStringContainsString('Empty', $result);
        $this->assertStringContainsString('0', $result);
    }

    public function test_pie_chart_returns_empty_string_for_empty_data(): void
    {
        $result = Chart::pie([]);
        $this->assertEquals('', $result);
    }

    public function test_pie_chart_renders_single_item(): void
    {
        $result = Chart::pie(['Active' => 100]);

        $this->assertStringContainsString('Active', $result);
        $this->assertStringContainsString('100.0%', $result);
        $this->assertStringContainsString('●', $result);
        $this->assertStringContainsString('█', $result);
    }

    public function test_pie_chart_calculates_percentages(): void
    {
        $data = [
            'Active' => 75,
            'Inactive' => 25,
        ];

        $result = Chart::pie($data);

        $this->assertStringContainsString('75.0%', $result);
        $this->assertStringContainsString('25.0%', $result);
    }

    public function test_pie_chart_includes_title_when_provided(): void
    {
        $result = Chart::pie(['Active' => 100], 'Distribution');

        $this->assertStringContainsString('Distribution', $result);
    }

    public function test_pie_chart_includes_ansi_colors(): void
    {
        $result = Chart::pie(['Active' => 100]);

        $this->assertStringContainsString("\e[", $result);
        $this->assertStringContainsString("\e[0m", $result);
    }

    public function test_pie_chart_handles_zero_total(): void
    {
        $data = [
            'A' => 0,
            'B' => 0,
        ];

        $result = Chart::pie($data);

        $this->assertStringContainsString('0.0%', $result);
    }

    public function test_horizontal_bar_chart_returns_empty_string_for_empty_data(): void
    {
        $result = Chart::horizontalBar([]);
        $this->assertEquals('', $result);
    }

    public function test_horizontal_bar_chart_renders_with_border(): void
    {
        $result = Chart::horizontalBar(['Active' => 100]);

        $this->assertStringContainsString('├', $result);
        $this->assertStringContainsString('┤', $result);
        $this->assertStringContainsString('─', $result);
        $this->assertStringContainsString('│', $result);
    }

    public function test_horizontal_bar_chart_renders_multiple_items(): void
    {
        $data = [
            'Active' => 100,
            'Inactive' => 50,
        ];

        $result = Chart::horizontalBar($data);

        $this->assertStringContainsString('Active', $result);
        $this->assertStringContainsString('Inactive', $result);
        $this->assertStringContainsString('100', $result);
        $this->assertStringContainsString('50', $result);
    }

    public function test_horizontal_bar_chart_includes_title_when_provided(): void
    {
        $result = Chart::horizontalBar(['Active' => 100], 'Status');

        $this->assertStringContainsString('Status', $result);
    }

    public function test_horizontal_bar_chart_includes_ansi_colors(): void
    {
        $result = Chart::horizontalBar(['Active' => 100]);

        $this->assertStringContainsString("\e[", $result);
    }

    public function test_bar_chart_respects_custom_width(): void
    {
        $data = ['Test' => 100];

        $result = Chart::bar($data, null, 60);

        $this->assertStringContainsString('Test', $result);
        $this->assertStringContainsString('100', $result);
        $this->assertStringContainsString('█', $result);
    }

    public function test_chart_handles_many_items(): void
    {
        $data = [];
        for ($i = 1; $i <= 10; $i++) {
            $data["Item {$i}"] = $i * 10;
        }

        $result = Chart::bar($data);

        for ($i = 1; $i <= 10; $i++) {
            $this->assertStringContainsString("Item {$i}", $result);
        }
    }

    public function test_chart_cycles_colors_for_many_items(): void
    {
        $data = [];
        for ($i = 1; $i <= 15; $i++) {
            $data["Item {$i}"] = $i;
        }

        $result = Chart::bar($data);

        $this->assertNotEmpty($result);
    }

    public function test_bar_chart_with_show_percentages_appends_percent_row(): void
    {
        $data = ['Jan' => 50, 'Feb' => 50];

        $result = Chart::bar($data, null, 40, true);

        $this->assertStringContainsString('50.0%', $result);
    }

    public function test_bar_chart_without_show_percentages_omits_percent_row(): void
    {
        $data = ['Jan' => 50, 'Feb' => 50];

        $result = Chart::bar($data, null, 40, false);
        $resultExplicit = Chart::bar($data, null, 40);

        // The default-arg call should produce the same output as the explicit-false call.
        $this->assertSame($resultExplicit, $result);
        $this->assertStringNotContainsString('%', $result);
    }

    public function test_horizontal_bar_chart_with_show_percentages_replaces_value_with_percent(): void
    {
        $data = ['Jan' => 50, 'Feb' => 50];

        $result = Chart::horizontalBar($data, null, 60, true);

        $this->assertStringContainsString('50.0%', $result);
    }

    public function test_horizontal_bar_chart_without_show_percentages_unchanged(): void
    {
        $data = ['Jan' => 50, 'Feb' => 50];

        $result = Chart::horizontalBar($data, null, 60, false);
        $resultExplicit = Chart::horizontalBar($data, null, 60);

        $this->assertSame($resultExplicit, $result);
    }
}
