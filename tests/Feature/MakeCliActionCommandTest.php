<?php

namespace Repat\CliCrud\Tests\Feature;

use Repat\CliCrud\Tests\TestCase;

class MakeCliActionCommandTest extends TestCase
{
    protected function makeTempPath(): string
    {
        $path = sys_get_temp_dir().'/cli-crud-test-'.uniqid();
        mkdir($path, 0755, true);

        return $path;
    }

    protected function cleanTempPath(string $path): void
    {
        foreach (glob($path.'/*') as $file) {
            unlink($file);
        }
        rmdir($path);
    }

    public function test_generates_action_in_configured_path(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.actions.path' => $tempPath]);
        config(['cli-crud.actions.namespace' => 'App\\CliCrud\\Actions']);

        $this->artisan('make:cli-action', ['name' => 'ActivateUser'])
            ->expectsOutput('Action [ActivateUserAction] created successfully.')
            ->assertExitCode(0);

        $expectedPath = $tempPath.'/ActivateUserAction.php';
        $this->assertFileExists($expectedPath);

        $content = file_get_contents($expectedPath);
        $this->assertStringContainsString('class ActivateUserAction', $content);
        $this->assertStringContainsString('namespace App\\CliCrud\\Actions;', $content);
        $this->assertStringContainsString('extends Action', $content);

        $this->cleanTempPath($tempPath);
    }

    public function test_generates_action_in_configured_namespace(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.actions.path' => $tempPath]);
        config(['cli-crud.actions.namespace' => 'App\\Custom\\Actions']);

        $this->artisan('make:cli-action', ['name' => 'BanUser'])
            ->assertExitCode(0);

        $content = file_get_contents($tempPath.'/BanUserAction.php');
        $this->assertStringContainsString('namespace App\\Custom\\Actions;', $content);

        $this->cleanTempPath($tempPath);
    }

    public function test_default_path_and_namespace_when_config_unset(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.actions.path' => $tempPath]);
        config(['cli-crud.actions.namespace' => 'App\\CliCrud\\Actions']);

        $this->artisan('make:cli-action', ['name' => 'Default'])
            ->assertExitCode(0);

        $this->assertFileExists($tempPath.'/DefaultAction.php');
        $content = file_get_contents($tempPath.'/DefaultAction.php');
        $this->assertStringContainsString('namespace App\\CliCrud\\Actions;', $content);

        $this->cleanTempPath($tempPath);
    }

    public function test_queued_flag_adds_should_queue_interface(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.actions.path' => $tempPath]);
        config(['cli-crud.actions.namespace' => 'App\\CliCrud\\Actions']);

        $this->artisan('make:cli-action', ['name' => 'EmailAccountProfile', '--queued' => true])
            ->assertExitCode(0);

        $content = file_get_contents($tempPath.'/EmailAccountProfileAction.php');
        $this->assertStringContainsString('implements ShouldQueue', $content);
        $this->assertStringContainsString('use Illuminate\\Contracts\\Queue\\ShouldQueue;', $content);

        $this->cleanTempPath($tempPath);
    }

    public function test_destructive_flag_extends_destructive_action(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.actions.path' => $tempPath]);
        config(['cli-crud.actions.namespace' => 'App\\CliCrud\\Actions']);

        $this->artisan('make:cli-action', ['name' => 'DeleteAccount', '--destructive' => true])
            ->assertExitCode(0);

        $content = file_get_contents($tempPath.'/DeleteAccountAction.php');
        $this->assertStringContainsString('extends DestructiveAction', $content);

        $this->cleanTempPath($tempPath);
    }

    public function test_prevents_duplicate_file(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.actions.path' => $tempPath]);
        config(['cli-crud.actions.namespace' => 'App\\CliCrud\\Actions']);

        $this->artisan('make:cli-action', ['name' => 'DuplicateAction'])
            ->assertExitCode(0);

        $this->artisan('make:cli-action', ['name' => 'DuplicateAction'])
            ->expectsOutput('Action [DuplicateAction] already exists!')
            ->assertExitCode(1);

        $this->cleanTempPath($tempPath);
    }

    public function test_name_without_action_suffix_is_appended(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.actions.path' => $tempPath]);
        config(['cli-crud.actions.namespace' => 'App\\CliCrud\\Actions']);

        $this->artisan('make:cli-action', ['name' => 'ResetPassword'])
            ->assertExitCode(0);

        $this->assertFileExists($tempPath.'/ResetPasswordAction.php');

        $this->cleanTempPath($tempPath);
    }

    public function test_name_with_action_suffix_is_kept(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.actions.path' => $tempPath]);
        config(['cli-crud.actions.namespace' => 'App\\CliCrud\\Actions']);

        $this->artisan('make:cli-action', ['name' => 'MyAction'])
            ->assertExitCode(0);

        $this->assertFileExists($tempPath.'/MyAction.php');
        $content = file_get_contents($tempPath.'/MyAction.php');
        $this->assertStringContainsString('class MyAction', $content);

        $this->cleanTempPath($tempPath);
    }
}
