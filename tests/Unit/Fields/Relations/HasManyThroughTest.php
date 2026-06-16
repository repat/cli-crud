<?php

namespace Repat\CliCrud\Tests\Unit\Fields\Relations;

use Repat\CliCrud\Fields\Relations\HasManyThrough;
use Repat\CliCrud\Tests\Fixtures\Resources\PostResource;
use Repat\CliCrud\Tests\TestCase;

class HasManyThroughTest extends TestCase
{
    public function test_get_relation_type_returns_has_many_through(): void
    {
        $relation = HasManyThrough::make('Posts', PostResource::class);

        $this->assertSame('hasManyThrough', $relation->getRelationType());
    }

    public function test_two_arg_make_derives_name_from_label(): void
    {
        $relation = HasManyThrough::make('Published Posts', PostResource::class);

        $this->assertSame('published_posts', $relation->getName());
        $this->assertSame('Published Posts', $relation->getLabel());
        $this->assertSame(PostResource::class, $relation->getResourceClass());
    }

    public function test_three_arg_make_uses_explicit_name(): void
    {
        $relation = HasManyThrough::make('Posts', 'published_posts', PostResource::class);

        $this->assertSame('published_posts', $relation->getName());
        $this->assertSame('Posts', $relation->getLabel());
        $this->assertSame(PostResource::class, $relation->getResourceClass());
    }

    public function test_get_resource_returns_configured_resource(): void
    {
        $relation = HasManyThrough::make('Posts', PostResource::class);

        $resource = $relation->getResource();

        $this->assertInstanceOf(PostResource::class, $resource);
    }
}
