<?php

namespace Repat\CliCrud\Tests\Fixtures\Resources;

use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\User;

class OverrideSearchUserResource extends Resource
{
    protected static string $model = User::class;

    protected static string $label = 'OverrideSearchUsers';

    protected static string $singularLabel = 'OverrideSearchUser';

    protected static ?string $title = 'name';

    /**
     * Explicit override: search these raw column names regardless of
     * any ->searchable() opt-ins on the field definitions.
     *
     * @var array<int, string>|null
     */
    protected static ?array $search = ['name', 'email'];

    public static function fields(): array
    {
        return [
            Text::make('Name', 'name'),
            Text::make('Email', 'email'),
        ];
    }

    public static function tableColumns(): array
    {
        return ['id', 'name', 'email'];
    }
}
