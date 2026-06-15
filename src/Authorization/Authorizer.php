<?php

namespace Repat\CliCrud\Authorization;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Repat\CliCrud\Exceptions\UnauthorizedException;
use Repat\CliCrud\Resources\Resource;

/**
 * Authorization helper for the CLI CRUD command.
 *
 * NOTE: This authorizer is designed for a CLI context where the runtime
 * is typically not authenticated (no HTTP session, no implicit user).
 * To avoid silently denying all operations, the following branches
 * default to ALLOW:
 *
 *   1. `! $enabled` — when `cli-crud.authorization.enabled` is false.
 *   2. `! hasAuthenticatedUser()` — when no user is logged in.
 *   3. `! hasPolicy($modelClass)` — when the model has no policy class.
 *
 * For HTTP routes, write a dedicated policy / middleware. Do not reuse
 * this authorizer for web requests.
 */
class Authorizer
{
    protected bool $enabled;

    public function __construct(bool $enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function viewAny(Resource $resource): bool
    {
        return $this->authorize('viewAny', $resource);
    }

    public function view(Resource $resource, Model $model): bool
    {
        return $this->authorize('view', $resource, $model);
    }

    public function create(Resource $resource): bool
    {
        return $this->authorize('create', $resource);
    }

    public function update(Resource $resource, Model $model): bool
    {
        return $this->authorize('update', $resource, $model);
    }

    public function delete(Resource $resource, Model $model): bool
    {
        return $this->authorize('delete', $resource, $model);
    }

    public function forceDelete(Resource $resource, Model $model): bool
    {
        return $this->authorize('forceDelete', $resource, $model);
    }

    public function restore(Resource $resource, Model $model): bool
    {
        return $this->authorize('restore', $resource, $model);
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

    public function authorizeUpdate(Resource $resource, Model $model): void
    {
        if (! $this->update($resource, $model)) {
            throw UnauthorizedException::forAction('update', get_class($model));
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

    /**
     * @internal CLI-only. Do not call from HTTP request handlers.
     */
    private function authorize(string $ability, Resource $resource, Model|string|null $subject = null): bool
    {
        if (! $this->enabled) {
            return true;
        }

        if (! $this->hasAuthenticatedUser()) {
            return true;
        }

        if ($subject === null) {
            $subject = $resource::getModel();
        }

        $modelClass = $subject instanceof Model ? get_class($subject) : $subject;

        if (! $this->hasPolicy($modelClass)) {
            return true;
        }

        try {
            return Gate::allows($ability, $subject);
        } catch (\Exception $e) {
            return false;
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
