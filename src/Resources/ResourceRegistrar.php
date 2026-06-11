<?php

namespace Repat\CliCrud\Resources;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ResourceRegistrar
{
    protected array $resources = [];

    public function __construct(
        protected string $path,
        protected string $namespace
    ) {
        $this->discoverResources();
    }

    protected function discoverResources(): void
    {
        if (!File::isDirectory($this->path)) {
            return;
        }

        $files = File::allFiles($this->path);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassNameFromFile($file);

            if ($className && class_exists($className) && is_subclass_of($className, Resource::class)) {
                $this->resources[] = $className;
            }
        }
    }

    protected function getClassNameFromFile(\SplFileInfo $file): ?string
    {
        $relativePath = Str::after($file->getPathname(), $this->path . DIRECTORY_SEPARATOR);
        $relativePath = Str::beforeLast($relativePath, '.php');
        $relativePath = str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

        return $this->namespace . '\\' . $relativePath;
    }

    public function register(string $resourceClass): void
    {
        if (!in_array($resourceClass, $this->resources)) {
            $this->resources[] = $resourceClass;
        }
    }

    /**
     * @return array<class-string<Resource>>
     */
    public function getResources(): array
    {
        return $this->resources;
    }

    public function findByModel(string $modelClass): ?string
    {
        foreach ($this->resources as $resource) {
            if ($resource::getModel() === $modelClass) {
                return $resource;
            }
        }

        return null;
    }
}
