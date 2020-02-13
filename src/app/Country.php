<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Country extends Eloquent
{
    protected $connection = 'statics';
    protected $collection = 'countries';
    protected $fillable = [
        'id', 'name', 'slug',
    ];
}

