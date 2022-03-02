<?php

namespace App;

use Illuminate\Support\Str;
use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class Organisation extends Eloquent
{
  protected $connection = 'organisations';
  protected $collection = 'organisations';

  protected $fillable = [
    'name', 'website', 'contact_person', 'address', 'county', 'city', 'comments', 'added_by', 'cover', 'slug'
  ];

  public function setNameAttribute($value){
      $this->attributes['name'] = $value;
      $this->attributes['slug'] = Str::slug($value);
  }

}