<?php

namespace Repat\CliCrud\Tests\Unit\Commands;

use Illuminate\Database\Eloquent\Model;
use Repat\CliCrud\Actions\Action;
use Repat\CliCrud\Actions\ActionDispatcher;
use Repat\CliCrud\Authorization\Authorizer;
use Repat\CliCrud\Commands\CrudCommand;
use Repat\CliCrud\Forms\FormBuilder;
use Repat\CliCrud\Resources\ResourceRegistrar;
use Repat\CliCrud\Tables\TableRenderer;
use Repat\CliCrud\Tests\Fixtures\FormType;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;
use Repat\CliCrud\Tests\Unit\Actions\DestructiveActionFixture;
use Repat\CliCrud\Tests\Unit\Actions\FooBarAction;
use Repat\CliCrud\Views\DetailViewRenderer;

class CrudCommandTest extends TestCase
{
    protected CrudCommand $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new CrudCommand(
            app(ResourceRegistrar::class),
            app(Authorizer::class),
            app(TableRenderer::class),
            app(FormBuilder::class),
            app(DetailViewRenderer::class),
            app(ActionDispatcher::class)
        );
    }

    public function test_center_pad_with_odd_header_width(): void
    {
        $result = $this->invokeCenterPad('✓', 6);

        $this->assertEquals('  ✓   ', $result);
        $this->assertEquals(6, mb_strlen($result));
    }

    public function test_center_pad_with_even_header_width(): void
    {
        $result = $this->invokeCenterPad('✓', 8);

        $this->assertEquals('   ✓    ', $result);
        $this->assertEquals(8, mb_strlen($result));
    }

    public function test_center_pad_with_value_longer_than_width(): void
    {
        $result = $this->invokeCenterPad('✓', 1);

        $this->assertEquals('✓', $result);
    }

    public function test_center_pad_with_value_equal_to_width(): void
    {
        $result = $this->invokeCenterPad('✓', 1);

        $this->assertEquals('✓', $result);
    }

    public function test_center_pad_with_ansi_codes(): void
    {
        $valueWithAnsi = "\e[32m✓\e[39m";
        $result = $this->invokeCenterPad($valueWithAnsi, 6);

        $this->assertEquals("  \e[32m✓\e[39m   ", $result);
        $this->assertEquals(6, $this->invokeGetVisibleLength($result));
    }

    public function test_center_pad_with_empty_string(): void
    {
        $result = $this->invokeCenterPad('', 4);

        $this->assertEquals('    ', $result);
        $this->assertEquals(4, mb_strlen($result));
    }

    public function test_get_visible_length_strips_ansi_codes(): void
    {
        $result = $this->invokeGetVisibleLength("\e[32m✓\e[39m");

        $this->assertEquals(1, $result);
    }

    public function test_get_visible_length_with_plain_text(): void
    {
        $result = $this->invokeGetVisibleLength('Hello');

        $this->assertEquals(5, $result);
    }

    public function test_get_visible_length_with_multiple_ansi_codes(): void
    {
        $result = $this->invokeGetVisibleLength("\e[32mHello\e[39m \e[31mWorld\e[39m");

        $this->assertEquals(11, $result);
    }

    public function test_get_visible_length_with_empty_string(): void
    {
        $result = $this->invokeGetVisibleLength('');

        $this->assertEquals(0, $result);
    }

    public function test_format_table_value_with_enum_returns_name(): void
    {
        $result = $this->invokeFormatTableValue(FormType::Draft);

        $this->assertEquals('Draft', $result);
    }

    public function test_format_table_value_for_datatable_with_enum_returns_name(): void
    {
        $result = $this->invokeFormatTableValueForDatatable(FormType::Published);

        $this->assertEquals('Published', $result);
    }

    public function test_render_list_header_without_search(): void
    {
        $this->assertEquals('Users', $this->invokeRenderListHeader('Users', null));
    }

    public function test_render_list_header_with_search(): void
    {
        $this->assertEquals(
            'Users (search: "joe")',
            $this->invokeRenderListHeader('Users', 'joe')
        );
    }

    public function test_format_action_label_adds_destructive_prefix(): void
    {
        $action = (new DestructiveActionFixture)
            ->name('Destroy');

        $this->assertEquals(
            '[DESTRUCTIVE] Destroy',
            $this->invokeFormatActionLabel($action)
        );
    }

    public function test_format_action_label_plain_for_non_destructive(): void
    {
        $action = new FooBarAction;

        $this->assertEquals('Foo Bar', $this->invokeFormatActionLabel($action));
    }

    public function test_format_action_confirm_label_uses_confirm_text_when_set(): void
    {
        $action = (new FooBarAction)
            ->confirmText('Are you sure?');
        $resource = new class extends \Repat\CliCrud\Resources\Resource
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
        $user = new User;

        $this->assertEquals(
            'Are you sure?',
            $this->invokeFormatActionConfirmLabel($action, $resource, $user)
        );
    }

    public function test_format_action_confirm_label_includes_destructive_prefix(): void
    {
        $action = (new DestructiveActionFixture)
            ->name('Destroy');
        $resource = new class extends \Repat\CliCrud\Resources\Resource
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
        $user = new User;

        $label = $this->invokeFormatActionConfirmLabel($action, $resource, $user);

        $this->assertStringStartsWith('[DESTRUCTIVE] ', $label);
        $this->assertStringContainsString('Destroy', $label);
    }

    public function test_resolve_action_by_hash_finds_action(): void
    {
        $a = new FooBarAction;
        $b = new DestructiveActionFixture;
        $hashA = spl_object_hash($a);

        $this->assertSame($a, $this->invokeResolveActionByHash([$a, $b], $hashA));
    }

    public function test_resolve_action_by_hash_returns_null_for_unknown_hash(): void
    {
        $a = new FooBarAction;

        $this->assertNull($this->invokeResolveActionByHash([$a], 'unknown-hash'));
    }

    protected function invokeCenterPad(string $value, int $width): string
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('centerPad');
        $method->setAccessible(true);

        return $method->invoke($this->command, $value, $width);
    }

    protected function invokeGetVisibleLength(string $value): int
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('getVisibleLength');
        $method->setAccessible(true);

        return $method->invoke($this->command, $value);
    }

    protected function invokeFormatTableValue(mixed $value): string
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('formatTableValue');
        $method->setAccessible(true);

        return $method->invoke($this->command, $value);
    }

    protected function invokeFormatTableValueForDatatable(mixed $value): string
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('formatTableValueForDatatable');
        $method->setAccessible(true);

        return $method->invoke($this->command, $value);
    }

    protected function invokeRenderListHeader(string $label, ?string $search): string
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('renderListHeader');
        $method->setAccessible(true);

        return $method->invoke($this->command, $label, $search);
    }

    protected function invokeFormatActionLabel(Action $action): string
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('formatActionLabel');
        $method->setAccessible(true);

        return $method->invoke($this->command, $action);
    }

    protected function invokeFormatActionConfirmLabel(Action $action, \Repat\CliCrud\Resources\Resource $resource, Model $item): string
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('formatActionConfirmLabel');
        $method->setAccessible(true);

        return $method->invoke($this->command, $action, $resource, $item);
    }

    protected function invokeResolveActionByHash(array $actions, string $hash): ?Action
    {
        $reflection = new \ReflectionClass($this->command);
        $method = $reflection->getMethod('resolveActionByHash');
        $method->setAccessible(true);

        return $method->invoke($this->command, $actions, $hash);
    }
}
