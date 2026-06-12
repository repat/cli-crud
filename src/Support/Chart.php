<?php

namespace Repat\CliCrud\Support;

class Chart
{
    protected const COLORS = [
        "\e[32m",
        "\e[34m",
        "\e[33m",
        "\e[35m",
        "\e[36m",
        "\e[91m",
        "\e[92m",
        "\e[93m",
        "\e[94m",
        "\e[95m",
    ];

    protected const RESET = "\e[0m";

    protected const BAR_CHAR = '█';

    protected const BAR_EMPTY = '░';

    protected const BULLET = '●';

    public static function bar(array $data, ?string $title = null, int $width = 40): string
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

        $barWidth = $width - $maxLabelLength - $maxValueLength - 4;

        foreach ($data as $label => $value) {
            $barLength = $maxValue > 0 ? (int) round(($value / $maxValue) * $barWidth) : 0;
            $bar = str_repeat(self::BAR_CHAR, $barLength).str_repeat(self::BAR_EMPTY, $barWidth - $barLength);

            $colorIndex = array_search($label, array_keys($data));
            $color = self::COLORS[$colorIndex % count(self::COLORS)];

            $paddedLabel = str_pad($label, $maxLabelLength);
            $paddedValue = str_pad((string) $value, $maxValueLength, ' ', STR_PAD_LEFT);

            $output .= "{$paddedLabel} {$color}{$bar}".self::RESET." {$paddedValue}\n";
        }

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
            $color = self::COLORS[$colorIndex % count(self::COLORS)];

            $paddedLabel = str_pad($label, $maxLabelLength);
            $percentStr = sprintf('%5.1f%%', $percentage);

            $output .= "{$color}".self::BULLET.self::RESET." {$paddedLabel} {$percentStr} {$color}{$bar}".self::RESET."\n";
        }

        return $output;
    }

    public static function horizontalBar(array $data, ?string $title = null, int $width = 60): string
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

        $barWidth = $width - $maxLabelLength - $maxValueLength - 6;

        $output .= '├'.str_repeat('─', $barWidth + 2).'┤'."\n";

        foreach ($data as $label => $value) {
            $barLength = $maxValue > 0 ? (int) round(($value / $maxValue) * $barWidth) : 0;
            $bar = str_repeat(self::BAR_CHAR, $barLength);

            $colorIndex = array_search($label, array_keys($data));
            $color = self::COLORS[$colorIndex % count(self::COLORS)];

            $paddedLabel = str_pad($label, $maxLabelLength);
            $paddedValue = str_pad((string) $value, $maxValueLength, ' ', STR_PAD_LEFT);

            $output .= "{$paddedLabel} │{$color}{$bar}".self::RESET.str_repeat(' ', $barWidth - $barLength)."│ {$paddedValue}\n";
        }

        return $output;
    }
}
