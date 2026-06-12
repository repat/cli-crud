<?php

namespace Repat\CliCrud\Tests\Unit\Cards;

use Repat\CliCrud\Cards\CustomCard;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class CustomCardTest extends TestCase
{
    public function test_renders_closure_content(): void
    {
        $card = new CustomCard('Test Card', fn () => 'Custom content here');

        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $resource = new class extends Resource
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

        $output = $card->render($user, $resource);

        $this->assertStringContainsString('Test Card', $output);
        $this->assertStringContainsString('Custom content here', $output);
    }

    public function test_renders_multiline_content(): void
    {
        $card = new CustomCard('Test Card', fn () => "Line 1\nLine 2\nLine 3");

        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $resource = new class extends Resource
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

        $output = $card->render($user, $resource);

        $this->assertStringContainsString('Line 1', $output);
        $this->assertStringContainsString('Line 2', $output);
        $this->assertStringContainsString('Line 3', $output);
    }

    public function test_closure_receives_model(): void
    {
        $receivedModel = null;
        $card = new CustomCard('Test', function ($model) use (&$receivedModel) {
            $receivedModel = $model;

            return 'content';
        });

        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $resource = new class extends Resource
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

        $card->render($user, $resource);

        $this->assertSame($user, $receivedModel);
    }

    public function test_closure_receives_resource(): void
    {
        $receivedResource = null;
        $card = new CustomCard('Test', function ($model, $resource) use (&$receivedResource) {
            $receivedResource = $resource;

            return 'content';
        });

        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $resource = new class extends Resource
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

        $card->render($user, $resource);

        $this->assertSame($resource, $receivedResource);
    }

    public function test_has_box_characters(): void
    {
        $card = new CustomCard('Test', fn () => 'content');

        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $resource = new class extends Resource
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

        $output = $card->render($user, $resource);

        $this->assertStringContainsString('╭', $output);
        $this->assertStringContainsString('╮', $output);
        $this->assertStringContainsString('├', $output);
        $this->assertStringContainsString('┤', $output);
        $this->assertStringContainsString('╰', $output);
        $this->assertStringContainsString('╯', $output);
        $this->assertStringContainsString('│', $output);
        $this->assertStringContainsString('─', $output);
    }
}
