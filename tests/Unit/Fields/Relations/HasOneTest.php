<?php

namespace Repat\CliCrud\Tests\Unit\Fields\Relations;

use Repat\CliCrud\Fields\Relations\HasOne;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\Resources\ProfileResource;
use Repat\CliCrud\Tests\TestCase;

class HasOneTest extends TestCase
{
    public function test_get_relation_type_returns_has_one(): void
    {
        $relation = HasOne::make('Profile', ProfileResource::class);

        $this->assertEquals('hasOne', $relation->getRelationType());
    }

    public function test_two_arg_make_derives_name_from_label(): void
    {
        $relation = HasOne::make('Profile', ProfileResource::class);

        $this->assertEquals('profile', $relation->getName());
        $this->assertEquals('Profile', $relation->getLabel());
        $this->assertEquals(ProfileResource::class, $relation->getResourceClass());
    }

    public function test_three_arg_make_uses_explicit_name(): void
    {
        $relation = HasOne::make('User Profile', 'profile', ProfileResource::class);

        $this->assertEquals('profile', $relation->getName());
        $this->assertEquals('User Profile', $relation->getLabel());
    }

    public function test_get_resource_returns_configured_resource(): void
    {
        $relation = HasOne::make('Profile', ProfileResource::class);

        $resource = $relation->getResource();

        $this->assertInstanceOf(Resource::class, $resource);
        $this->assertInstanceOf(ProfileResource::class, $resource);
    }
}
