<?php

namespace Repat\CliCrud\Tests\Fixtures\Resources;

use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\Profile;

class ProfileResource extends Resource
{
    protected static string $model = Profile::class;

    protected static string $label = 'Profiles';

    protected static string $singularLabel = 'Profile';

    protected static ?string $title = 'bio';

    public static function fields(): array
    {
        return [
            Text::make('Bio', 'bio'),
        ];
    }

    public static function tableColumns(): array
    {
        return ['id', 'user_id', 'bio'];
    }
}
