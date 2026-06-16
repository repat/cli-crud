<?php

namespace Repat\CliCrud\Tests\Unit\Fields\Relations;

use Repat\CliCrud\Fields\Relations\MorphedByMany;
use Repat\CliCrud\Tests\Fixtures\Resources\PostResource;
use Repat\CliCrud\Tests\TestCase;

class MorphedByManyTest extends TestCase
{
    public function test_get_relation_type_returns_morphed_by_many(): void
    {
        $relation = MorphedByMany::make('Teams', PostResource::class);

        $this->assertSame('morphedByMany', $relation->getRelationType());
    }

    public function test_two_arg_make_derives_name_from_label(): void
    {
        $relation = MorphedByMany::make('User Teams', PostResource::class);

        $this->assertSame('user_teams', $relation->getName());
        $this->assertSame('User Teams', $relation->getLabel());
        $this->assertSame(PostResource::class, $relation->getResourceClass());
    }

    public function test_three_arg_make_uses_explicit_name(): void
    {
        $relation = MorphedByMany::make('Teams', 'user_teams', PostResource::class);

        $this->assertSame('user_teams', $relation->getName());
        $this->assertSame('Teams', $relation->getLabel());
        $this->assertSame(PostResource::class, $relation->getResourceClass());
    }

    public function test_get_resource_returns_configured_resource(): void
    {
        $relation = MorphedByMany::make('Teams', PostResource::class);

        $resource = $relation->getResource();

        $this->assertInstanceOf(PostResource::class, $resource);
    }
}
