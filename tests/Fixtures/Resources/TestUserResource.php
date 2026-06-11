<?php

namespace Repat\CliCrud\Tests\Fixtures\Resources;

use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Resources\Resource;

class TestUserResource extends Resource
{
    protected static string $model = App\Models\TestUser::class;

    protected static string $label = 'TestUsers';

    protected static string $singularLabel = 'TestUser';

    public static function fields(): array
    {
        return [
            Text::make('name')->required(),
        ];
    }

    public static function tableColumns(): array
    {
        return ['id', 'name', 'created_at'];
    }
}
