<?php

/**
 * Manual verification script for relation table alignment fix.
 *
 * This script demonstrates that the separator line now properly aligns
 * with the header and has gaps (spaces) between columns.
 *
 * Run with: php tests/manual/relation_table_test.php
 */

require __DIR__.'/../../vendor/autoload.php';

use Orchestra\Testbench\TestCase;
use Repat\CliCrud\CliCrudServiceProvider;
use Repat\CliCrud\Tests\Fixtures\Post;
use Repat\CliCrud\Tests\Fixtures\Resources\UserResource;
use Repat\CliCrud\Tests\Fixtures\User;
use Repat\CliCrud\Views\DetailViewRenderer;

class RelationTableTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [CliCrudServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Fixtures/database/migrations');
    }

    public function runTest(): void
    {
        $this->setUp();

        echo "\n=== Relation Table Alignment Test ===\n\n";

        // Create test data
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'is_active' => true,
        ]);

        Post::create([
            'user_id' => $user->id,
            'title' => 'First Post',
            'content' => 'This is the first post content',
        ]);

        Post::create([
            'user_id' => $user->id,
            'title' => 'Second Post',
            'content' => 'This is the second post content',
        ]);

        // Render the detail view
        $renderer = new DetailViewRenderer;
        $renderer->render($user, new UserResource);

        echo "\n=== Verification Points ===\n";
        echo "1. The separator line should have gaps (spaces) between dashes\n";
        echo "2. The right border (│) should align perfectly with the header\n";
        echo "3. All columns should be properly aligned\n";
        echo "\n";
    }
}

$test = new RelationTableTest('runTest');
$test->runTest();
