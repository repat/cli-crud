<?php

namespace Repat\CliCrud\Tests\Unit\Forms;

use Repat\CliCrud\Fields\Relations\MorphTo;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Forms\FormBuilder;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\Post;
use Repat\CliCrud\Tests\Fixtures\Resources\PostResource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class FormBuilderMorphToTest extends TestCase
{
    public function test_build_writes_both_type_and_id_columns_for_morph_to(): void
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Content',
        ]);

        $fields = [
            Text::make('Title', 'title'),
            MorphTo::make('Commentable', 'commentable', [PostResource::class]),
        ];

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

        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->onlyMethods(['promptForField', 'promptForMorphTo'])
            ->getMock();

        $formBuilder->method('promptForField')
            ->willReturn('Updated Title');

        $formBuilder->method('promptForMorphTo')
            ->willReturn(['type' => Post::class, 'id' => $post->id]);

        $data = $formBuilder->build($fields, $post, $resource);

        $this->assertArrayHasKey('commentable_type', $data);
        $this->assertArrayHasKey('commentable_id', $data);
        $this->assertEquals(Post::class, $data['commentable_type']);
        $this->assertEquals($post->id, $data['commentable_id']);
    }

    public function test_build_passes_current_type_and_id_to_prompt_for_morph_to(): void
    {
        $user = User::create([
            'name' => 'John',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'Test Post',
            'content' => 'Content',
        ]);

        $fields = [
            MorphTo::make('Commentable', 'commentable', [PostResource::class]),
        ];

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

        $formBuilder = $this->getMockBuilder(FormBuilder::class)
            ->onlyMethods(['promptForMorphTo'])
            ->getMock();

        $formBuilder->expects($this->once())
            ->method('promptForMorphTo')
            ->with(
                $this->isInstanceOf(MorphTo::class),
                null,
                null,
                null,
                null,
            )
            ->willReturn(['type' => null, 'id' => null]);

        $formBuilder->build($fields, $post, $resource);
    }

    public function test_build_produces_in_and_required_rules_for_morph_to_columns(): void
    {
        $fields = [
            MorphTo::make('Commentable', 'commentable', [PostResource::class])->required(),
        ];

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

        $formBuilder = new FormBuilder;

        $reflection = new \ReflectionClass($formBuilder);
        $method = $reflection->getMethod('buildValidationRules');
        $method->setAccessible(true);

        $rules = $method->invoke($formBuilder, $fields, new Post, null);

        $this->assertArrayHasKey('commentable_type', $rules);
        $this->assertArrayHasKey('commentable_id', $rules);

        $this->assertContains('required', $rules['commentable_type']);
        $this->assertContains('string', $rules['commentable_type']);
        $this->assertContains('in:'.Post::class, $rules['commentable_type']);

        $this->assertContains('required', $rules['commentable_id']);
    }

    public function test_build_produces_nullable_rules_for_non_required_morph_to(): void
    {
        $fields = [
            MorphTo::make('Commentable', 'commentable', [PostResource::class]),
        ];

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

        $formBuilder = new FormBuilder;

        $reflection = new \ReflectionClass($formBuilder);
        $method = $reflection->getMethod('buildValidationRules');
        $method->setAccessible(true);

        $rules = $method->invoke($formBuilder, $fields, new Post, null);

        $this->assertContains('nullable', $rules['commentable_type']);
        $this->assertContains('nullable', $rules['commentable_id']);
    }
}
