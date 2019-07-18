<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Volunteer extends Eloquent
{
  protected $connection = 'volunteers';
  protected $collection = 'volunteers';
  protected $fillable = [
    'organisation', 'courses', 'name', 'ssn', 'email', 'phone', 'county', 'city', 'address', 'comments', 'job', 'added_by',
  ];

}