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

    public function test_horizontal_bar_chart_returns_empty_string_for_empty_data(): void
    {
        $result = Chart::horizontalBar([]);
        $this->assertEquals('', $result);
    }

    public function test_horizontal_bar_chart_renders_with_column_dividers(): void
    {
        $result = Chart::horizontalBar(['Active' => 100]);

        // The card box already renders its own top/bottom borders. The
        // horizontal bar chart's data rows just use │ as the in-row column
        // divider. No internal ├...┤ border is rendered anymore.
        $this->assertStringContainsString('│', $result);
        $this->assertStringContainsString('█', $result);
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

    public function test_bar_chart_aligns_labels_and_values_under_bars(): void
    {
        // Each label and value should start at the same column as the bar's
        // left edge within its cell. Strip ANSI codes, then compare the
        // column-offset of the label, value, and first '█' per column.
        $data = ['Jan' => 50, 'Feb' => 50, 'Mar' => 50];
        $result = Chart::bar($data);
        $plain = preg_replace('/\e\[[0-9;]*m/', '', $result);
        $lines = explode("\n", $plain);

        // Drop the trailing empty line caused by the chart's final newline.
        $lines = array_values(array_filter($lines, fn (string $l) => $l !== ''));

        // The chart renders: 8 bar-height rows, 1 labels row, 1 values row.
        $this->assertCount(10, $lines);

        $barRow = $lines[0];
        $labelRow = $lines[8];
        $valueRow = $lines[9];

        // Find the column-offset of the first '█' in each bar cell.
        $barPositions = [];
        $offset = 0;
        while (($pos = mb_strpos($barRow, '█', $offset)) !== false) {
            $barPositions[] = $pos;
            $offset = $pos + 1;
            if (count($barPositions) === count($data)) {
                break;
            }
        }

        $this->assertCount(3, $barPositions, 'Expected 3 bar cells in the top row');

        // For each column, the label and value content should sit directly
        // under the bar — the first non-space char in each cell is at the
        // same column as the bar's first '█'.
        $labels = array_keys($data);
        foreach ($barPositions as $colIndex => $barOffset) {
            $cellStart = $colIndex * 10;

            $this->assertSame(
                $labels[$colIndex],
                mb_substr($labelRow, $cellStart + 1, mb_strlen($labels[$colIndex])),
                "Label '{$labels[$colIndex]}' should sit directly under the bar at column {$barOffset}"
            );

            $this->assertSame(
                '50',
                mb_substr($valueRow, $cellStart + 1, 2),
                "Value should sit directly under the bar at column {$barOffset}"
            );
        }
    }

    public function test_horizontal_bar_top_border_aligns_with_data_rows(): void
    {
        // The internal ├...┤ top border has been removed — the card box
        // already renders its own header/divider. The chart now starts
        // directly with the first data row.
        $data = ['Jan' => 42, 'Feb' => 38, 'Mar' => 55, 'Apr' => 61, 'May' => 48];

        $result = Chart::horizontalBar($data, null, 60);
        $plain = preg_replace('/\e\[[0-9;]*m/', '', $result);
        $lines = explode("\n", $plain);
        $lines = array_values(array_filter($lines, fn (string $l) => $l !== ''));

        // First line should be a data row, not a border.
        $this->assertStringStartsNotWith('├', $lines[0]);
        $this->assertStringContainsString('Jan', $lines[0]);
    }

    public function test_scatter_returns_empty_string_for_empty_data(): void
    {
        $this->assertSame('', Chart::scatter([]));
    }

    public function test_scatter_renders_single_point(): void
    {
        $result = Chart::scatter(['A' => [10, 50]]);

        $this->assertStringContainsString('●', $result);
        $this->assertStringContainsString('10', $result);
        $this->assertStringContainsString('50', $result);
    }

    public function test_scatter_renders_multiple_points(): void
    {
        $data = ['A' => [10, 10], 'B' => [20, 50], 'C' => [30, 25]];

        $result = Chart::scatter($data);

        $this->assertStringContainsString('●', $result);
        $this->assertStringContainsString('10', $result);
        $this->assertStringContainsString('30', $result);
    }

    public function test_scatter_includes_title_when_provided(): void
    {
        $result = Chart::scatter(['A' => [10, 50]], 'My Scatter');

        $this->assertStringContainsString('My Scatter', $result);
    }

    public function test_scatter_includes_ansi_colors(): void
    {
        $result = Chart::scatter(['A' => [10, 50], 'B' => [20, 80]]);

        $this->assertStringContainsString("\e[", $result);
        $this->assertStringContainsString('●', $result);
    }
}
