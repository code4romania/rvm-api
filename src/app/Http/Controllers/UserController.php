<?php

namespace App\Http\Controllers;

use http\Env\Response;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * ******
     * THIS IS ONLY AN EXAMPLE CONTROLLER TO DEMONSTRATE THE USE OF SWAGGER ANNOTATIONS!
     * ******
     * @SWG\Get(
     *   path="/create",
     *   summary="Create user",
     *   operationId="createUser",
     *   @SWG\Parameter(
     *     name="firstname",
     *     in="query",
     *     description="Customer first name.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="lastname",
     *     in="query",
     *     description="Customer last name.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function create(Request $request)
    {
        $userData = $request->only([
            'firstname',
            'lastname',
        ]);

        if (empty($userData['firstname']) && empty($userData['lastname'])) {
            return new \Exception('Missing data', 404);
        }

        return $userData['firstname'] . ' ' . $userData['lastname'];
    }
}