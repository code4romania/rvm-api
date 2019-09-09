<?php
namespace App\DBViews;

use \Doctrine\CouchDB\View\DesignDocument;

class StaticCitiesByNameView implements DesignDocument
        {
            public function getData()
            {
                return array(
                    'language' => 'javascript',
                    'views' => array(
                        'name' => array(
                            'map' => 'function(doc) {
                                if(\'cities\' == doc.type) {
                                    emit([doc.county_id,doc.name], doc.name);
                                }
                            }',
                        ),
                    ),
                );
            }
        }