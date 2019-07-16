<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Organisation extends Eloquent {
  protected $collection = 'organisations_coll';

  protected $fillable = [
    'name', 'website', 'contact_person', 'email', 'phone', 'county', 'city', 'address', 'comments',
    ];

}