<?php

namespace Repat\CliCrud\Support;

class Chart
{
    protected const BAR_CHAR = '█';

    protected const BAR_EMPTY = '░';

    public static function bar(array $data, ?string $title = null, int $width = 40, bool $showPercentages = false): string
    {
        if (empty($data)) {
            return '';
        }

        $output = '';

        if ($title !== null) {
            $output .= $title."\n\n";
        }

        $count = count($data);
        $maxValue = max($data);
        $maxValueLength = mb_strlen((string) $maxValue);
        $barHeight = 8;
        $colors = Theme::chartColors();
        $colorCount = count($colors);

        $values = array_values($data);
        $labels = array_keys($data);

        // Calculate bar width: one bar plus one space gap
        $barFillWidth = max(2, min(6, (int) floor(($width - $count) / $count)));
        $totalColumnWidth = $barFillWidth + 1;

        // Determine label width for alignment
        $labelWidth = max(array_map('mb_strlen', $labels));
        $labelWidth = max($labelWidth, $maxValueLength);

        $columnWidth = $barFillWidth + $labelWidth;

        // Build from top row down to bottom
        for ($row = $barHeight - 1; $row >= 0; $row--) {
            $threshold = $maxValue > 0 ? ($maxValue / $barHeight) * ($row + 1) : 0;

            for ($col = 0; $col < $count; $col++) {
                $value = $values[$col];
                $colorIndex = $col % $colorCount;
                $color = $colors[$colorIndex];

                if ($value >= $threshold) {
                    $bar = $color.str_repeat(self::BAR_CHAR, $barFillWidth).Theme::resetAll();
                } else {
                    $bar = str_repeat(' ', $barFillWidth);
                }

                $output .= ' '.$bar.str_repeat(' ', $labelWidth);
            }

            $output .= "\n";
        }

        // Labels row — match the bar cell width exactly (1 leading space +
        // $barFillWidth + $labelWidth), and left-align the label within that
        // cell so the label's left edge sits under the bar's left edge.
        for ($col = 0; $col < $count; $col++) {
            $label = $labels[$col];
            $padded = str_pad($label, $barFillWidth + $labelWidth, ' ', STR_PAD_RIGHT);
            $output .= ' '.$padded;
        }

        $output .= "\n";

        // Values row (or percentages row when percentage() is enabled) — same
        // alignment as the label row.
        for ($col = 0; $col < $count; $col++) {
            $cell = $showPercentages
                ? self::formatPercentage($values[$col], $values)
                : self::formatScalar($values[$col]);
            $padded = str_pad($cell, $barFillWidth + $labelWidth, ' ', STR_PAD_RIGHT);
            $output .= ' '.$padded;
        }

        $output .= "\n";

        return $output;
    }

    public static function horizontalBar(array $data, ?string $title = null, int $width = 60, bool $showPercentages = false): string
    {
        if (empty($data)) {
            return '';
        }

        $output = '';

        if ($title !== null) {
            $output .= $title."\n";
        }

        $maxLabelLength = max(array_map('mb_strlen', array_keys($data)));
        $maxValue = max($data);
        $maxValueLength = mb_strlen((string) $maxValue);

        // When showing percentages, the right-side column holds the percentage
        // string; we still right-align based on maxValueLength to preserve layout
        // when the flag is off (so toggling doesn't visually shift unrelated rows).
        $columnWidth = $maxValueLength;
        $barWidth = $width - $maxLabelLength - $columnWidth - 6;

        $values = array_values($data);

        foreach ($data as $label => $value) {
            $barLength = $maxValue > 0 ? (int) round(($value / $maxValue) * $barWidth) : 0;
            $bar = str_repeat(self::BAR_CHAR, $barLength);

            $colorIndex = array_search($label, array_keys($data));
            $color = Theme::chartColors()[$colorIndex % count(Theme::chartColors())];

            $paddedLabel = str_pad($label, $maxLabelLength);

            $rightCell = $showPercentages
                ? self::formatPercentage($value, $values)
                : self::formatScalar($value);
            $paddedRight = str_pad($rightCell, $columnWidth, ' ', STR_PAD_LEFT);

            $output .= "{$paddedLabel} │{$color}{$bar}".Theme::resetAll().str_repeat(' ', $barWidth - $barLength)."│ {$paddedRight}\n";
        }

        return $output;
    }

