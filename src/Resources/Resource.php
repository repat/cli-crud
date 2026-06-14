<?php

namespace Repat\CliCrud\Resources;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Repat\CliCrud\Actions\Action;
use Repat\CliCrud\Cards\Card;
use Repat\CliCrud\Fields\Field;
use Repat\CliCrud\Fields\Relations\Relation;

abstract class Resource
{
    protected static string $model;

    protected static string $label;

    protected static string $singularLabel;

    protected static ?string $title = null;

    /**
     * Optional explicit override for searchable column names. When set,
     * takes precedence over fields marked with ->searchable().
     *
     * @var array<int, string>|null
     */
    protected static ?array $search = null;

    /**
     * @return array<Field|Relation>
     */
    abstract public static function fields(): array;

    /**
     * @return array<string>
     */
    abstract public static function tableColumns(): array;

    public static function getModel(): string
    {
        return static::$model;
    }

    public static function getModelInstance(): Model
    {
        $modelClass = static::$model;

        return new $modelClass;
    }

    public static function getLabel(): string
    {
        return static::$label;
    }

    public static function getSingularLabel(): string
    {
        return static::$singularLabel;
    }

    public static function getTitle(): string
    {
        $title = static::$title;

        if ($title === null) {
            throw new \RuntimeException(sprintf(
                'Resource [%s] must define a $title property.',
                static::class
            ));
        }

        if (! \Illuminate\Support\Facades\Schema::hasColumn(static::getModelInstance()->getTable(), $title)) {
            throw new \RuntimeException(sprintf(
                'The column "%s" set as $title on resource [%s] does not exist in table "%s".',
                $title,
                static::class,
                static::getModelInstance()->getTable()
            ));
        }

        return $title;
    }

    /**
     * @return array<Field>
     */
    public static function getFields(): array
    {
        return array_filter(static::fields(), fn ($field) => $field instanceof Field);
    }

    /**
     * @return array<Relation>
     */
    public static function getRelations(): array
    {
        return array_filter(static::fields(), fn ($field) => $field instanceof Relation);
    }

    public static function usesSoftDeletes(): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive(static::$model));
    }

    /**
     * Resolve the list of column names the resource can be searched on.
     *
     * Resolution order:
     *   1. The explicit $search override on the subclass (raw column names).
     *   2. Names of all Field instances that opted in via ->searchable().
     *
     * @return array<int, string>
     */
    public static function searchableFields(): array
    {
        if (is_array(static::$search)) {
            return array_values(array_unique(array_map('strval', static::$search)));
        }

        $names = [];
        foreach (static::getFields() as $field) {
            if ($field->isSearchable()) {
                $names[] = $field->getName();
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Apply the search term to the query. Override this method to integrate
     * with a full-text search engine such as Laravel Scout, Algolia, or
     * Meilisearch. The method must return an Eloquent Builder.
     */
    public static function searchUsing(Builder $query, string $term): Builder
    {
        $searchable = static::searchableFields();
        $trimmed = trim($term);

        if ($trimmed === '' || empty($searchable)) {
            return $query;
        }

        $like = '%'.$trimmed.'%';

        return $query->where(function (Builder $inner) use ($searchable, $like) {
            foreach ($searchable as $field) {
                $inner->orWhere($field, 'like', $like);
            }
        });
    }

    /**
     * @return array<Card>
     */
    public static function cards(): array
    {
        return [];
    }

    /**
     * @return array<Card>
     */
    public static function getCards(): array
    {
        return static::cards();
    }

    /**
     * Declare the actions available for this resource. Return an array
     * of Action class strings or pre-built Action instances. The user
     * picks one from the "Run action..." sub-menu in the list and
     * detail views.
     *
     * @return array<int, class-string<Action>|Action>
     */
    public static function actions(): array
    {
        return [];
    }

    /**
     * Resolve actions() to Action instances. Class strings are
     * instantiated; instances are kept as-is.
     *
     * @return array<int, Action>
     */
    public static function getActions(): array
    {
        $resolved = [];

        foreach (static::actions() as $action) {
            $resolved[] = $action instanceof Action ? $action : new $action;
        }

        return $resolved;
    }
}
