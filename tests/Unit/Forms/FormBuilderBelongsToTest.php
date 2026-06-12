<?php

namespace Repat\CliCrud\Tests\Unit\Forms;

use Illuminate\Database\Eloquent\Model;
use Repat\CliCrud\Fields\Relations\BelongsTo;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Forms\FormBuilder;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\Post;
use Repat\CliCrud\Tests\Fixtures\Resources\UserResource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class FormBuilderBelongsToTest extends TestCase
{
    public function test_build_uses_foreign_key_for_belongs_to(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);

        $fields = [
            Text::make('Title', 'title'),
            BelongsTo::make('Author', 'user', UserResource::class),
        ];

        // Create a mock resource
        $resource = new class extends Resource
        {
            protected static string $model = Post::class;

            protected static string $label = 'Posts';

            protected static string $singularLabel = 'Post';

            public static function fields(): array
            {
                return [];
            }

            public static function tableColumns(): array
            {
                return ['id', 'title'];
            }
        };

        // Mock the form builder to avoid actual prompts
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->onlyMethods(['promptForField', 'promptForBelongsTo'])
            ->getMock();

        $formBuilder->method('promptForField')
            ->willReturn('Updated Title');

        $formBuilder->method('promptForBelongsTo')
            ->willReturn($user->id);

        $data = $formBuilder->build($fields, $post, $resource);

        // Should use foreign key name, not relationship name
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayNotHasKey('user', $data);
        $this->assertEquals($user->id, $data['user_id']);
        $this->assertEquals('Updated Title', $data['title']);
    }

    public function test_build_loads_current_belongs_to_value_from_foreign_key(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);

        $fields = [
            BelongsTo::make('Author', 'user', UserResource::class),
        ];

        // Create a mock resource
        $resource = new class extends Resource
        {
            protected static string $model = Post::class;

            protected static string $label = 'Posts';

            protected static string $singularLabel = 'Post';

            public static function fields(): array
            {
                return [];
            }

            public static function tableColumns(): array
            {
                return ['id', 'title'];
            }
        };

        // Mock the form builder
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->onlyMethods(['promptForBelongsTo'])
            ->getMock();

        // Verify that promptForBelongsTo receives the foreign key value (user_id)
        $formBuilder->expects($this->once())
            ->method('promptForBelongsTo')
            ->with(
                $this->isInstanceOf(BelongsTo::class),
                $this->equalTo($user->id), // Should receive the foreign key value
                $this->anything()
            )
            ->willReturn($user->id);

        $data = $formBuilder->build($fields, $post, $resource);

        $this->assertEquals($user->id, $data['user_id']);
    }

    public function test_build_validation_rules_use_foreign_key(): void
    {
        $post = new Post;
        $fields = [
            BelongsTo::make('Author', 'user', UserResource::class),
        ];

        $formBuilder = new FormBuilder;

        // Use reflection to call protected method
        $reflection = new \ReflectionClass($formBuilder);
        $method = $reflection->getMethod('buildValidationRules');
        $method->setAccessible(true);

        $introspectionModel = $post;
        $rules = $method->invoke($formBuilder, $fields, $introspectionModel);

        $this->assertArrayHasKey('user_id', $rules);
        $this->assertArrayNotHasKey('user', $rules);
        $this->assertContains('exists:users,id', $rules['user_id']);
    }

    public function test_build_validation_rules_respects_required(): void
    {
        $post = new Post;

        // Without required()
        $fields1 = [
            BelongsTo::make('Author', 'user', UserResource::class),
        ];

        $formBuilder = new FormBuilder;
        $reflection = new \ReflectionClass($formBuilder);
        $method = $reflection->getMethod('buildValidationRules');
        $method->setAccessible(true);

        $rules1 = $method->invoke($formBuilder, $fields1, $post);
        $this->assertContains('nullable', $rules1['user_id']);
        $this->assertNotContains('required', $rules1['user_id']);

        // With required()
        $fields2 = [
            BelongsTo::make('Author', 'user', UserResource::class)->required(),
        ];

        $rules2 = $method->invoke($formBuilder, $fields2, $post);
        $this->assertContains('required', $rules2['user_id']);
        $this->assertNotContains('nullable', $rules2['user_id']);
    }

    public function test_build_with_create_scenario_no_model(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $fields = [
            Text::make('Title', 'title'),
            BelongsTo::make('Author', 'user', UserResource::class),
        ];

        // Create a mock resource that returns Post model
        $resource = new class extends Resource
        {
            protected static string $model = Post::class;

            protected static string $label = 'Posts';

            protected static string $singularLabel = 'Post';

            public static function fields(): array
            {
                return [];
            }

            public static function tableColumns(): array
            {
                return ['id', 'title'];
            }
        };

        // Mock the form builder
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->onlyMethods(['promptForField', 'promptForBelongsTo'])
            ->getMock();

        $formBuilder->method('promptForField')
            ->willReturn('New Post Title');

        $formBuilder->method('promptForBelongsTo')
            ->willReturn($user->id);

        // Build without a model (create scenario) but with resource
        $data = $formBuilder->build($fields, null, $resource);

        // Should still use foreign key name
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayNotHasKey('user', $data);
        $this->assertEquals($user->id, $data['user_id']);
        $this->assertEquals('New Post Title', $data['title']);
    }

    public function test_build_with_multiple_belongs_to_fields(): void
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Test Content',
        ]);

        $fields = [
            Text::make('Title', 'title'),
            BelongsTo::make('Author', 'user', UserResource::class),
        ];

        // Create a mock resource
        $resource = new class extends Resource
        {
            protected static string $model = Post::class;

            protected static string $label = 'Posts';

            protected static string $singularLabel = 'Post';

            public static function fields(): array
            {
                return [];
            }

            public static function tableColumns(): array
            {
                return ['id', 'title'];
            }
        };

        // Mock the form builder
        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->onlyMethods(['promptForField', 'promptForBelongsTo'])
            ->getMock();

        $formBuilder->method('promptForField')
            ->willReturn('Updated Title');

        $formBuilder->method('promptForBelongsTo')
            ->willReturn($user->id);

        $data = $formBuilder->build($fields, $post, $resource);

        // All foreign keys should be present
        $this->assertArrayHasKey('user_id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertEquals($user->id, $data['user_id']);
    }
}
