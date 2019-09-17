<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Mail\SetUpPassword;
use App\User;
use App\PasswordReset;
use App\Institution;
use App\Organisation;
use App\Volunteer;

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
        if(isRole('institution')) {
            $users->where('role', '=', '0')
                  ->where('institution._id', '=', getAffiliationId());
        }
        applyFilters($users, $params, array(
            '0' => array( 'institution._id', 'ilike'),
            '1' => array( 'name', 'ilike' ),
        ));
        applySort($users, $params, array(
            '1' => 'name',
            '2' => 'role',
            '3' => 'institution.name'
        ));

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
        $user = User::findOrFail($id);
        allowResourceAccess($user);

        if(isset($user->organisation['_id'])){
            $user->organisation = Organisation::find($user->organisation['_id']);
        }

        if(isset($user->institution['_id'])){
            $user->institution = Institution::find($user->institution['_id']);
        }
        return response()->json($user, 200);
    }

    /**
     * @SWG\Post(
     *   tags={"Users"},
     *   path="/api/users",
     *   summary="Create volunteer",
     *   operationId="store",
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Customer name.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="Customer email.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="phone",
     *     in="query",
     *     description="Customer Phone.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="role",
     *     in="query",
     *     description="Customer Role.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="institution",
     *     in="query",
     *     description="Customer Institution.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="organisation",
     *     in="query",
     *     description="Customer Organisation.",
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users.users',
            'role' => 'required',
            'phone' => 'required|string|min:6|',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 400);
        }
        $data = convertData($validator->validated(), $rules);
        if(!isRole('dsu')){
            if(isset($data['institution'])) {
                unset($data['institution']);
            }
            if(isset($data['organisation'])){
                unset($data['organisation']);
            }
        } else {
            $request->has('institution') ? $institution = Institution::findOrFail($request->institution) : '';
            if(isset($institution)) {
                $data['institution'] = [
                    '_id' => $institution->_id,
                    'name' => $institution->name
                ];
            }
            $request->has('organisation') ? $organisation = Organisation::findOrFail($request->organisation) : '';
            if(isset($organisation)) {
                $data['organisation'] = [
                    '_id' => $organisation->_id,
                    'name' => $organisation->name
                ]; 
            }
        }
        $data = setAffiliate($data);
        if(\Auth::check()) {
           $data['added_by'] = \Auth::user()->_id;
        }
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $data['email']],
            [
                'email' => $data['email'],
                'token' => str_random(60)
            ]
        );
        $url = env('FRONT_END_URL') . '/auth/reset/'.$passwordReset->token;
        $set_password_data = array(
            'url' => $url
        );
        Mail::to($data['email'])->send(new SetUpPassword($set_password_data));
        $data['password'] = Hash::make(str_random(16));
        $user = User::create($data);
        $response = array(
            "message" => 'Password sent to email.',
            "user" => $user
        );
        return response()->json($response, 201); 
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
        allowResourceAccess($user);
        $user = setAffiliate($user);
        $data = $request->all();
        if(isset($data['institution']) && $data['institution']) {
            $institution = Institution::findOrFail($data['institution']);
            $data['institution'] = [
                '_id' => $institution->_id,
                'name' => $institution->name
            ];
        }
        if(isset($data['organisation']) && $data['organisation']) {
            $organisation = Organisation::findOrFail($data['organisation']);
            $data['organisation'] = [
                '_id' => $organisation->_id,
                'name' => $organisation->name
            ];
        }
        $user->update($data);

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
        allowResourceAccess($user);
        if(!isRole('dsu') && getAffiliationId($id) != \Auth::user()->institution['_id']){
           isDenied();
        }

        $user->delete();
        $response = array("message" => 'User deleted.');

        return response()->json($response, 200);
    }
}
