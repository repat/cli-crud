<?php

namespace Repat\CliCrud\Tests\Unit\Cards;

use Repat\CliCrud\Cards\MetricCard;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class MetricCardTest extends TestCase
{
    public function test_renders_title_and_value(): void
    {
        $card = new MetricCard('Total Users', fn () => 42);
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

        $this->assertStringContainsString('Total Users', $output);
        $this->assertStringContainsString('42', $output);
        $this->assertStringContainsString('╭', $output);
        $this->assertStringContainsString('╰', $output);
    }

    public function test_closure_receives_model(): void
    {
        $receivedModel = null;
        $card = new MetricCard('Test', function ($model) use (&$receivedModel) {
            $receivedModel = $model;

            return 'value';
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
        $card = new MetricCard('Test', function ($model, $resource) use (&$receivedResource) {
            $receivedResource = $resource;

            return 'value';
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
        $card = new MetricCard('Test', fn () => 42);
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
