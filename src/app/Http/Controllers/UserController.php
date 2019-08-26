<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\User;

class UserController extends Controller
{
    /**
     * @SWG\Get(
     *   tags={"Users"},
     *   path="/api/users",
     *   summary="Return all users",
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
        $users = User::query();
        $pager = applyPaginate($users, $params);

        return response()->json(array(
            "pager" => $pager,
            "data" => $users->get()
        ), 200); 
    }

     /**
     * @SWG\Get(
     *   tags={"Users"},
     *   path="/api/users/{id}",
     *   summary="Show user info ",
     *   operationId="show",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function show($id)
    {
        return response()->json(User::findOrFail($id), 200);
    }

    /**
     * @SWG\Post(
     *   tags={"Users"},
     *   path="/api/users",
     *   summary="Create volunteer",
     *   operationId="store",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function store(Request $request)
    {
        //generate apssword
        //send to mail 
        //manage users

        $data = $request->all();
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users.users',
            'role' => 'required',
            'phone' => 'required|string|min:6|'
            //'organisation_id' => 'required',
            //'county' => 'required|string|min:4|',
            //'city' => 'required|string|min:4|',
            //'password' => 'required|string|min:6|confirmed',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 400);
        }

        $data = convertData($validator->validated(), $rules);
        $request->has('admin_at') ? $data['admin_at'] = $request->admin_at : '';
        $data['password'] = bcrypt('test1234'); //should change with Email change pass;

        $user = User::create($data);

        return response()->json($user, 201); 
    }

    /**
     * @SWG\put(
     *   tags={"Users"},
     *   path="/api/users/{id}",
     *   summary="Update user",
     *   operationId="update",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->all());

        return response()->json($user, 201); 
    }

    /**
     * @SWG\Delete(
     *   tags={"Users"},
     *   path="/api/users/{id}",
     *   summary="Delete user",
     *   operationId="delete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function delete(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        $response = array("message" => 'User deleted.');

        return response()->json($response, 200);
    }
}
