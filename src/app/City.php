<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class City extends Eloquent
{
    protected $connection = 'statics';
    protected $collection = 'cities';
    protected $fillable = [
        'id', 'name', 'slug', 'county_id', 'country_id',
    ];
}

