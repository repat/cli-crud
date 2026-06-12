<?php

namespace Repat\CliCrud\Tests\Unit\Fields\Relations;

use Repat\CliCrud\Fields\Relations\HasMany;
use Repat\CliCrud\Tests\Fixtures\Resources\PostResource;
use Repat\CliCrud\Tests\TestCase;

class RelationTest extends TestCase
{
    public function test_relation_with_two_args_derives_name(): void
    {
        $relation = HasMany::make('Posts', PostResource::class);

        $this->assertEquals('posts', $relation->getName());
        $this->assertEquals('Posts', $relation->getLabel());
        $this->assertEquals(PostResource::class, $relation->getResourceClass());
    }

    public function test_relation_with_three_args_uses_explicit_name(): void
    {
        $relation = HasMany::make('Author Posts', 'custom_posts', PostResource::class);

        $this->assertEquals('custom_posts', $relation->getName());
        $this->assertEquals('Author Posts', $relation->getLabel());
        $this->assertEquals(PostResource::class, $relation->getResourceClass());
    }

    public function test_relation_derives_name_from_camel_case(): void
    {
        $relation = HasMany::make('userPosts', PostResource::class);

        $this->assertEquals('user_posts', $relation->getName());
    }

    public function test_relation_derives_name_with_special_chars(): void
    {
        $relation = HasMany::make("User's Posts", PostResource::class);

        $this->assertEquals('users_posts', $relation->getName());
    }

    public function test_relation_derives_name_from_pascal_case(): void
    {
        $relation = HasMany::make('BlogPosts', PostResource::class);

        $this->assertEquals('blog_posts', $relation->getName());
    }
}
