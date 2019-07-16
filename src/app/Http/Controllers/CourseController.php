<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Course;

class CourseController extends Controller
{
        /**
     * @SWG\Get(
     *   tags={"Courses"},
     *   path="/api/courses",
     *   summary="Return all courses",
     *   operationId="index",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function index()
    {
        return Course::all();
    }

     /**
     * @SWG\Get(
     *   tags={"Courses"},
     *   path="/api/courses/{id}",
     *   summary="Show course info ",
     *   operationId="show",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function show($id)
    {
        return Course::find($id);
    }

    /**
     * @SWG\Post(
     *   tags={"Courses"},
     *   path="/api/courses",
     *   summary="Create course",
     *   operationId="store",
     *   @SWG\Parameter(
     *     name="volunteer_id",
     *     in="query",
     *     description="Course volunteer id.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Course name.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="ssn",
     *     in="query",
     *     description="Course acredited.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="Course obatained.",
     *     required=true,
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
            'volunteer_id' => 'required|string',
            'name' => 'required|string|max:255',
            'acredited' => 'required|string',
            'obatained' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response(['errors'=>$validator->errors()->all()], 400);
        }
        
        $course = Course::create($request->all());
        return response()->json($course, 201); 
    }

    /**
     * @SWG\put(
     *   tags={"Courses"},
     *   path="/api/courses/{id}",
     *   summary="Update course",
     *   operationId="update",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $course->update($request->all());

        return $course;
    }

    /**
     * @SWG\Delete(
     *   tags={"Courses"},
     *   path="/api/courses/{id}",
     *   summary="Delete course",
     *   operationId="delete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function delete(Request $request, $id)
    {
        $course = Course::findOrFail($id);
        $course->delete();

        $response = array("message" => 'Course deleted.');

        return response()->json($response, 200);
    }
}
