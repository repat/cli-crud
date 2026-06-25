<?php

namespace Repat\CliCrud\Cards;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Support\TerminalImage;

class ImageCard extends Card
{
    protected string $protocol = 'auto';

    public function __construct(
        string $title,
        protected Closure $pathResolver
    ) {
        parent::__construct($title);
    }

    public function kitty(): static
    {
        $this->protocol = 'kitty';

        return $this;
    }

    public function iterm(): static
    {
        $this->protocol = 'iterm';

        return $this;
    }

    public function render(Model $model, Resource $resource): string
    {
        $path = ($this->pathResolver)($model, $resource);

        if ($path === null || $path === '') {
            return $this->renderFallback('No path provided');
        }

        $path = $this->resolvePath((string) $path);

        if (! file_exists($path)) {
            return $this->renderFallback("File not found: {$path}");
        }

        $dimensions = @getimagesize($path);

        if ($dimensions === false) {
            return $this->renderFallback("Unsupported image: {$path}");
        }

        $width = $dimensions[0];

        // Scale to fit a reasonable terminal width
        $displayWidth = min($width, 320);

        // Estimate display columns from pixel width
        $displayCols = (int) max(1, ceil($displayWidth / 8));

        $protocol = $this->protocol === 'auto' ? TerminalImage::detectProtocol() : $this->protocol;

        $sequence = match ($protocol) {
            'iterm' => TerminalImage::iterm($path, $displayWidth),
            default => TerminalImage::kitty($path, $displayCols),
        };

        $titleLen = mb_strlen($this->title);
        $boxWidth = max($displayCols + 4, $titleLen + 4);
        $boxWidth = min($boxWidth, 120);

        $output = '';
        $output .= '╭'.str_repeat('─', $boxWidth - 2).'╮'."\n";
        $output .= '│ '.str_pad($this->title, $boxWidth - 4).' │'."\n";
        $output .= '├'.str_repeat('─', $boxWidth - 2).'┤'."\n";
        $output .= '│ '.$sequence."\n";
        $output .= '╰'.str_repeat('─', $boxWidth - 2).'╯';

        return $output;
    }

    protected function resolvePath(string $path): string
    {
        if (preg_match('#^https?://#', $path)) {
            $temp = tempnam(sys_get_temp_dir(), 'cli-crud-img-');
            $content = @file_get_contents($path);

            if ($content !== false) {
                file_put_contents($temp, $content);

                return $temp;
            }

            return $path;
        }

        return $path;
    }

    protected function renderFallback(string $message): string
    {
        $titleLen = mb_strlen($this->title);
        $msgLen = mb_strlen($message);
        $boxWidth = max($titleLen + 4, $msgLen + 4);
        $boxWidth = min($boxWidth, 120);

        $output = '';
        $output .= '╭'.str_repeat('─', $boxWidth - 2).'╮'."\n";
        $output .= '│ '.str_pad($this->title, $boxWidth - 4).' │'."\n";
        $output .= '├'.str_repeat('─', $boxWidth - 2).'┤'."\n";
        $output .= '│ '.str_pad($message, $boxWidth - 4).' │'."\n";
        $output .= '╰'.str_repeat('─', $boxWidth - 2).'╯';

        return $output;
    }
}
