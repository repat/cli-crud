<?php

namespace Repat\CliCrud\Tests\Unit\Validation;

use Repat\CliCrud\Exceptions\FieldMismatchException;
use Repat\CliCrud\Fields\Boolean;
use Repat\CliCrud\Fields\Json;
use Repat\CliCrud\Fields\Number;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Tests\Fixtures\Post;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;
use Repat\CliCrud\Validation\FieldValidator;

class FieldValidatorTest extends TestCase
{
    protected FieldValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new FieldValidator;
    }

    public function test_validates_correct_field_types(): void
    {
        $resource = new class extends \Repat\CliCrud\Resources\Resource
        {
            protected static string $model = User::class;

            protected static string $label = 'Users';

            protected static string $singularLabel = 'User';

            public static function fields(): array
            {
                return [
                    Text::make('Name', 'name'),
                    Text::make('Email', 'email'),
                    Boolean::make('Is Active', 'is_active'),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'name'];
            }
        };

        $this->validator->validate($resource);

        $this->assertTrue(true);
    }

    public function test_throws_exception_for_wrong_field_type(): void
    {
        $resource = new class extends \Repat\CliCrud\Resources\Resource
        {
            protected static string $model = User::class;

            protected static string $label = 'Users';

            protected static string $singularLabel = 'User';

            public static function fields(): array
            {
                return [
                    Number::make('Name', 'name'),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'name'];
            }
        };

        $this->expectException(FieldMismatchException::class);
        $this->expectExceptionMessage("Field 'name' defined as Number in resource, but database column is varchar");

        $this->validator->validate($resource);
    }

    public function test_throws_exception_for_nonexistent_column(): void
    {
        $resource = new class extends \Repat\CliCrud\Resources\Resource
        {
            protected static string $model = User::class;

            protected static string $label = 'Users';

            protected static string $singularLabel = 'User';

            public static function fields(): array
            {
                return [
                    Text::make('Nonexistent Field', 'nonexistent_field'),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'name'];
            }
        };

        $this->expectException(FieldMismatchException::class);
        $this->expectExceptionMessage("Field 'nonexistent_field' defined in resource, but column does not exist");

        $this->validator->validate($resource);
    }

    public function test_json_field_is_valid_for_json_column(): void
    {
        $resource = new class extends \Repat\CliCrud\Resources\Resource
        {
            protected static string $model = Post::class;

            protected static string $label = 'Posts';

            protected static string $singularLabel = 'Post';

            public static function fields(): array
            {
                return [
                    Text::make('Title', 'title'),
                    Json::make('Metadata', 'metadata'),
                ];
            }

            public static function tableColumns(): array
            {
                return ['id', 'title'];
            }
        };

        $this->validator->validate($resource);

        $this->assertTrue(true);
    }
}
