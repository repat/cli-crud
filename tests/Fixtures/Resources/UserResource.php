<?php

namespace Repat\CliCrud\Tests\Fixtures\Resources;

use Repat\CliCrud\Fields\Boolean;
use Repat\CliCrud\Fields\Relations\HasMany;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\User;

class UserResource extends Resource
{
    protected static string $model = User::class;

    protected static string $label = 'Users';

    protected static string $singularLabel = 'User';
    protected static ?string $title = 'name';

    public static function fields(): array
    {
        return [
            Text::make('Name', 'name')->required(),
            Text::make('Email', 'email')->required()->email(),
            Boolean::make('Is Active', 'is_active')->default(true),
            HasMany::make('Posts', 'posts', PostResource::class),
        ];
    }

    public static function tableColumns(): array
    {
        return ['id', 'name', 'email', 'is_active', 'created_at'];
    }
}
