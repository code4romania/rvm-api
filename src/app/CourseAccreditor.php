<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class CourseAccreditor extends Eloquent
{
    protected $connection = 'courses';
    protected $collection = 'course_accreditors';
    protected $fillable = [
        'name', 'courses', 
    ];
}

