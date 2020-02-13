<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Organisation extends Eloquent
{
  protected $connection = 'organisations';
  protected $collection = 'organisations';

  protected $fillable = [
    'name', 'website', 'contact_person', 'address', 'county', 'city', 'comments', 'added_by', 'cover'
  ];

}