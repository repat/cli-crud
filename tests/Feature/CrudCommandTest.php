<?php

namespace Repat\CliCrud\Tests\Feature;

use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class CrudCommandTest extends TestCase
{
    public function test_make_cli_resource_command_creates_resource(): void
    {
        config(['cli-crud.resources.path' => app_path('CliCrud/Resources')]);
        config(['cli-crud.resources.namespace' => 'App\\CliCrud\\Resources']);
        
        $this->artisan('make:cli-resource', ['name' => 'TestUser'])
            ->expectsOutput('Resource [TestUserResource] created successfully.')
            ->assertExitCode(0);

        $expectedPath = app_path('CliCrud/Resources/TestUserResource.php');
        $this->assertFileExists($expectedPath);

        $content = file_get_contents($expectedPath);
        $this->assertStringContainsString('class TestUserResource', $content);
        $this->assertStringContainsString('App\Models\TestUser::class', $content);

        unlink($expectedPath);
        rmdir(app_path('CliCrud/Resources'));
        rmdir(app_path('CliCrud'));
    }

    public function test_make_cli_resource_command_prevents_duplicate(): void
    {
        config(['cli-crud.resources.path' => app_path('CliCrud/Resources')]);
        config(['cli-crud.resources.namespace' => 'App\\CliCrud\\Resources']);
        
        $this->artisan('make:cli-resource', ['name' => 'TestUser'])
            ->assertExitCode(0);

        $this->artisan('make:cli-resource', ['name' => 'TestUser'])
            ->expectsOutput('Resource [TestUserResource] already exists!')
            ->assertExitCode(1);

        $path = app_path('CliCrud/Resources/TestUserResource.php');
        unlink($path);
        rmdir(app_path('CliCrud/Resources'));
        rmdir(app_path('CliCrud'));
    }
}
