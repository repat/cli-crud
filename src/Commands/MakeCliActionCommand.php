<?php

namespace Repat\CliCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeCliActionCommand extends Command
{
    protected $signature = 'make:cli-action {name : The name of the action} {--queued : Generate a queued (ShouldQueue) action} {--destructive : Generate a destructive action}';

    protected $description = 'Create a new CLI CRUD action class';

    public function handle(Filesystem $files): int
    {
        $name = $this->argument('name');
        $className = Str::studly($name);

        if (! Str::endsWith($className, 'Action')) {
            $className .= 'Action';
        }

        $path = $this->getPath($className);

        if ($files->exists($path)) {
            $this->error("Action [{$className}] already exists!");

            return self::FAILURE;
        }

        $files->ensureDirectoryExists(dirname($path));
        $files->put($path, $this->buildClass($className));

        $this->info("Action [{$className}] created successfully.");
        $this->line("Path: {$path}");

        return self::SUCCESS;
    }

    protected function getPath(string $className): string
    {
        $path = (string) config('cli-crud.actions.path', app_path('CliCrud/Actions'));

        return $path.DIRECTORY_SEPARATOR.$className.'.php';
    }

    protected function buildClass(string $className): string
    {
        $stub = $this->getStub();

        $namespace = (string) config('cli-crud.actions.namespace', 'App\\CliCrud\\Actions');
        $displayName = Str::headline(
            Str::replaceLast('Action', '', $className)
        );

        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ displayName }}'],
            [$namespace, $className, $displayName],
            $stub
        );

        return $stub;
    }

    protected function getStub(): string
    {
        $customStub = base_path('stubs/cli-crud/'.$this->stubName());

        if (file_exists($customStub)) {
            return file_get_contents($customStub);
        }

        return file_get_contents(__DIR__.'/../../stubs/'.$this->stubName());
    }

    protected function stubName(): string
    {
        if ($this->option('queued')) {
            return 'cli-queued-action.stub';
        }

        if ($this->option('destructive')) {
            return 'cli-destructive-action.stub';
        }

        return 'cli-action.stub';
    }
}
