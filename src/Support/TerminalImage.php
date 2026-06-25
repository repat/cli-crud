<?php

namespace Repat\CliCrud\Support;

class TerminalImage
{
    public static function detectProtocol(): string
    {
        if (getenv('TERM_PROGRAM') === 'iTerm.app' || getenv('ITERM_SESSION_ID') !== false) {
            return 'iterm';
        }

        if (getenv('KITTY_WINDOW_ID') !== false) {
            return 'kitty';
        }

        if (getenv('TERM_PROGRAM') === 'WezTerm') {
            return 'kitty';
        }

        return 'kitty';
    }

    public static function kitty(string $path, int $displayCols): string
    {
        if (! file_exists($path)) {
            return '';
        }

        $path = self::ensurePng($path);

        if ($path === null) {
            return '';
        }

        $data = file_get_contents($path);
        $encodedData = base64_encode($data);

        return "\e_Ga=T,f=100,t=d,c={$displayCols};{$encodedData}\e\\";
    }

    public static function iterm(string $path, int $displayWidth): string
    {
        if (! file_exists($path)) {
            return '';
        }

        $path = self::ensurePng($path);

        if ($path === null) {
            return '';
        }

        $data = file_get_contents($path);
        $size = strlen($data);
        $base64 = base64_encode($data);
        $name = base64_encode(basename($path));

        return "\e]1337;File=name={$name};size={$size};width={$displayWidth}px;inline=1:{$base64}\x07";
    }

    private static function ensurePng(string $path): ?string
    {
        $mime = mime_content_type($path);

        if ($mime === 'image/png') {
            return $path;
        }

        if (! extension_loaded('gd')) {
            return null;
        }

        $image = null;

        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
            $image = @imagecreatefromjpeg($path);
        } elseif ($mime === 'image/gif' && function_exists('imagecreatefromgif')) {
            $image = @imagecreatefromgif($path);
        } elseif ($mime === 'image/webp' && function_exists('imagecreatefromwebp')) {
            $image = @imagecreatefromwebp($path);
        }

        if (! $image) {
            return null;
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'cli-crud-kitty-').'.png';
        imagepng($image, $tempPath);
        imagedestroy($image);

        return $tempPath;
    }
}
