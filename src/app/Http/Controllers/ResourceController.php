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
    public function index()
    {
        //$resources = Resource::simplePaginate();
        return response()->json(Resource::all(), 200); 
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
        return Resource::find($id);
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
        $validator = Validator::make($request->all(), [
            'organisation_id' => 'required',
            'name' => 'required|string|max:255',
            'type_name' => 'required|string',
            'quantity' => 'required|string',
            'county' => 'required|string|min:4|',
            'city' => 'required|string|min:4|'
        ]);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 400);
        }
        
        $organisation_id = $request->organisation_id;
        $organisation = \DB::connection('organisations')->collection('organisations')
            ->where('_id', '=', $organisation_id)
            ->get(['_id', 'name', 'website'])
            ->first();

        $request->request->add(['organisation' => $organisation]);
        $resource = Resource::create($request->all());

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
        $resource->update($request->all());

        return $resource;
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

    public function list()
    {
        $items = [];
        $resOrgsIds = [];

        $resources = Resource::all();
        $resType = $resources->groupBy(['type_name']);

        foreach($resType as $key => $resource) {
            foreach($resource as $item) {
                $resOrgsIds[$key][$item->name]['organisation_ids'][] = $item->organisation['_id'];
                if(isset($items[$key]) && array_key_exists($item->name,$items[$key])) {
                    $items[$key][$item->name]['quantity'] +=  (int)$item->quantity;
                    $items[$key][$item->name]['organisations_nr'] = count(array_unique($resOrgsIds[$key][$item->name]['organisation_ids']));
                } else {
                    $items[$key][$item->name]['quantity']  =  (int)$item->quantity;
                    $items[$key][$item->name]['organisations_nr'] =  1;
                }
            }
        }
        //$paginare = Resource::simplePaginate();
        //var_Dump($paginare);

        return response()->json($items, 201);
    }
}
