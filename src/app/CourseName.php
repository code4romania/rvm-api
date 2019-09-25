<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class CourseName extends Eloquent
{
    protected $connection = 'courses';
    protected $collection = 'course_names';
    protected $fillable = [
        'id','name', 'slug', 'accreditors', 'static_accreditor'
    ];
}

