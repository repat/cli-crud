<?php

namespace Repat\CliCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeCliResourceCommand extends Command
{
    protected $signature = 'make:cli-resource {name : The name of the resource}';

    protected $description = 'Create a new CLI CRUD resource class';

    public function handle(Filesystem $files): int
    {
        $name = $this->argument('name');
        $className = Str::studly($name);

        if (! Str::endsWith($className, 'Resource')) {
            $className .= 'Resource';
        }

        $path = $this->getPath($className);

        if ($files->exists($path)) {
            $this->error("Resource [{$className}] already exists!");

            return self::FAILURE;
        }

        $files->ensureDirectoryExists(dirname($path));
        $files->put($path, $this->buildClass($className));

        $this->info("Resource [{$className}] created successfully.");
        $this->line("Path: {$path}");

        return self::SUCCESS;
    }

    protected function getPath(string $className): string
    {
        $path = config('cli-crud.resources.path', app_path('CliCrud/Resources'));

        return $path.DIRECTORY_SEPARATOR.$className.'.php';
    }

    protected function buildClass(string $className): string
    {
        $stub = $this->getStub();

        $namespace = config('cli-crud.resources.namespace', 'App\\CliCrud\\Resources');
        $modelName = Str::beforeLast($className, 'Resource');
        $modelClass = "App\\Models\\{$modelName}";

        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ model }}', '{{ label }}', '{{ singularLabel }}'],
            [$namespace, $className, $modelClass, Str::plural($modelName), $modelName],
            $stub
        );

        return $stub;
    }

    protected function getStub(): string
    {
        $customStub = base_path('stubs/cli-crud/cli-resource.stub');

        if (file_exists($customStub)) {
            return file_get_contents($customStub);
        }

        return file_get_contents(__DIR__.'/../../stubs/cli-resource.stub');
    }
}
