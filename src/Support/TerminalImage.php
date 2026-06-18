<?php

namespace Repat\CliCrud\Support;

class TerminalImage
{
    public static function kitty(string $path, int $width): string
    {
        if (! file_exists($path)) {
            return '';
        }

        $size = filesize($path);
        $mime = mime_content_type($path);
        $format = match ($mime) {
            'image/png' => 100,
            'image/jpeg', 'image/jpg' => 101,
            'image/gif' => 102,
            'image/webp' => 100,
            'image/svg+xml' => 100,
            default => 100,
        };

        return "\e_Ga=T,f={$format},t=f,s={$size},v={$width};{$path}\e\\";
    }

    public static function iterm(string $path, int $width): string
    {
        if (! file_exists($path)) {
            return '';
        }

        $data = file_get_contents($path);
        $size = strlen($data);
        $base64 = base64_encode($data);

        return "\e]1337;File=inline=1;size={$size};width={$width}px:{$base64}\a";
    }
}
