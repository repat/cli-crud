<?php

namespace Repat\CliCrud\Tests\Unit\Fields\Relations;

use Repat\CliCrud\Fields\Relations\MorphMany;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\Resources\CommentResource;
use Repat\CliCrud\Tests\TestCase;

class MorphManyTest extends TestCase
{
    public function test_get_relation_type_returns_morph_many(): void
    {
        $relation = MorphMany::make('Comments', CommentResource::class);

        $this->assertEquals('morphMany', $relation->getRelationType());
    }

    public function test_two_arg_make_derives_name_from_label(): void
    {
        $relation = MorphMany::make('Comments', CommentResource::class);

        $this->assertEquals('comments', $relation->getName());
        $this->assertEquals('Comments', $relation->getLabel());
        $this->assertEquals(CommentResource::class, $relation->getResourceClass());
    }

    public function test_three_arg_make_uses_explicit_name(): void
    {
        $relation = MorphMany::make('Author Comments', 'custom_comments', CommentResource::class);

        $this->assertEquals('custom_comments', $relation->getName());
        $this->assertEquals('Author Comments', $relation->getLabel());
    }

    public function test_get_resource_returns_configured_resource(): void
    {
        $relation = MorphMany::make('Comments', CommentResource::class);

        $resource = $relation->getResource();

        $this->assertInstanceOf(Resource::class, $resource);
        $this->assertInstanceOf(CommentResource::class, $resource);
    }
}
