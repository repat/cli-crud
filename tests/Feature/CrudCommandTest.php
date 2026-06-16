<?php

namespace Repat\CliCrud\Tests\Feature;

use Repat\CliCrud\Tests\Fixtures\Post;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Tests\TestCase;

class CrudCommandTest extends TestCase
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

    public function test_make_cli_resource_command_creates_resource(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.resources.path' => $tempPath]);
        config(['cli-crud.resources.namespace' => 'App\\CliCrud\\Resources']);

        $this->artisan('make:cli-resource', ['name' => 'TestUser'])
            ->expectsOutput('Resource [TestUserResource] created successfully.')
            ->assertExitCode(0);

        $expectedPath = $tempPath.'/TestUserResource.php';
        $this->assertFileExists($expectedPath);

        $content = file_get_contents($expectedPath);
        $this->assertStringContainsString('class TestUserResource', $content);
        $this->assertStringContainsString('App\Models\TestUser::class', $content);

        $this->cleanTempPath($tempPath);
    }

    public function test_make_cli_resource_command_prevents_duplicate(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.resources.path' => $tempPath]);
        config(['cli-crud.resources.namespace' => 'App\\CliCrud\\Resources']);

        $this->artisan('make:cli-resource', ['name' => 'TestUser'])
            ->assertExitCode(0);

        $this->artisan('make:cli-resource', ['name' => 'TestUser'])
            ->expectsOutput('Resource [TestUserResource] already exists!')
            ->assertExitCode(1);

        $this->cleanTempPath($tempPath);
    }

    public function test_make_cli_resource_with_model_option_creates_resource(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.resources.path' => $tempPath]);
        config(['cli-crud.resources.namespace' => 'App\\CliCrud\\Resources']);

        $this->artisan('make:cli-resource', [
            'name' => 'ModelUser',
            '--model' => User::class,
        ])
            ->expectsOutput('Resource [ModelUserResource] created successfully.')
            ->assertExitCode(0);

        $expectedPath = $tempPath.'/ModelUserResource.php';
        $this->assertFileExists($expectedPath);

        $content = file_get_contents($expectedPath);
        $this->assertStringContainsString('class ModelUserResource extends Resource', $content);
        $this->assertStringContainsString('User::class', $content);

        $this->cleanTempPath($tempPath);
    }

    public function test_make_cli_resource_with_model_option_generates_correct_fields(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.resources.path' => $tempPath]);
        config(['cli-crud.resources.namespace' => 'App\\CliCrud\\Resources']);

        $this->artisan('make:cli-resource', [
            'name' => 'SchemaUser',
            '--model' => User::class,
        ])->assertExitCode(0);

        $expectedPath = $tempPath.'/SchemaUserResource.php';
        $content = file_get_contents($expectedPath);

        $this->assertStringContainsString("Text::make('Name', 'name')", $content);
        $this->assertStringContainsString("Text::make('Email', 'email')", $content);
        $this->assertStringContainsString('->email()', $content);
        $this->assertStringContainsString("Text::make('Password', 'password')", $content);
        $this->assertStringContainsString('->password()', $content);
        $this->assertStringContainsString("Boolean::make('Is Active', 'is_active')", $content);

        $this->assertStringContainsString("'id', 'name', 'email', 'password', 'is_active', 'created_at'", $content);

        $this->cleanTempPath($tempPath);
    }

    public function test_make_cli_resource_with_model_option_handles_various_types(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.resources.path' => $tempPath]);
        config(['cli-crud.resources.namespace' => 'App\\CliCrud\\Resources']);

        $this->artisan('make:cli-resource', [
            'name' => 'SchemaPost',
            '--model' => Post::class,
        ])->assertExitCode(0);

        $expectedPath = $tempPath.'/SchemaPostResource.php';
        $content = file_get_contents($expectedPath);

        $this->assertStringContainsString("Number::make('User', 'user_id')", $content);
        $this->assertStringContainsString("Text::make('Title', 'title')", $content);
        $this->assertStringContainsString("Textarea::make('Content', 'content')", $content);
        // SQLite has no native json type; text columns map to Textarea
        $this->assertStringContainsString("Textarea::make('Metadata', 'metadata')", $content);

        $this->assertStringContainsString("'id'", $content);
        $this->assertStringContainsString("'user_id'", $content);
        $this->assertStringContainsString("'title'", $content);
        $this->assertStringContainsString("'content'", $content);
        $this->assertStringContainsString("'metadata'", $content);
        $this->assertStringContainsString("'created_at'", $content);

        $this->cleanTempPath($tempPath);
    }

    public function test_make_cli_resource_with_invalid_model_returns_error(): void
    {
        $this->artisan('make:cli-resource', [
            'name' => 'InvalidModel',
            '--model' => 'App\\Models\\NonExistent',
        ])
            ->expectsOutput('Model class [App\\Models\\NonExistent] not found.')
            ->assertExitCode(1);
    }

    public function test_make_cli_resource_without_model_uses_default_fields(): void
    {
        $tempPath = $this->makeTempPath();
        config(['cli-crud.resources.path' => $tempPath]);
        config(['cli-crud.resources.namespace' => 'App\\CliCrud\\Resources']);

        $this->artisan('make:cli-resource', ['name' => 'DefaultFields'])
            ->assertExitCode(0);

        $expectedPath = $tempPath.'/DefaultFieldsResource.php';
        $content = file_get_contents($expectedPath);

        $this->assertStringContainsString("Text::make('Name', 'name')->required()", $content);
        $this->assertStringContainsString("'id', 'name', 'created_at'", $content);

        $this->cleanTempPath($tempPath);
    }
}
