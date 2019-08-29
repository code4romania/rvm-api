<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class County extends Eloquent
{
    protected $connection = 'statics';
    protected $collection = 'counties';
    protected $fillable = [
        'id', 'name', 'slug', 'country_id',
    ];
}

