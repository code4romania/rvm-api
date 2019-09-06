<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Resource;
use App\Organisation;
use App\ResourceCategory;
use App\City;
use App\County;

class ResourceController extends Controller
{
        /**
     * @SWG\Get(
     *   tags={"Resources"},
     *   path="/api/resources",
     *   summary="Return all resources",
     *   operationId="index",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function index(Request $request)
    {
        $params = $request->query();
        $resources = Resource::query();

        applyFilters($resources, $params, array(
            '0' => array( 'categories', 'elemmatch', '_id', 'ilike' ),
            '1' => array( 'county._id', 'ilike' )
        ));

        applySort($resources, $params, array(
            '1' => 'name',
            '2' => 'resource_type',
            '3' => 'quantity',
            '4' => 'organisation',
        ));

        $resources = $resources->get()->groupBy('slug');

        $resources = applyCollectionPaginate($resources, $params);

        foreach($resources['data'] as $key => &$resource){
            $res = $resource->toArray();
            $categories = array_filter( $resource->pluck('categories')->toArray());
            $organisations = $resource->unique('organisation._id')->pluck('organisation');

            $resources['data'][$key] = array(
                "slug" => $key,
                "name" => $res[0]['name'],
                "resources" => $res,
                "quantity" => $resource->sum('quantity'),
                "organisations_total" => $organisations->count(),
                "organisations" => $organisations,
                "categories" =>  $categories ? array_values( array_unique( call_user_func_array('array_merge',$categories) , SORT_REGULAR) ) : array()
            );
        }

        $resources['data'] = array_values($resources['data']->toArray());

        return response()->json($resources, 200); 
    }

     /**
     * @SWG\Get(
     *   tags={"Resources"},
     *   path="/api/resources/{id}",
     *   summary="Show resource info ",
     *   operationId="show",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function show(Request $request, $id)
    {   
        $params =  $request->query();
        $resources = Resource::query();
        applySort($resources, $params, array(
            '1' => 'organisation.name',
            '2' => 'quantity',
            '3' => 'address',
            '4' => 'county_id',
            '5' => 'updated_at'
        ));
        $pager = applyPaginate($resources,$params);
        $resources = $resources->where('slug', '=', $id)->get();
        foreach ($resources as $resource) {
            $resource->organisation = Organisation::query()->where('_id', '=', $resource->organisation['_id'])->get();
        }
        if(empty($resources)) {
            return response()->json(404);
        }

        return response()->json(array(
            "pager" => $pager,
            "data" => $resources
        ), 200);
    }

    /**
     * @SWG\Post(
     *   tags={"Resources"},
     *   path="/api/resources",
     *   summary="Create resource",
     *   operationId="store",
     *   @SWG\Parameter(
     *     name="organisation_id",
     *     in="query",
     *     description="Resource organisation id.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Resource name.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="resource_type",
     *     in="query",
     *     description="Resource resource_type.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="quantity",
     *     in="query",
     *     description="Resource quantity.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="county",
     *     in="query",
     *     description="Resource county.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="city",
     *     in="query",
     *     description="Resource city.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="address",
     *     in="query",
     *     description="Resource address.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="comments",
     *     in="query",
     *     description="Resource comments.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="added_by",
     *     in="query",
     *     description="Resource added by.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function store(Request $request)
    {
        $data = $request->all();
        $rules = [
            'organisation_id' => 'required',
            'name' => 'required|string|max:255',
            'resource_type' => 'required|string',
            'quantity' => 'required|integer',
            'county' => 'required',
            'city' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 400);
        }
        $data = convertData($validator->validated(), $rules);
        
        if($request->has('categories'))
        {
            $data['categories'] = array();
            foreach ($request->categories as $key =>$val) {
                $resCat = ResourceCategory::query()
                    ->where('_id', '=', $val)
                    ->get(['_id', 'name', 'slug'])
                    ->first();

                $data['categories'][$key] = array(
                    '_id' => $resCat->_id,
                    'name' => $resCat->name,
                    'slug' => $resCat->slug
                );
            }
        } 

        $request->has('unit') ? $data['unit'] = $request->unit : '';
        $request->has('size') ? $data['size'] = $request->size : '';
        $request->has('comments') ? $data['comments'] = $request->comments : '';
        $request->has('address') ? $data['address'] = $request->address : '';

        //Add City and County
        if ($request->has('county')) {
            $data['county'] = getCityOrCounty($request->county,County::query());
        }
        if ($request->has('city')) {            
            $data['city'] = getCityOrCounty($request->city,City::query());
        }

        //Add Organisation
        $organisation_id = $request->organisation_id;
        $organisation = \DB::connection('organisations')->collection('organisations')
            ->where('_id', '=', $organisation_id)
            ->get(['_id', 'name', 'slug', 'website'])
            ->first();
        $data['organisation'] = $organisation;

        //Added by
        \Auth::check() ? $data['added_by'] = \Auth::user()->_id : '';

        $resource = Resource::create($data);
        return response()->json($resource, 201); 
    }

    /**
     * @SWG\put(
     *   tags={"Resources"},
     *   path="/api/resources/{id}",
     *   summary="Update resource",
     *   operationId="update",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function update(Request $request, $id)
    {
        $resource = Resource::findOrFail($id);

        if($request->has('organisation_id')) {
            $organisation_id = $request->organisation_id;
            $organisation = \DB::connection('organisations')->collection('organisations')
                ->where('_id', '=', $organisation_id)
                ->get(['_id', 'name', 'website', 'address', 'county'])
                ->first();

            $request->request->add(['organisation' => $organisation]);
        }

        $resource->update($request->all());
 
        return response()->json($resource, 200); 
    }

    /**
     * @SWG\Delete(
     *   tags={"Resources"},
     *   path="/api/resources/{id}",
     *   summary="Delete resource",
     *   operationId="delete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function delete(Request $request, $id)
    {
        $resource = Resource::findOrFail($id);
        $resource->delete();

        $response = array("message" => 'Resource deleted.');

        return response()->json($response, 200);
    }


    /**
     * @SWG\Get(
     *   tags={"Resources"},
     *   path="/api/resources/list",
     *   summary="List resources",
     *   operationId="delete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    
    public function list(Request $request)
    {
        $items = [];
        $resOrgsIds = [];

        $resources = Resource::all();
        $resType = $resources->groupBy(['name']);

        foreach($resType as $key => $resource) {
            foreach($resource as $item) {
                $resOrgsIds[$key]['organisation_ids'][] = $item->organisation['_id'];
                if(isset($items[$key]) && array_key_exists($key, $items)) {
                    $items[$key]['resource_type'] = $item->resource_type;
                    $items[$key]['quantity'] +=  (int)$item->quantity;
                    $items[$key]['organisations_nr'] = count(array_unique($resOrgsIds[$key]['organisation_ids']));
                } else {
                    $items[$key]['resource_type'] = $item->resource_type;
                    $items[$key]['quantity']  =  (int)$item->quantity;
                    $items[$key]['organisations_nr'] =  1;
                }
            }
        }
       // $items = rvmPaginate($items);

        return response()->json($items, 200);
    }
    /**
     * @SWG\Get(
     *   tags={"Resources"},
     *   path="/api/resources/categories",
     *   summary="List resource categories",
     *   operationId="delete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function getAllResourceCategories(Request $request)
    {
        $params = $request->query();
        $resources = ResourceCategory::query();

        applyFilters($resources, $params, array(
            '1' => array( 'slug', 'ilike' ),
            '2' => array( 'parent_id', '=' )
        ));

        return response()->json(array(
            "data" => $resources->get(['_id', 'parent_id', 'name', 'slug'])
        ), 200); 
    }

    public function getResourceCategory($id) {
        $resourceCat = ResourceCategory::find($id);

        if(empty($resourceCat)) {
            return response()->json(404);
        }

        return response()->json($resourceCat, 200);

    }
}
