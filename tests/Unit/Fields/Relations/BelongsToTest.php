<?php

namespace Repat\CliCrud\Tests\Unit\Fields\Relations;

use InvalidArgumentException;
use Repat\CliCrud\Fields\Relations\BelongsTo;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\Post;
use Repat\CliCrud\Tests\Fixtures\Resources\UserResource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class BelongsToTest extends TestCase
{
    public function test_belongs_to_can_be_marked_as_required(): void
    {
        $field = BelongsTo::make('User', 'user', UserResource::class);
        $this->assertFalse($field->isRequired());

        $field->required();
        $this->assertTrue($field->isRequired());
    }

    public function test_belongs_to_required_returns_self(): void
    {
        $field = BelongsTo::make('User', 'user', UserResource::class);
        $result = $field->required();
        $this->assertSame($field, $result);
    }

    public function test_get_foreign_key_returns_correct_column(): void
    {
        $field = BelongsTo::make('User', 'user', UserResource::class);
        $post = new Post;

        $foreignKey = $field->getForeignKey($post);
        $this->assertEquals('user_id', $foreignKey);
    }

    public function test_get_foreign_key_caches_result(): void
    {
        $field = BelongsTo::make('User', 'user', UserResource::class);
        $post = new Post;

        $first = $field->getForeignKey($post);
        $second = $field->getForeignKey($post);

        $this->assertEquals($first, $second);

        // Verify it's the same cached value by checking the property
        $reflection = new \ReflectionClass($field);
        $property = $reflection->getProperty('foreignKey');
        $property->setAccessible(true);
        $cachedValue = $property->getValue($field);

        $this->assertEquals('user_id', $cachedValue);
    }

    public function test_get_foreign_key_throws_exception_for_missing_relationship(): void
    {
        $field = BelongsTo::make('Category', 'category', UserResource::class);
        $post = new Post;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Relationship method 'category' does not exist on model");

        $field->getForeignKey($post);
    }

    public function test_get_foreign_key_throws_exception_for_wrong_relationship_type(): void
    {
        // User has posts() as hasMany, not belongsTo
        $field = BelongsTo::make('Posts', 'posts', UserResource::class);
        $user = new User;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('is not a BelongsTo relationship');

        $field->getForeignKey($user);
    }

    public function test_belongs_to_with_custom_name(): void
    {
        $field = BelongsTo::make('Author', 'author', UserResource::class);
        $post = new Post;

        // This will throw an exception because Post doesn't have an 'author' relationship
        $this->expectException(InvalidArgumentException::class);
        $field->getForeignKey($post);
    }

    public function test_belongs_to_display_field_can_be_set(): void
    {
        $field = BelongsTo::make('User', 'user', UserResource::class);
        $result = $field->displayField('email');

        $this->assertSame($field, $result);
        $this->assertEquals('email', $field->getDisplayField());
    }

    public function test_belongs_to_display_field_defaults_to_null(): void
    {
        $field = BelongsTo::make('User', 'user', UserResource::class);
        $this->assertNull($field->getDisplayField());
    }

    public function test_belongs_to_get_relation_type(): void
    {
        $field = BelongsTo::make('User', 'user', UserResource::class);
        $this->assertEquals('belongsTo', $field->getRelationType());
    }

    public function test_belongs_to_get_resource(): void
    {
        $field = BelongsTo::make('User', 'user', UserResource::class);
        $resource = $field->getResource();

        $this->assertInstanceOf(Resource::class, $resource);
        $this->assertInstanceOf(UserResource::class, $resource);
    }

    public function test_belongs_to_get_name(): void
    {
        $field = BelongsTo::make('User', 'user', UserResource::class);
        $this->assertEquals('user', $field->getName());
    }

    public function test_belongs_to_get_label(): void
    {
        $field = BelongsTo::make('User', 'user', UserResource::class);
        $this->assertEquals('User', $field->getLabel());
    }
}
