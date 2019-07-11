<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * @SWG\Post(
     *   path="/api/register",
     *   summary="Register user",
     *   operationId="register",
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
     *  @SWG\Parameter(
     *     name="password",
     *     in="query",
     *     description="Customer password.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="password_confirmation",
     *     in="query",
     *     description="Customer confirmation password.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function register (Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    
        if ($validator->fails())
        {
            return response(['errors'=>$validator->errors()->all()], 422);
        }
    
        $request['password']=Hash::make($request['password']);
        $user = User::create($request->toArray());
    
        $token = $user->createToken('Laravel Password Grant Client')->accessToken;
        $response = ['token' => $token];
    
        return response($response, 200);
    
    }


    /**
     * @SWG\Post(
     *   path="/api/login",
     *   summary="User login",
     *   operationId="login",
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="Emaill address.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="password",
     *     in="query",
     *     description="Password",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */


    public function login (Request $request) {

        $user = User::where('email', $request->email)->first();

        if ($user) {
            
            if (Hash::check($request->password, $user->password)) {
                
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token];
                return response($response, 200);
            } else {
                $response = "Password missmatch";
                return response($response, 422);
            }
    
        } else {
            $response = 'User does not exist';
            return response($response, 422);
        }
    
    }


    /**
     * @SWG\Get(
     *   path="/api/logout",
     *   summary="User logout",
     *   operationId="logout",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */


    public function logout (Request $request) {

        $token = $request->user()->token();
        $token->revoke();
    
        $response = array("message" => 'You have been succesfully logged out!');
        return response()->json($response, 200);
    
    }


    /**
     * @SWG\Get(
     *   path="/api/profile",
     *   summary="Get user profile",
     *   operationId="profile",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function profile() 
    { 
        $user = Auth::user(); 
        return response()->json($user, 200); 
    } 
}
