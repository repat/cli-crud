<?php

namespace Repat\CliCrud\Tests\Fixtures\Resources;

use Repat\CliCrud\Fields\Relations\MorphTo;
use Repat\CliCrud\Fields\Text;
use Repat\CliCrud\Resources\Resource;
use Repat\CliCrud\Tests\Fixtures\Comment;

class CommentResource extends Resource
{
    protected static string $model = Comment::class;

    protected static string $label = 'Comments';

    protected static string $singularLabel = 'Comment';

    protected static ?string $title = 'body';

    public static function fields(): array
    {
        return [
            Text::make('Body', 'body')->required(),
            MorphTo::make('Commentable', 'commentable', [PostResource::class]),
        ];
    }

    public static function tableColumns(): array
    {
        return ['id', 'body', 'created_at'];
    }
}
