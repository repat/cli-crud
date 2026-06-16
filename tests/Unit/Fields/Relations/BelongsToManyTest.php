<?php

namespace Repat\CliCrud\Tests\Unit\Fields\Relations;

use Repat\CliCrud\Fields\Relations\BelongsToMany;
use Repat\CliCrud\Tests\Fixtures\Resources\PostResource;
use Repat\CliCrud\Tests\TestCase;

class BelongsToManyTest extends TestCase
{
    public function test_get_relation_type_returns_belongs_to_many(): void
    {
        $relation = BelongsToMany::make('Roles', PostResource::class);

        $this->assertSame('belongsToMany', $relation->getRelationType());
    }

    public function test_two_arg_make_derives_name_from_label(): void
    {
        $relation = BelongsToMany::make('User Roles', PostResource::class);

        $this->assertSame('user_roles', $relation->getName());
        $this->assertSame('User Roles', $relation->getLabel());
        $this->assertSame(PostResource::class, $relation->getResourceClass());
    }

    public function test_three_arg_make_uses_explicit_name(): void
    {
        $relation = BelongsToMany::make('Roles', 'assigned_roles', PostResource::class);

        $this->assertSame('assigned_roles', $relation->getName());
        $this->assertSame('Roles', $relation->getLabel());
        $this->assertSame(PostResource::class, $relation->getResourceClass());
    }

    public function test_get_resource_returns_configured_resource(): void
    {
        $relation = BelongsToMany::make('Roles', PostResource::class);

        $resource = $relation->getResource();

        $this->assertInstanceOf(PostResource::class, $resource);
    }
}
