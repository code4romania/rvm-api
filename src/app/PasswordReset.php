<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class PasswordReset extends Eloquent
{
    protected $connection = 'rvm';
    protected $collection = 'password_reset_token';
    protected $fillable = [
        'email', 'token'
    ];
}