    /**
     * Format a single value as a percentage of the total of all values.
     * Used by the bar() and horizontalBar() bottom/right cells when
     * ChartCard::percentage() is enabled.
     */
    public static function scatter(array $data, ?string $title = null, int $width = 60): string
    {
        if (empty($data)) {
            return '';
        }

        $output = '';

        if ($title !== null) {
            $output .= $title."\n\n";
        }

        $labels = array_keys($data);
        $points = array_values($data);
        $colors = Theme::chartColors();
        $colorCount = count($colors);

        $xs = array_map(fn ($p) => $p[0], $points);
        $ys = array_map(fn ($p) => $p[1], $points);
        $minX = min($xs);
        $maxX = max($xs);
        $minY = min($ys);
        $maxY = max($ys);
        $rangeX = $maxX - $minX ?: 1;
        $rangeY = $maxY - $minY ?: 1;

        $yLabelWidth = max(array_map('mb_strlen', [(string) round($maxY), (string) round($minY)]));
        $plotWidth = $width - $yLabelWidth - 3;
        $plotHeight = max(8, min(18, (int) ($plotWidth / 2.5)));

        // Pre-compute pixel positions for each data point
        $pixelPositions = [];
        foreach ($points as $i => $point) {
            $col = (int) round(($point[0] - $minX) / $rangeX * ($plotWidth - 1));
            $row = (int) round(($point[1] - $minY) / $rangeY * ($plotHeight - 1));
            $pixelPositions[$col][$row] = $i;
        }

        // Build rows from top to bottom
        $lastYLabel = null;
        for ($row = $plotHeight - 1; $row >= 0; $row--) {
            $yVal = $minY + ($rangeY * $row / max($plotHeight - 1, 1));
            $yLabel = (string) round($yVal);
            $labelStr = ($yLabel !== $lastYLabel) ? str_pad($yLabel, $yLabelWidth, ' ', STR_PAD_LEFT) : str_repeat(' ', $yLabelWidth);
            $lastYLabel = $yLabel;
            $output .= $labelStr.' │';

            for ($col = 0; $col < $plotWidth; $col++) {
                if (isset($pixelPositions[$col][$row])) {
                    $idx = $pixelPositions[$col][$row];
                    $output .= $colors[$idx % $colorCount].'●'.Theme::resetAll();
                } else {
                    $output .= ' ';
                }
            }

            $output .= '│'."\n";
        }

        // X-axis
        $output .= str_repeat(' ', $yLabelWidth).' ├'.str_repeat('─', $plotWidth).'┤'."\n";

        // X-axis labels — min left, max right
        $output .= str_repeat(' ', $yLabelWidth + 2).$minX;
        $output .= str_repeat(' ', $plotWidth - mb_strlen($minX));
        $output .= $maxX."\n";

        // Legend
        $output .= "\n";
        foreach ($labels as $i => $label) {
            $output .= ' '.$colors[$i % $colorCount].'●'.Theme::resetAll().' '.$label;
            $output .= str_repeat(' ', 3);
        }
        $output .= "\n";

        return $output;
    }

    protected static function formatPercentage(mixed $value, array $allValues): string
    {
        $total = array_sum(array_map(
            fn ($v) => $v instanceof \UnitEnum ? 0 : (is_numeric($v) ? $v : 0),
            $allValues
        ));

        $numeric = is_numeric($value) ? $value : 0;

        $percentage = $total > 0 ? ($numeric / $total) * 100 : 0;

        return sprintf('%5.1f%%', $percentage);
    }

    /**
     * Format a single value as its scalar (string) representation, used by the
     * default value cells in bar() and horizontalBar().
     */
    protected static function formatScalar(mixed $value): string
    {
        if ($value instanceof \UnitEnum) {
            return $value->name;
        }

        return (string) $value;
    }
}
