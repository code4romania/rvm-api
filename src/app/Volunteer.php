<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Volunteer extends Eloquent
{
    protected $collection = 'volunteers_coll';
    protected $fillable = [
        'organisation_id', 'name', 'ssn', 'email', 'phone',
        'county', 'city', 'address', 'comments', 'job', 'added_by',
    ];

}