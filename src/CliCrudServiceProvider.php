<?php

namespace Repat\CliCrud;

use Illuminate\Support\ServiceProvider;
use Repat\CliCrud\Authorization\Authorizer;
use Repat\CliCrud\Commands\CrudCommand;
use Repat\CliCrud\Commands\MakeCliResourceCommand;
use Repat\CliCrud\Forms\FormBuilder;
use Repat\CliCrud\Resources\ResourceRegistrar;
use Repat\CliCrud\Tables\TableRenderer;
use Repat\CliCrud\Validation\FieldValidator;
use Repat\CliCrud\Views\DetailViewRenderer;

class CliCrudServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/cli-crud.php', 'cli-crud');

        $this->app->singleton(ResourceRegistrar::class, function ($app) {
            $path = config('cli-crud.resources.path');
            $namespace = config('cli-crud.resources.namespace');

            return new ResourceRegistrar($path, $namespace);
        });

        $this->app->singleton(Authorizer::class, function ($app) {
            $enabled = config('cli-crud.authorization.enabled', true);

            return new Authorizer($enabled);
        });

        $this->app->singleton(TableRenderer::class, function ($app) {
            return new TableRenderer;
        });

        $this->app->singleton(FormBuilder::class, function ($app) {
            return new FormBuilder;
        });

        $this->app->singleton(FieldValidator::class, function ($app) {
            return new FieldValidator;
        });

        $this->app->singleton(DetailViewRenderer::class, function ($app) {
            return new DetailViewRenderer;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/cli-crud.php' => config_path('cli-crud.php'),
            ], 'cli-crud-config');

            $this->publishes([
                __DIR__.'/../stubs/cli-resource.stub' => base_path('stubs/cli-crud/cli-resource.stub'),
            ], 'cli-crud-stubs');

            $this->commands([
                CrudCommand::class,
                MakeCliResourceCommand::class,
            ]);
        }
    }
}
