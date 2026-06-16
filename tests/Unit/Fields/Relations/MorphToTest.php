<?php

namespace Repat\CliCrud\Tests\Unit\Fields\Relations;

use Repat\CliCrud\Fields\Relations\MorphTo;
use Repat\CliCrud\Tests\Fixtures\Post;
use Repat\CliCrud\Tests\Fixtures\Resources\PostResource;
use Repat\CliCrud\Tests\Fixtures\Resources\UserResource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class MorphToTest extends TestCase
{
    public function test_get_resources_returns_instantiated_resources(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);

        $resources = $field->getResources();

        $this->assertCount(1, $resources);
        $this->assertInstanceOf(PostResource::class, $resources[0]);
    }

    public function test_get_type_column_returns_name_underscore_type(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);
        $post = new Post;

        $this->assertEquals('commentable_type', $field->getTypeColumn($post));
    }

    public function test_get_id_column_returns_name_underscore_id(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);
        $post = new Post;

        $this->assertEquals('commentable_id', $field->getIdColumn($post));
    }

    public function test_get_related_models_returns_model_classes(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);

        $this->assertEquals([Post::class], $field->getRelatedModels());
    }

    public function test_get_morph_class_strings_returns_fqcn_when_no_morphmap(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);

        $this->assertEquals([Post::class], $field->getMorphClassStrings());
    }

    public function test_display_field_is_chainable_and_gettable(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);
        $result = $field->displayField('email');

        $this->assertSame($field, $result);
        $this->assertEquals('email', $field->getDisplayField());
    }

    public function test_display_field_defaults_to_null(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);

        $this->assertNull($field->getDisplayField());
    }

    public function test_required_is_chainable_and_gettable(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);

        $this->assertFalse($field->isRequired());

        $result = $field->required();

        $this->assertSame($field, $result);
        $this->assertTrue($field->isRequired());
    }

    public function test_get_relation_type_returns_morph_to(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);

        $this->assertEquals('morphTo', $field->getRelationType());
    }

    public function test_get_name_derives_from_label_when_name_arg_is_null(): void
    {
        $field = MorphTo::make('Commentable', null, [PostResource::class]);

        $this->assertEquals('commentable', $field->getName());
    }

    public function test_get_name_uses_explicit_name(): void
    {
        $field = MorphTo::make('Commentable', 'parent', [PostResource::class]);

        $this->assertEquals('parent', $field->getName());
    }

    public function test_get_name_derives_from_camel_case_label(): void
    {
        $field = MorphTo::make('OwnableAsset', null, [PostResource::class]);

        $this->assertEquals('ownable_asset', $field->getName());
    }

    public function test_resources_setter_replaces_array(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);
        $field->resources([UserResource::class]);

        $this->assertEquals([User::class], $field->getRelatedModels());
        $this->assertCount(1, $field->getResources());
        $this->assertInstanceOf(UserResource::class, $field->getResources()[0]);
    }

    public function test_multiple_resources(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class, UserResource::class]);

        $this->assertEquals([Post::class, User::class], $field->getRelatedModels());
        $this->assertEquals([Post::class, User::class], $field->getMorphClassStrings());
    }

    public function test_get_label_returns_label(): void
    {
        $field = MorphTo::make('Commentable', 'commentable', [PostResource::class]);

        $this->assertEquals('Commentable', $field->getLabel());
    }
}
