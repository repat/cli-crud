<?php

namespace Repat\CliCrud\Tests\Unit\Actions;

use Repat\CliCrud\Actions\ActionResponse;
use Repat\CliCrud\Tests\TestCase;

class ActionResponseTest extends TestCase
{
    public function test_message_constructor(): void
    {
        $response = new ActionResponse('Hello');

        $this->assertEquals('Hello', $response->getMessage());
        $this->assertFalse($response->isDanger());
    }

    public function test_message_factory(): void
    {
        $response = ActionResponse::message('Hello');

        $this->assertEquals('Hello', $response->getMessage());
        $this->assertFalse($response->isDanger());
    }

    public function test_danger_factory(): void
    {
        $response = ActionResponse::danger('Boom');

        $this->assertEquals('Boom', $response->getMessage());
        $this->assertTrue($response->isDanger());
    }

    public function test_default_constructor_has_no_message(): void
    {
        $response = new ActionResponse;

        $this->assertNull($response->getMessage());
        $this->assertFalse($response->isDanger());
    }
}
