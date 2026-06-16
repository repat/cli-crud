<?php

namespace Repat\CliCrud\Support;

class Chart
{
    protected const BAR_CHAR = '█';

    protected const BAR_EMPTY = '░';

    protected const BULLET = '●';

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

        // Labels row
        for ($col = 0; $col < $count; $col++) {
            $label = $labels[$col];
            $padded = str_pad($label, $labelWidth + $barFillWidth - mb_strlen($label), ' ', STR_PAD_BOTH);
            $output .= ' '.$padded;
        }

        $output .= "\n";

        // Values row (or percentages row when percentage() is enabled)
        for ($col = 0; $col < $count; $col++) {
            $cell = $showPercentages
                ? self::formatPercentage($values[$col], $values)
                : self::formatScalar($values[$col]);
            $padded = str_pad($cell, $labelWidth + $barFillWidth - mb_strlen($cell), ' ', STR_PAD_BOTH);
            $output .= ' '.$padded;
        }

        $output .= "\n";

        return $output;
    }

    public static function pie(array $data, ?string $title = null, int $width = 40): string
    {
        if (empty($data)) {
            return '';
        }

        $output = '';

        if ($title !== null) {
            $output .= $title."\n";
        }

        $total = array_sum($data);
        $maxLabelLength = max(array_map('mb_strlen', array_keys($data)));

        $barWidth = $width - $maxLabelLength - 8;

        foreach ($data as $label => $value) {
            $percentage = $total > 0 ? ($value / $total) * 100 : 0;
            $barLength = (int) round(($percentage / 100) * $barWidth);
            $bar = str_repeat(self::BAR_CHAR, $barLength);

            $colorIndex = array_search($label, array_keys($data));
            $color = Theme::chartColors()[$colorIndex % count(Theme::chartColors())];

            $paddedLabel = str_pad($label, $maxLabelLength);
            $percentStr = sprintf('%5.1f%%', $percentage);

            $output .= "{$color}".self::BULLET.Theme::resetAll()." {$paddedLabel} {$percentStr} {$color}{$bar}".Theme::resetAll()."\n";
        }

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

        $output .= '├'.str_repeat('─', $barWidth + 2).'┤'."\n";

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
