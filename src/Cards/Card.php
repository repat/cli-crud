<?php

namespace Repat\CliCrud\Cards;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Repat\CliCrud\Resources\Resource;

abstract class Card
{
    protected string $position = 'after';

    public function __construct(
        protected string $title
    ) {}

    public static function chart(string $title, Closure $dataResolver): ChartCard
    {
        return new ChartCard($title, $dataResolver);
    }

    public static function custom(string $title, Closure $contentResolver): CustomCard
    {
        return new CustomCard($title, $contentResolver);
    }

    public function before(): static
    {
        $this->position = 'before';

        return $this;
    }

    public function after(): static
    {
        $this->position = 'after';

        return $this;
    }

    public function getPosition(): string
    {
        return $this->position;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    abstract public function render(Model $model, Resource $resource): string;

    protected function renderBox(string $content): string
    {
        $lines = explode("\n", $content);

        // Strip ANSI escape codes for width measurement — they take 0 visible
        // columns but are counted by mb_strlen, which would otherwise inflate
        // the box width and leave trailing padding inside it.
        $plainLines = array_map(
            fn (string $line) => preg_replace('/\e\[[0-9;]*m/', '', $line),
            $lines
        );

        $maxWidth = max(array_map('mb_strlen', $plainLines));
        $maxWidth = max($maxWidth, mb_strlen($this->title) + 4);
        $boxWidth = min($maxWidth + 4, 120);

        $output = '';
        $output .= '╭'.str_repeat('─', $boxWidth - 2).'╮'."\n";
        $output .= '│ '.str_pad($this->title, $boxWidth - 4).' │'."\n";
        $output .= '├'.str_repeat('─', $boxWidth - 2).'┤'."\n";

        foreach ($lines as $i => $line) {
            $visibleLen = mb_strlen($plainLines[$i]);
            $padding = max(0, $boxWidth - 4 - $visibleLen);
            $output .= '│ '.$line.str_repeat(' ', $padding).' │'."\n";
        }

        $output .= '╰'.str_repeat('─', $boxWidth - 2).'╯';

        return $output;
    }
}
