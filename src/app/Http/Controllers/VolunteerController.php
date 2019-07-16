<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Volunteer;

class VolunteerController extends Controller
{
        /**
     * @SWG\Get(
     *   tags={"Volunteers"},
     *   path="/api/volunteers",
     *   summary="Return all volunteers",
     *   operationId="index",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function index()
    {
        return Volunteer::all();
    }

     /**
     * @SWG\Get(
     *   tags={"Volunteers"},
     *   path="/api/volunteers/{id}",
     *   summary="Show volunteer info ",
     *   operationId="show",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function show($id)
    {
        return Volunteer::find($id);
    }

    /**
     * @SWG\Post(
     *   tags={"Volunteers"},
     *   path="/api/volunteers",
     *   summary="Create volunteer",
     *   operationId="store",
     *   @SWG\Parameter(
     *     name="organisation_id",
     *     in="query",
     *     description="Volunteer organisation id.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Volunteer name.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="ssn",
     *     in="query",
     *     description="Volunteer ssn.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="Volunteer email.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="phone",
     *     in="query",
     *     description="Volunteer phone.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="county",
     *     in="query",
     *     description="Volunteer county.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="city",
     *     in="query",
     *     description="Volunteer city.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="address",
     *     in="query",
     *     description="Volunteer address.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="comments",
     *     in="query",
     *     description="Volunteer comments.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="job",
     *     in="query",
     *     description="Volunteer job.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="added_by",
     *     in="query",
     *     description="Volunteer added by.",
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
            'name' => 'required|string|max:255',
            'ssn' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|min:6|',
            'county' => 'required|string|min:4|',
            'city' => 'required|string|min:4|'
        ]);

        if ($validator->fails()) {
            return response(['errors'=>$validator->errors()->all()], 400);
        }
        
        $volunteer = Volunteer::create($request->all());
        return response()->json($volunteer, 201); 
    }

    /**
     * @SWG\put(
     *   tags={"Volunteers"},
     *   path="/api/volunteers/{id}",
     *   summary="Update volunteer",
     *   operationId="update",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function update(Request $request, $id)
    {
        $volunteer = Volunteer::findOrFail($id);
        $volunteer->update($request->all());

        return $volunteer;
    }

    /**
     * @SWG\Delete(
     *   tags={"Volunteers"},
     *   path="/api/volunteers/{id}",
     *   summary="Delete volunteer",
     *   operationId="delete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function delete(Request $request, $id)
    {
        $volunteer = Volunteer::findOrFail($id);
        $volunteer->delete();

        $response = array("message" => 'Volunteer deleted.');

        return response()->json($response, 200);
    }
}
