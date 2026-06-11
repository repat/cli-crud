<?php

namespace Repat\CliCrud\Authorization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Repat\CliCrud\Exceptions\UnauthorizedException;
use Repat\CliCrud\Resources\Resource;

class Authorizer
{
    protected bool $enabled;

    public function __construct(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function viewAny(Resource $resource): bool
    {
        if (! $this->enabled) {
            return true;
        }

        if (! $this->hasAuthenticatedUser()) {
            return true;
        }

        $modelClass = $resource::getModel();

        if (! $this->hasPolicy($modelClass)) {
            return true;
        }

        try {
            return Gate::allows('viewAny', $modelClass);
        } catch (\Exception $e) {
            return true;
        }
    }

    public function view(Resource $resource, Model $model): bool
    {
        if (! $this->enabled) {
            return true;
        }

        if (! $this->hasAuthenticatedUser()) {
            return true;
        }

        if (! $this->hasPolicy(get_class($model))) {
            return true;
        }

        try {
            return Gate::allows('view', $model);
        } catch (\Exception $e) {
            return true;
        }
    }

    public function create(Resource $resource): bool
    {
        if (! $this->enabled) {
            return true;
        }

        if (! $this->hasAuthenticatedUser()) {
            return true;
        }

        $modelClass = $resource::getModel();

        if (! $this->hasPolicy($modelClass)) {
            return true;
        }

        try {
            return Gate::allows('create', $modelClass);
        } catch (\Exception $e) {
            return true;
        }
    }

    public function delete(Resource $resource, Model $model): bool
    {
        if (! $this->enabled) {
            return true;
        }

        if (! $this->hasAuthenticatedUser()) {
            return true;
        }

        if (! $this->hasPolicy(get_class($model))) {
            return true;
        }

        try {
            return Gate::allows('delete', $model);
        } catch (\Exception $e) {
            return true;
        }
    }

    public function forceDelete(Resource $resource, Model $model): bool
    {
        if (! $this->enabled) {
            return true;
        }

        if (! $this->hasAuthenticatedUser()) {
            return true;
        }

        if (! $this->hasPolicy(get_class($model))) {
            return true;
        }

        try {
            return Gate::allows('forceDelete', $model);
        } catch (\Exception $e) {
            return true;
        }
    }

    public function restore(Resource $resource, Model $model): bool
    {
        if (! $this->enabled) {
            return true;
        }

        if (! $this->hasAuthenticatedUser()) {
            return true;
        }

        if (! $this->hasPolicy(get_class($model))) {
            return true;
        }

        try {
            return Gate::allows('restore', $model);
        } catch (\Exception $e) {
            return true;
        }
    }

    public function authorizeViewAny(Resource $resource): void
    {
        if (! $this->viewAny($resource)) {
            throw UnauthorizedException::forAction('viewAny', $resource::getModel());
        }
    }

    public function authorizeView(Resource $resource, Model $model): void
    {
        if (! $this->view($resource, $model)) {
            throw UnauthorizedException::forAction('view', get_class($model));
        }
    }

    public function authorizeCreate(Resource $resource): void
    {
        if (! $this->create($resource)) {
            throw UnauthorizedException::forAction('create', $resource::getModel());
        }
    }

    public function authorizeDelete(Resource $resource, Model $model): void
    {
        if (! $this->delete($resource, $model)) {
            throw UnauthorizedException::forAction('delete', get_class($model));
        }
    }

    public function authorizeForceDelete(Resource $resource, Model $model): void
    {
        if (! $this->forceDelete($resource, $model)) {
            throw UnauthorizedException::forAction('forceDelete', get_class($model));
        }
    }

    public function authorizeRestore(Resource $resource, Model $model): void
    {
        if (! $this->restore($resource, $model)) {
            throw UnauthorizedException::forAction('restore', get_class($model));
        }
    }

    protected function hasPolicy(string $modelClass): bool
    {
        try {
            $policy = Gate::getPolicyFor($modelClass);

            return $policy !== null;
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function hasAuthenticatedUser(): bool
    {
        return auth()->check();
    }
}
