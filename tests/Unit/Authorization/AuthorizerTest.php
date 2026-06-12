<?php

namespace Repat\CliCrud\Tests\Unit\Authorization;

use Illuminate\Support\Facades\Gate;
use Repat\CliCrud\Authorization\Authorizer;
use Repat\CliCrud\Exceptions\UnauthorizedException;
use Repat\CliCrud\Tests\Fixtures\Resources\UserResource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\Fixtures\UserPolicy;
use Repat\CliCrud\Tests\TestCase;

class AuthorizerTest extends TestCase
{
    public function test_allows_all_actions_when_disabled(): void
    {
        $authorizer = new Authorizer(false);
        $resource = new UserResource;

        $this->assertTrue($authorizer->viewAny($resource));
        $this->assertTrue($authorizer->create($resource));

        $user = User::create(['name' => 'Test', 'email' => 'test@example.com']);
        $this->assertTrue($authorizer->view($resource, $user));
        $this->assertTrue($authorizer->update($resource, $user));
        $this->assertTrue($authorizer->delete($resource, $user));
    }

    public function test_allows_all_actions_when_no_policy_exists(): void
    {
        $authorizer = new Authorizer(true);
        $resource = new UserResource;

        $this->assertTrue($authorizer->viewAny($resource));
        $this->assertTrue($authorizer->create($resource));

        $user = User::create(['name' => 'Test', 'email' => 'test@example.com']);
        $this->assertTrue($authorizer->view($resource, $user));
        $this->assertTrue($authorizer->update($resource, $user));
        $this->assertTrue($authorizer->delete($resource, $user));
    }

    public function test_respects_policy_when_exists(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        $user = User::create(['name' => 'Test', 'email' => 'test@example.com']);
        $this->actingAs($user);

        $authorizer = new Authorizer(true);
        $resource = new UserResource;

        $this->assertFalse($authorizer->viewAny($resource));
        $this->assertFalse($authorizer->create($resource));

        $this->assertFalse($authorizer->view($resource, $user));
        $this->assertFalse($authorizer->update($resource, $user));
        $this->assertFalse($authorizer->delete($resource, $user));
    }

    public function test_authorize_methods_throw_exception_when_unauthorized(): void
    {
        Gate::policy(User::class, UserPolicy::class);

        $user = User::create(['name' => 'Test', 'email' => 'test@example.com']);
        $this->actingAs($user);

        $authorizer = new Authorizer(true);
        $resource = new UserResource;

        $this->expectException(UnauthorizedException::class);
        $authorizer->authorizeViewAny($resource);
    }
}
