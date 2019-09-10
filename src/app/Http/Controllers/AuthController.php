<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendRecoverPasswordMail;
use App\Mail\PasswordChanged;
use App\User;
use App\PasswordReset;

class AuthController extends Controller
{
    /**
     * @SWG\Post(
     *   tags={"Auth"},
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
     *  @SWG\Parameter(
     *     name="role",
     *     in="query",
     *     description="Customer role.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="phone",
     *     in="query",
     *     description="Customer phone.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    // public function register (Request $request) {

    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users.users',
    //         'password' => 'required|string|min:6|confirmed',
    //         'role' => 'required',
    //         'phone' => 'required|string|min:6|'
    //     ]);
    
    //     if ($validator->fails())
    //     {
    //         return response(['errors'=>$validator->errors()->all()], 422);
    //     }
    
    //     $request['password']=Hash::make($request['password']);
    //     $user = User::create($request->toArray());
    
    //     $token = $user->createToken('Laravel Password Grant Client')->accessToken;
    //     $response = ['token' => $token];
    
    //     return response($response, 200);
    
    // }


    /**
     * @SWG\Post(
     *   tags={"Auth"},
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
        $user = User::where('email', $request->email)
            ->orWhere('phone', $request->phone)
            ->first();

        if (($user && $user->role!= 0 && (is_null($request->device)||empty($request->device)||$request->device!='mobile')) || ($request->has('device')=="mobile" && $user->role == 0)) {
            if (Hash::check($request->password, $user->password)) {
                
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token,
                    'user' => $user]; 
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
     *   tags={"Auth"},
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
     *   tags={"Auth"},
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

    /**
     * @SWG\Post(
     *   tags={"Auth"},
     *   path="/api/recoverpassword",
     *   summary="Recover Password, Send Reset Password Mail",
     *   operationId="recoverpassword",
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="Emaill address.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function recoverpassword(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);
        $user = User::whereEmail($request->email)->first();
        if ($user) {
            $passwordReset = PasswordReset::updateOrCreate(
                ['email' => $user->email],
                [
                    'email' => $user->email,
                    'token' => str_random(60)
                ]
            );
            $url = url('/auth/reset/'.$passwordReset->token);
            $url = str_replace('-api','',$url);
            $data = array(
                'url' => $url
            );
            $url = str_replace('/api','',$url);
            Mail::to($user->email)->send(new SendRecoverPasswordMail($data));

            return response()->json([
                'message' => 'Mail was sent succesfully.'
            ], 200);
        } else {
            $response = "We can't find a user with that e-mail address.";
            return response($response, 422);
        }
    } 

    /**
     * @SWG\Post(
     *   tags={"Auth"},
     *   path="/api/resetpassword",
     *   summary="Reset the user password",
     *   operationId="resetpassword",
     *   @SWG\Parameter(
     *     name="password",
     *     in="query",
     *     description="Password",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="passtoken",
     *     in="query",
     *     description="Password Token",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function resetpassword(Request $request) 
    {
        $request->validate([
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);

        $resetToken = PasswordReset::where('token', $request->token)->first();

        if (!$resetToken)
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);

        if (Carbon::parse($resetToken->updated_at)->addMinutes(2880)->isPast()) {
            $resetToken->delete();
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }

        $user = User::whereEmail($resetToken->email)->first();

        if (!$user)
            return response()->json([
                'message' => "We can't find a user with that e-mail address."
            ], 404);

        $user['password']=Hash::make($request['password']);

        $user->save();

        $resetToken->delete();

        // Mail::to($user->email)->send(new PasswordChanged);

        return response()->json([
            'message' => 'Password changed succesfully.'
        ], 200);
    } 

}
