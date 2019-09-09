<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\City;
use App\County;
use App\DBViews\StaticCitiesBySlugView;
use App\DBViews\StaticCitiesByNameView;

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

        $client->createDesignDocument('cities', new StaticCitiesBySlugView());
        $client->createDesignDocument('cities', new StaticCitiesByNameView());

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

        //TEMPORARY
        return response()->json($docs->toArray());
       

        /*
        ** OLD QUERY

        $cities = applyFilters($cities, $params, array(
            '1' => array( 'county_id', '=' ),
            '2' => array( 'slug', 'ilike' )
        ));

        $cities = applySort($cities, $params, array(
            '1' => 'name'
        ));

        $pager = applyPaginate($cities, $params);

        return response()->json(array(
            "pager" => $pager,
            "data" => $cities->get(['name','_id'])
        ), 200); */
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

        applyFilters($counties, $params, array(
            '1' => array( 'slug', 'ilike' ),
            '2' => array( 'country_id', '=' ),
        ));

        applySort($counties, $params, array(
            '1' => 'name',
        ));

        $pager = applyPaginate($counties, $params);

        return response()->json(array(
            "pager" => $pager,
            "data" => $counties->get()
        ), 200); 
    }

}
