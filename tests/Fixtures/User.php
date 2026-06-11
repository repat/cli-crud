<?php

namespace Repat\CliCrud\Tests\Fixtures;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use SoftDeletes;

    protected $fillable = ['name', 'email', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
