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

    public function test_pie_sets_chart_type(): void
    {
        $card = (new ChartCard('Test', fn () => ['A' => 1]))->pie();

        $this->assertEquals('pie', $card->getChartType());
    }

    public function test_horizontal_bar_sets_chart_type(): void
    {
        $card = (new ChartCard('Test', fn () => ['A' => 1]))->horizontalBar();

        $this->assertEquals('horizontalBar', $card->getChartType());
    }

    public function test_bar_sets_chart_type(): void
    {
        $card = (new ChartCard('Test', fn () => ['A' => 1]))->pie()->bar();

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

    public function test_renders_pie_chart(): void
    {
        $card = (new ChartCard('Test Chart', fn () => [
            'Active' => 75,
            'Inactive' => 25,
        ]))->pie();

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
        $this->assertStringContainsString('75.0%', $output);
        $this->assertStringContainsString('25.0%', $output);
        $this->assertStringContainsString('●', $output);
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

    public function test_pie_with_percentages_does_not_change_pie_output(): void
    {
        $cardWithout = (new ChartCard('Orders', fn () => [
            'Jan' => 75,
            'Feb' => 25,
        ]))->pie();

        $cardWith = (new ChartCard('Orders', fn () => [
            'Jan' => 75,
            'Feb' => 25,
        ]))->pie()->percentage();

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

        $this->assertSame(
            $cardWithout->render($user, $resource),
            $cardWith->render($user, $resource),
        );
    }
}
