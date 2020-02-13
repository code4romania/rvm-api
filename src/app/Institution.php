<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Institution extends Eloquent
{
    protected $connection = 'institutions';
    protected $collection = 'institutions';
    protected $fillable = [
        'id','name', 'slug',
    ];
}

