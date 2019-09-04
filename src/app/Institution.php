<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Institution extends Eloquent
{
    protected $connection = 'institutions';
    protected $collection = 'institution';
    protected $fillable = [
        'id','name', 'slug',
    ];
}

