<?php

namespace Repat\CliCrud\Tests\Fixtures\Resources;

use Repat\CliCrud\Fields\Boolean;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\User;

class SearchableUserResource extends Resource
{
    protected static string $model = User::class;

    protected static string $label = 'SearchableUsers';

    protected static string $singularLabel = 'SearchableUser';

    protected static ?string $title = 'name';

    public static function fields(): array
    {
        return [
            Text::make('Name', 'name')->searchable(),
            Text::make('Email', 'email')->searchable(),
            Boolean::make('Is Active', 'is_active'),
        ];
    }

    public static function tableColumns(): array
    {
        return ['id', 'name', 'email', 'is_active'];
    }
}
