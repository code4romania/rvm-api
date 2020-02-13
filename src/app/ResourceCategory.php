<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class ResourceCategory extends Eloquent
{
    protected $connection = 'resources';
    protected $collection = 'resource_categories';
    protected $fillable = [
        'id', 'parent_id', 'name',
    ];
}

