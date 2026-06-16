<?php

namespace Repat\CliCrud\Tests\Unit\Resources;

use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class ResourceTest extends TestCase
{
    public function test_get_title_returns_explicit_value(): void
    {
        $resource = new class extends Resource
        {
            protected static string $model = User::class;

            protected static string $label = 'Users';

            protected static string $singularLabel = 'User';

            protected static ?string $title = 'name';

            public static function fields(): array
            {
                return [];
            }

            public static function tableColumns(): array
            {
                return ['id', 'name'];
            }
        };

        $this->assertSame('name', $resource::getTitle());
    }

    public function test_get_title_throws_when_not_set(): void
    {
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
                return ['id', 'name'];
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('must define a $title property');

        $resource::getTitle();
    }

    public function test_get_title_throws_when_column_not_found(): void
    {
        $resource = new class extends Resource
        {
            protected static string $model = User::class;

            protected static string $label = 'Users';

            protected static string $singularLabel = 'User';

            protected static ?string $title = 'nonexistent_column';

            public static function fields(): array
            {
                return [];
            }

            public static function tableColumns(): array
            {
                return ['id', 'name'];
            }
        };

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('does not exist in table');

        $resource::getTitle();
    }
}
