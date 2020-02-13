<?php
namespace App\DBViews;

use \Doctrine\CouchDB\View\DesignDocument;

class StaticCountiesBySlugAndNameView implements DesignDocument
        {
            public function getData()
            {
                return array(
                    'language' => 'javascript',
                    'views' => array(
                        'slug' => array(
                            'map' => 'function(doc) {
                                if(\'counties\' == doc.type) {
                                    emit([doc.country_id,doc.slug], doc.name);
                                }
                            }',
                        ),
                        'name' => array(
                            'map' => 'function(doc) {
                                if(\'counties\' == doc.type) {
                                    emit([doc.country_id,doc.name], doc.name);
                                }
                            }',
                        ),
                    ),
                );
            }
        }