<?php

namespace Repat\CliCrud\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Repat\CliCrud\Fields\Field;
use Repat\CliCrud\Fields\Relations\Relation;

abstract class Resource
{
    protected static string $model;

    protected static string $label;

    protected static string $singularLabel;

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
}
