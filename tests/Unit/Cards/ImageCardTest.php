<?php

namespace Repat\CliCrud\Tests\Unit\Cards;

use Repat\CliCrud\Cards\ImageCard;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class ImageCardTest extends TestCase
{
    protected string $testImage;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a 1x1 red pixel PNG for testing
        $this->testImage = tempnam(sys_get_temp_dir(), 'cli-crud-test-img-').'.png';
        $img = imagecreatetruecolor(1, 1);
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 0, 0));
        imagepng($img, $this->testImage);
        imagedestroy($img);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->testImage)) {
            unlink($this->testImage);
        }

        parent::tearDown();
    }

    public function test_default_protocol_is_kitty(): void
    {
        $card = new ImageCard('Test', fn () => $this->testImage);

        $this->assertStringStartsWith("\e_G", $card->render(new User, $this->createResource()));
    }

    public function test_iterm_sets_protocol(): void
    {
        $card = (new ImageCard('Test', fn () => $this->testImage))->iterm();

        $this->assertStringStartsWith("\e]1337", $card->render(new User, $this->createResource()));
    }

    public function test_kitty_returns_static_for_chaining(): void
    {
        $card = new ImageCard('Test', fn () => $this->testImage);
        $result = $card->kitty();

        $this->assertSame($card, $result);
    }

    public function test_iterm_returns_static_for_chaining(): void
    {
        $card = new ImageCard('Test', fn () => $this->testImage);
        $result = $card->iterm();

        $this->assertSame($card, $result);
    }

    public function test_render_shows_fallback_on_missing_file(): void
    {
        $card = new ImageCard('Test', fn () => '/nonexistent/path.png');
        $output = $card->render(new User, $this->createResource());

        $this->assertStringContainsString('File not found', $output);
    }

    public function test_render_shows_fallback_on_null_path(): void
    {
        $card = new ImageCard('Test', fn () => null);
        $output = $card->render(new User, $this->createResource());

        $this->assertStringContainsString('No path provided', $output);
    }

    protected function createResource(): Resource
    {
        return new class extends Resource
        {
            protected static string $model = User::class;

            protected static string $label = 'Users';

            protected static string $singularLabel = 'User';

            public static function fields(): array
            {
                return [];
            }

            public static function tableColumns(): array
            {
                return ['id'];
            }
        };
    }
}
