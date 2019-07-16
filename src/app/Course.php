<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Course extends Eloquent
{
    protected $collection = 'courses_coll';
    protected $fillable = [
        'volunteer_id', 'name', 'acredited', 'obatained', 
    ];
}
