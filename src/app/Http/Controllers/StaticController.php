<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\City;
use App\County;
use App\DBViews\StaticCitiesBySlugAndNameView;
use App\DBViews\StaticCountiesBySlugAndNameView;


class StaticController extends Controller
{
    /**
     * Function responsible with extracting a list of cities.
     * 
     * @param object $request Contains all the needed parameter for extracting the list of cities.
     * 
     * @return object 200 and the list of cities if no error occurs
     *                500 if an error occurs
     * 
     * @SWG\Get(
     *   tags={"Statics"},
     *   path="/api/cities",
     *   summary="Return all cities",
     *   operationId="getAllCities",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function getAllCities(Request $request) {
        /** Extract the DB client. */
        $client = \DB::connection('statics')->getCouchDBClient();
        /** Create the DB query. */
        $query = $client->createViewQuery('cities', 'slug');

        $startKey = null;
        $endKey = null;

        /** Check if a start city is specified. */
        if(isset($request->filters[1])){
            /** Set the start city. */
            $startKey = array($request->filters[1]);
            /** Set the end city. */
            $endKey = array($request->filters[1], (object)[]);
        }

        /** Check if an end city is specified. */
        if(isset($request->filters[1]) && isset($request->filters[2]) && $request->filters[2]){
            /** Set the start city. */
            $startKey[1] = $request->filters[2];
            /** Set the end city. */
            $endKey[1] = $request->filters[2] . $client::COLLATION_END;
        }

        /** Set the start and end city. */
        $query->setStartKey($startKey);
        $query->setEndKey($endKey);
        /** Extract the cities. */
        $docs = $query->execute();

        /** Return a JSOn encoded map of <ID, VALUE> pairs. */
        return response()->json(array_map(function($doc){
            return array(
                "_id" => $doc['id'],
                "name" =>  $doc['value']
            );
        }, $docs->toArray()));
    }


    /**
     * Function responsible with extracting a list of counties.
     * 
     * @param object $request Contains all the needed parameter for extracting the list of counties.
     * 
     * @return object 200 and the list of counties if no error occurs
     *                500 if an error occurs
     * 
     * @SWG\Get(
     *   tags={"Statics"},
     *   path="/api/counties",
     *   summary="Return all counties",
     *   operationId="getAllCounties",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function getAllCounties(Request $request) {
        /** Extract the DB client. */
        $client = \DB::connection('statics')->getCouchDBClient();
        /** Create the DB query. */
        $query = $client->createViewQuery('counties', 'slug');

        $startKey = null;
        $endKey = null;

        /** Check if a start city is specified. */
        if(isset($request->filters[1])){
            /** Set the start city. */
            $startKey = array($request->filters[1]);
            /** Set the end city. */
            $endKey = array($request->filters[1], (object)[]);
        }

        /** Check if an end city is specified. */
        if(isset($request->filters[1]) && isset($request->filters[2]) && $request->filters[2]){
            /** Set the start city. */
            $startKey[1] = $request->filters[2];
            /** Set the end city. */
            $endKey[1] = $request->filters[2] . $client::COLLATION_END;
        }

        /** Set the start and end city. */
        $query->setStartKey($startKey);
        $query->setEndKey($endKey);
        /** Extract the cities. */
        $docs = $query->execute();

        /** Return a JSOn encoded map of <ID, VALUE> pairs. */
        return response()->json(array_map(function($doc){
            return array(
                "_id" => $doc['id'],
                "name" =>  $doc['value']
            );
        }, $docs->toArray()));
    }
}
