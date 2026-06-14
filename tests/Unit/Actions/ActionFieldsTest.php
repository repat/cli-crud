<?php

namespace Repat\CliCrud\Tests\Unit\Actions;

use Repat\CliCrud\Actions\ActionFields;
use Repat\CliCrud\Tests\TestCase;

class ActionFieldsTest extends TestCase
{
    public function test_get_returns_stored_value(): void
    {
        $fields = new ActionFields(['subject' => 'Hello']);

        $this->assertEquals('Hello', $fields->subject);
    }

    public function test_get_throws_for_missing_key(): void
    {
        $this->expectException(\OutOfBoundsException::class);

        $fields = new ActionFields(['subject' => 'Hello']);
        $fields->missing;
    }

    public function test_isset_returns_true_for_existing_key(): void
    {
        $fields = new ActionFields(['subject' => 'Hello']);

        $this->assertTrue(isset($fields->subject));
    }

    public function test_isset_returns_false_for_missing_key(): void
    {
        $fields = new ActionFields(['subject' => 'Hello']);

        $this->assertFalse(isset($fields->missing));
    }

    public function test_set_adds_key(): void
    {
        $fields = new ActionFields([]);
        $fields->subject = 'Hello';

        $this->assertTrue(isset($fields->subject));
        $this->assertEquals('Hello', $fields->subject);
    }

    public function test_only_returns_subset(): void
    {
        $fields = new ActionFields(['a' => 1, 'b' => 2, 'c' => 3]);

        $subset = $fields->only(['a', 'c']);

        $this->assertInstanceOf(ActionFields::class, $subset);
        $this->assertEquals(['a' => 1, 'c' => 3], $subset->toArray());
    }

    public function test_except_returns_complement(): void
    {
        $fields = new ActionFields(['a' => 1, 'b' => 2, 'c' => 3]);

        $subset = $fields->except(['b']);

        $this->assertInstanceOf(ActionFields::class, $subset);
        $this->assertEquals(['a' => 1, 'c' => 3], $subset->toArray());
    }

    public function test_to_array_returns_raw_values(): void
    {
        $values = ['a' => 1, 'b' => 'two', 'c' => null];
        $fields = new ActionFields($values);

        $this->assertEquals($values, $fields->toArray());
    }
}
