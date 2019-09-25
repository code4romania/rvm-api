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
    public function getAllCities(Request $request)
    {
        $params = $request->query();
        $cities = City::query();

        $client = \DB::connection('statics')->getCouchDBClient();

        $query = $client->createViewQuery('cities', 'slug');

        $startKey = null;
        $endKey = null;

        if(isset($request->filters[1])){
            $startKey = array($request->filters[1]);
            $endKey = array($request->filters[1], (object)[]);
        }

        if(isset($request->filters[1]) && isset($request->filters[2]) && $request->filters[2]){
            $startKey[1] = $request->filters[2];
        }

        if(isset($request->filters[1]) && isset($request->filters[2]) && $request->filters[2]){
            $endKey[1] = $request->filters[2].$client::COLLATION_END;
        }

        $query->setStartKey($startKey);
        $query->setEndKey($endKey);
        $docs = $query->execute();

        return response()->json(array_map(function($doc){
            return array(
                "_id" => $doc['id'],
                "name" =>  $doc['value']
            );
        }, $docs->toArray()));
    }

    /**
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
    public function getAllCounties(Request $request)
    {
        $params = $request->query();
        $counties = County::query();

        $client = \DB::connection('statics')->getCouchDBClient();

        $query = $client->createViewQuery('counties', 'slug');

        $startKey = null;
        $endKey = null;

        if(isset($request->filters[1])){
            $startKey = array($request->filters[1]);
            $endKey = array($request->filters[1], (object)[]);
        }

        if(isset($request->filters[1]) && isset($request->filters[2]) && $request->filters[2]){
            $startKey[1] = $request->filters[2];
        }

        if(isset($request->filters[1]) && isset($request->filters[2]) && $request->filters[2]){
            $endKey[1] = $request->filters[2].$client::COLLATION_END;
        }

        $query->setStartKey($startKey);
        $query->setEndKey($endKey);
        $docs = $query->execute();

        return response()->json(array_map(function($doc){
            return array(
                "_id" => $doc['id'],
                "name" =>  $doc['value']
            );
        }, $docs->toArray()));
    
    }

}
