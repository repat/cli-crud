<?php

namespace Repat\CliCrud\Tests\Fixtures;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'is_active', 'password'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }
}
