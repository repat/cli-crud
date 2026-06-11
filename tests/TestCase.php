<?php

namespace Repat\CliCrud\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Repat\CliCrud\CliCrudServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            CliCrudServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['config']->set('cli-crud.resources.path', __DIR__ . '/Fixtures/Resources');
        $app['config']->set('cli-crud.resources.namespace', 'Repat\\CliCrud\\Tests\\Fixtures\\Resources');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Fixtures/database/migrations');
    }
}
