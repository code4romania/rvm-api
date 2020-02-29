<?php

namespace App;

use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Robsonvn\CouchDB\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    const ROLE_RESCUE_OFFICER = 0;
    const ROLE_INSTITUTION_ADMIN = 1;
    const ROLE_NGO_ADMIN = 2;
    const ROLE_DSU_ADMIN = 3;

    protected $connection = 'users';
    protected $collection = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role', 'phone', 'organisation', 'institution', 'added_by'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

}
