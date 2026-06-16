<?php

namespace Repat\CliCrud\Tests\Unit\Cards;

use Repat\CliCrud\Cards\ChartCard;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class ChartCardTest extends TestCase
{
    public function test_default_chart_type_is_bar(): void
    {
        $card = new ChartCard('Test', fn () => ['A' => 1]);

        $this->assertEquals('bar', $card->getChartType());
    }

    public function test_horizontal_bar_sets_chart_type(): void
    {
        $card = (new ChartCard('Test', fn () => ['A' => 1]))->horizontalBar();

        $this->assertEquals('horizontalBar', $card->getChartType());
    }

    public function test_bar_sets_chart_type(): void
    {
        $card = (new ChartCard('Test', fn () => ['A' => 1]))->bar();

        $this->assertEquals('bar', $card->getChartType());
    }

    public function test_renders_bar_chart(): void
    {
        $card = new ChartCard('Test Chart', fn () => [
            'Active' => 100,
            'Inactive' => 50,
        ]);

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

        $this->assertStringContainsString('Test Chart', $output);
        $this->assertStringContainsString('Active', $output);
        $this->assertStringContainsString('Inactive', $output);
        $this->assertStringContainsString('█', $output);
    }

    public function test_renders_horizontal_bar_chart(): void
    {
        $card = (new ChartCard('Test Chart', fn () => [
            'Active' => 100,
            'Inactive' => 50,
        ]))->horizontalBar();

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

        $this->assertStringContainsString('Test Chart', $output);
        $this->assertStringContainsString('Active', $output);
        $this->assertStringContainsString('├', $output);
        $this->assertStringContainsString('│', $output);
    }

    public function test_closure_receives_model(): void
    {
        $receivedModel = null;
        $card = new ChartCard('Test', function ($model) use (&$receivedModel) {
            $receivedModel = $model;

            return ['A' => 1];
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

    public function test_has_box_characters(): void
    {
        $card = new ChartCard('Test', fn () => ['A' => 1]);

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
    }

    public function test_default_show_percentages_is_false(): void
    {
        $card = new ChartCard('Test', fn () => ['A' => 1]);

        $this->assertFalse($card->shouldShowPercentages());
    }

    public function test_percentage_sets_flag_to_true(): void
    {
        $card = new ChartCard('Test', fn () => ['A' => 1]);

        $this->assertSame($card, $card->percentage());
        $this->assertTrue($card->shouldShowPercentages());
    }

    public function test_bar_with_percentages_renders_percentage_row(): void
    {
        $card = (new ChartCard('Orders', fn () => [
            'Jan' => 50,
            'Feb' => 50,
        ]))->bar()->percentage();

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

        $this->assertStringContainsString('Orders', $output);
        $this->assertStringContainsString('50.0%', $output);
    }

    public function test_horizontal_bar_with_percentages_replaces_value_with_percent(): void
    {
        $card = (new ChartCard('Orders', fn () => [
            'Jan' => 50,
            'Feb' => 50,
        ]))->horizontalBar()->percentage();

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

        $this->assertStringContainsString('50.0%', $output);
    }

    public function test_rendered_box_width_matches_visible_chart_width(): void
    {
        // Regression test: Card::renderBox() must ignore ANSI escape codes
        // when computing the box width. Otherwise colored chart bars would
        // inflate the width and leave trailing padding inside the box.
        $card = new ChartCard('Orders per Month', fn () => [
            'Jan' => 42, 'Feb' => 38, 'Mar' => 55, 'Apr' => 61, 'May' => 48,
        ]);

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
        $lines = explode("\n", $output);

        // Strip ANSI from every line and confirm they all have the same
        // visible length — the box width. This is the property that was
        // broken before the fix.
        $visibleLengths = array_map(
            fn (string $line) => mb_strlen(preg_replace('/\e\[[0-9;]*m/', '', $line)),
            $lines
        );

        $this->assertCount(1, array_unique($visibleLengths), 'All box lines must have the same visible width');
    }

    public function test_rendered_box_right_border_appears_soon_after_chart_content(): void
    {
        // The right `│` border should appear close to the chart content,
        // not far to the right. Specifically: the middle content lines
        // (those starting with `│ `) should end with ` │` at exactly the
        // same column, and the box width should be just enough to fit
        // the visible content.
        $card = new ChartCard('Orders per Month', fn () => [
            'Jan' => 42, 'Feb' => 38, 'Mar' => 55, 'Apr' => 61, 'May' => 48,
        ]);

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
        $lines = explode("\n", $output);

        $boxWidth = mb_strlen(preg_replace('/\e\[[0-9;]*m/', '', $lines[0]));

        foreach ($lines as $line) {
            $plain = preg_replace('/\e\[[0-9;]*m/', '', $line);

            // Skip the top, bottom, and divider borders — they use
            // corner/divider characters (╭/╰/├) instead of `│`.
            if (str_starts_with($plain, '╭') || str_starts_with($plain, '╰') || str_starts_with($plain, '├')) {
                continue;
            }

            $this->assertSame('│ ', mb_substr($plain, 0, 2), 'Each middle line should start with the left border');
            $this->assertSame(' │', mb_substr($plain, -2), 'Each middle line should end with the right border');
            $this->assertSame($boxWidth, mb_strlen($plain), 'Every middle line should have the same visible width as the box');
        }
    }

    public function test_scatter_sets_chart_type(): void
    {
        $card = (new ChartCard('Test', fn () => ['A' => [1, 2]]))->scatter();

        $this->assertSame('scatter', $card->getChartType());
    }

    public function test_renders_scatter_chart(): void
    {
        $card = (new ChartCard('Test Scatter', fn () => [
            'A' => [10, 50],
            'B' => [20, 80],
        ]))->scatter();

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

        $this->assertStringContainsString('Test Scatter', $output);
        $this->assertStringContainsString('●', $output);
    }
}
