<?php

namespace Repat\CliCrud\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['commentable_type', 'commentable_id', 'body'];

    public function commentable()
    {
        return $this->morphTo();
    }
}
