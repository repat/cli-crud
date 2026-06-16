<?php

namespace Repat\CliCrud\Tests\Unit\Fields\Relations;

use Repat\CliCrud\Fields\Relations\MorphOne;
use Repat\CliCrud\Tests\Fixtures\Resources\PostResource;
use Repat\CliCrud\Tests\TestCase;

class MorphOneTest extends TestCase
{
    public function test_get_relation_type_returns_morph_one(): void
    {
        $relation = MorphOne::make('Profile', PostResource::class);

        $this->assertSame('morphOne', $relation->getRelationType());
    }

    public function test_two_arg_make_derives_name_from_label(): void
    {
        $relation = MorphOne::make('User Profile', PostResource::class);

        $this->assertSame('user_profile', $relation->getName());
        $this->assertSame('User Profile', $relation->getLabel());
        $this->assertSame(PostResource::class, $relation->getResourceClass());
    }

    public function test_three_arg_make_uses_explicit_name(): void
    {
        $relation = MorphOne::make('Profile', 'user_profile', PostResource::class);

        $this->assertSame('user_profile', $relation->getName());
        $this->assertSame('Profile', $relation->getLabel());
        $this->assertSame(PostResource::class, $relation->getResourceClass());
    }

    public function test_get_resource_returns_configured_resource(): void
    {
        $relation = MorphOne::make('Profile', PostResource::class);

        $resource = $relation->getResource();

        $this->assertInstanceOf(PostResource::class, $resource);
    }
}
