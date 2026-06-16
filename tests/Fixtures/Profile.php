<?php

namespace Repat\CliCrud\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profile extends Model
{
    protected $fillable = ['user_id', 'bio'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
