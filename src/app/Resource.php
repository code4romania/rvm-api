<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Resource extends Eloquent
{
    protected $connection = 'resources';
    protected $collection = 'resources';
    protected $fillable = [
        'name', 'type_name', 'quantity', 'category', 'subcategory', 'organisation',
        'county', 'city', 'dimensions', 'comments', 'added_by',
    ];
  
    protected $casts = [
        'quantity' => 'integer',
    ];
}

