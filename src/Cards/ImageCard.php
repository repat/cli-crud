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

        // Scale to fit a reasonable terminal width (~80ch ≈ 320px at 4px/ch)
        $displayWidth = min($width, 320);

        // Estimate display columns from pixel width (~8px per character cell)
        $displayCols = (int) max(1, ceil($displayWidth / 8));

        $protocol = $this->protocol === 'auto' ? TerminalImage::detectProtocol() : $this->protocol;

        $sequence = match ($protocol) {
            'iterm' => TerminalImage::iterm($path, $displayWidth),
            default => TerminalImage::kitty($path, $displayCols),
        };

        return $sequence;
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
        return $this->title."\n".str_repeat('─', mb_strlen($this->title))."\n".$message."\n";
    }
}
