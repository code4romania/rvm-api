<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Volunteer extends Eloquent
{
  protected $connection = 'volunteers';
  protected $collection = 'volunteers';
  protected $fillable = [
    'name', 'slug', 'ssn', 'email', 'phone', 'county', 'city', 'address', 'organisation',
    'courses', 'allocation', 'comments', 'job', 'added_by',
  ];

}