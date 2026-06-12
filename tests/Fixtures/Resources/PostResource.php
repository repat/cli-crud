<?php

namespace Repat\CliCrud\Tests\Fixtures\Resources;

use Repat\CliCrud\Fields\Relations\BelongsTo;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Fields\Textarea;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\Post;

class PostResource extends Resource
{
    protected static string $model = Post::class;

    protected static string $label = 'Posts';

    protected static string $singularLabel = 'Post';

    public static function fields(): array
    {
        return [
            BelongsTo::make('User', 'user', UserResource::class)->displayField('name'),
            Text::make('Title', 'title')->required(),
            Textarea::make('Content', 'content')->nullable(),
        ];
    }

    public static function tableColumns(): array
    {
        return ['id', 'title', 'created_at'];
    }
}
