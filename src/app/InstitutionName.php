<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

class InstitutionName extends Eloquent
{
    protected $connection = 'statics';
    protected $collection = 'institution_names';
    protected $fillable = [
        'id','name', 'slug',
    ];
}

