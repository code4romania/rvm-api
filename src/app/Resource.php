<?php

namespace App;

use Robsonvn\CouchDB\Eloquent\Model as Eloquent;

/**
 * Resource Model
 */
class Resource extends Eloquent
{
    /**
     * DB Connection
     *
     * @var string $connection
     */
    protected $connection = 'resources';

    /**
     * @var string $collection
     */
    protected $collection = 'resources';

    /**
     * Fillable Fields
     * @var string[] $fillable
     */
    protected $fillable = [
        'name', 'resource_type', 'slug', 'categories', 'quantity', 'unit', 'size', 'comments',
        'county', 'city', 'address', 'organisation', 'added_by', 'tags'
    ];

    /**
     * Cast data types properties
     * 
     * @var string[] $casts
     */
    protected $casts = [
        'quantity' => 'integer',
    ];
}

