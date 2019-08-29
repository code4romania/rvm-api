<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Allocation extends Eloquent
{
    protected $connection = 'allocations';
    protected $collection = 'allocations';
    protected $fillable = [
        'rescue_officer', 'volunteer', 'organisation', 'county', 'city', 'added_by'
    ];
}
