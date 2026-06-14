<?php

namespace Repat\CliCrud\Tests\Unit\Resources;

use Illuminate\Database\Eloquent\Builder;
use Repat\CliCrud\Tests\Fixtures\Resources\OverrideSearchUserResource;
use Repat\CliCrud\Tests\Fixtures\Resources\SearchableUserResource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class ResourceSearchTest extends TestCase
{
    public function test_searchable_fields_derives_from_marked_fields(): void
    {
        $fields = SearchableUserResource::searchableFields();

        $this->assertEquals(['name', 'email'], $fields);
    }

    public function test_searchable_fields_excludes_unmarked_fields(): void
    {
        $fields = SearchableUserResource::searchableFields();

        $this->assertNotContains('is_active', $fields);
    }

    public function test_searchable_fields_returns_empty_when_no_fields_marked(): void
    {
        $resource = new class extends \Repat\CliCrud\Resources\Resource
        {
            protected static string $model = User::class;

            protected static string $label = 'X';

            protected static string $singularLabel = 'X';

            public static function fields(): array
            {
                return [];
            }

            public static function tableColumns(): array
            {
                return ['id'];
            }
        };

        $this->assertSame([], $resource::searchableFields());
    }

    public function test_search_property_overrides_searchable_fields(): void
    {
        $fields = OverrideSearchUserResource::searchableFields();

        $this->assertEquals(['name', 'email'], $fields);
    }

    public function test_default_search_using_returns_query_for_empty_term(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com']);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com']);

        $query = User::query();
        $result = SearchableUserResource::searchUsing($query, '');

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertEquals(2, $result->count());
    }

    public function test_default_search_using_returns_query_for_whitespace_term(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com']);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com']);

        $query = User::query();
        $result = SearchableUserResource::searchUsing($query, '   ');

        $this->assertInstanceOf(Builder::class, $result);
        $this->assertEquals(2, $result->count());
    }

    public function test_default_search_using_applies_like_clauses(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com']);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com']);
        User::create(['name' => 'Charlie', 'email' => 'charlie@example.com']);

        $result = SearchableUserResource::searchUsing(User::query(), 'alice');

        $this->assertEquals(1, $result->count());
        $this->assertEquals('Alice', $result->first()->name);
    }

    public function test_default_search_using_matches_across_searchable_fields(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@work.com']);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com']);
        User::create(['name' => 'Charlie', 'email' => 'charlie@example.com']);

        $result = SearchableUserResource::searchUsing(User::query(), 'example');

        $this->assertEquals(2, $result->count());
    }

    public function test_default_search_using_does_not_match_unmarked_fields(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com', 'is_active' => true]);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com', 'is_active' => false]);

        $result = SearchableUserResource::searchUsing(User::query(), 'true');

        $this->assertEquals(0, $result->count());
    }

    public function test_default_search_using_preserves_other_query_constraints(): void
    {
        User::create(['name' => 'Alice', 'email' => 'alice@example.com']);
        User::create(['name' => 'Alice2', 'email' => 'alice2@example.com']);
        User::create(['name' => 'Bob', 'email' => 'bob@example.com']);

        $query = User::query()->where('email', 'like', '%@example.com');
        $result = SearchableUserResource::searchUsing($query, 'alice');

        $this->assertEquals(2, $result->count());
    }
}
