<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Resource extends Eloquent {
    protected $collection = 'resources_coll';
  
    protected $fillable = [
        'organisation_id', 'name', 'type', 'quantity', 'county', 'city', 'address', 'comments', 'added_by',
      ];
  
  }

