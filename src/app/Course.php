<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Course extends Eloquent
{
    protected $connection = 'courses';
    protected $collection = 'courses';
    protected $fillable = [
        'volunteer_id', 'name', 'acredited', 'obtained', 
    ];

}
