<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Resource extends Eloquent
{
    protected $connection = 'resources';
    protected $collection = 'resources';
    protected $fillable = [
        'name', 'type_name', 'slug', 'categories', 'quantity', 'unit', 'size', 'comments',
        'county', 'city', 'address', 'organisation', 'added_by',
    ];
  
    protected $casts = [
        'quantity' => 'integer',
    ];
}

