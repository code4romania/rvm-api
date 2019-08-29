<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Resource;

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
            '1' => array( 'type_name', 'ilike' ),
            '2' => array( 'county', 'ilike' ),
            '3' => array( 'organisation.name', 'ilike')
        ));

        applySort($resources, $params, array(
            '1' => 'name',
            '2' => 'type_name',
            '3' => 'quantity',
            '4' => 'organisation', //change to nr_org
        ));

        $pager = applyPaginate($resources, $params);

        return response()->json(array(
            "pager" => $pager,
            "data" => $resources->get()
        ), 200); 
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

    public function show($id)
    {
        $resources = Resource::find($id);

        if(empty($resources)) {
            return response()->json(404);
        }

        return response()->json($resources, 200);
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
     *     name="type_name",
     *     in="query",
     *     description="Resource type_name.",
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
       // 'category', 'subcategory' required
       // dimensions - not required 
        // daca numele ii la fel -> fac update 
        //added_by
        //verificat comments
        //adresa

        $data = $request->all();
        $rules = [
            'organisation_id' => 'required',
            'name' => 'required|string|max:255',
            'type_name' => 'required|string',
            'quantity' => 'required|integer',
            'county' => 'required|min:4|',
            'city' => 'required|min:4|'
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 400);
        }

        $data = convertData($validator->validated(), $rules);
        $organisation_id = $request->organisation_id;

        $organisation = \DB::connection('organisations')->collection('organisations')
            ->where('_id', '=', $organisation_id)
            ->get(['_id', 'name', 'slug', 'website'])
            ->first();

        if(\Auth::check()) {
            $data['added_by'] = \Auth::user()->_id;
        }

        $data['organisation'] = $organisation;
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


    // public function listGroupByType()
    // {
    //     $items = [];
    //     $resOrgsIds = [];

    //     $resources = Resource::all();
    //     $resType = $resources->groupBy(['type_name']);


    //     foreach($resType as $key => $resource) {
    //         foreach($resource as $item) {
    //             $resOrgsIds[$key][$item->name]['organisation_ids'][] = $item->organisation['_id'];
    //             if(isset($items[$key]) && array_key_exists($item->name,$items[$key])) {
    //                 $items[$key][$item->name]['quantity'] +=  (int)$item->quantity;
    //                 $items[$key][$item->name]['organisations_nr'] = count(array_unique($resOrgsIds[$key][$item->name]['organisation_ids']));
    //             } else {
    //                 $items[$key][$item->name]['quantity']  =  (int)$item->quantity;
    //                 $items[$key][$item->name]['organisations_nr'] =  1;
    //             }
    //         }
    //     }
    //     //$paginare = Resource::simplePaginate();
    //     //var_Dump($paginare);

    //     return response()->json($items, 201);
    // }

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
                    $items[$key]['type_name'] = $item->type_name;
                    $items[$key]['quantity'] +=  (int)$item->quantity;
                    $items[$key]['organisations_nr'] = count(array_unique($resOrgsIds[$key]['organisation_ids']));
                } else {
                    $items[$key]['type_name'] = $item->type_name;
                    $items[$key]['quantity']  =  (int)$item->quantity;
                    $items[$key]['organisations_nr'] =  1;
                }
            }
        }
       // $items = rvmPaginate($items);

        return response()->json($items, 200);
    }
}
