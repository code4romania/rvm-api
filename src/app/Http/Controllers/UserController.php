<?php

namespace App\Http\Controllers;

use App\Institution;
use App\Mail\SetUpPassword;
use App\Organisation;
use App\Parser\CsvParser;
use App\PasswordReset;
use App\Services\User\Import;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Function responsible of processing get all users requests.
     *
     * @param object $request Contains all the data needed for extracting the users list.
     *
     * @return object 200 and the list of users if successful
     *                500 if an error occurs
     *
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

        if (isRole('institution')) {
            $users->where('role', '=', '0')->where('institution._id', '=', getAffiliationId());
        }
        applyFilters($users, $params, ['0' => ['institution._id', 'ilike'], '1' => ['name', 'ilike'],]);
        applySort($users, $params, ['1' => 'name', '2' => 'role', '3' => 'institution.name']);

        $pager = applyPaginate($users, $params);

        return response()->json(["pager" => $pager, "data" => $users->get()], 200);
    }


    /**
     * Function responsible of extracting a user details requests.
     *
     * @param object $request Contains all the data needed for extracting the user details.
     *
     * @return object 200 and the JSON encoded user details if successful
     *                500 if an error occurs
     *
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

        if (isset($user->organisation['_id'])) {
            $user->organisation = Organisation::find($user->organisation['_id']);
        }

        if (isset($user->institution['_id'])) {
            $user->institution = Institution::find($user->institution['_id']);
        }

        return response()->json($user, 200);
    }


    /**
     * Function responsible of processing user creation requests.
     *
     * @param object $request Contains all the data needed for creating a new user.
     *
     * @return object 201 and the JSON encoded new user details if successful
     *                400 if validation fails
     *                500 if an error occurs
     *
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
     *   @SWG\Response(response=201, description="successful operation"),
     *   @SWG\Response(response=400, description="validation fails"),
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
        if (!isRole('dsu')) {
            if (isset($data['institution'])) {
                unset($data['institution']);
            }

            if (isset($data['organisation'])) {
                unset($data['organisation']);
            }
        } else {
            $request->has('institution') ? $institution = Institution::findOrFail($request->institution) : '';
            if (isset($institution)) {
                $data['institution'] = ['_id' => $institution->_id, 'name' => $institution->name];
            }

            $request->has('organisation') ? $organisation = Organisation::findOrFail($request->organisation) : '';
            if (isset($organisation)) {
                $data['organisation'] = ['_id' => $organisation->_id, 'name' => $organisation->name];
            }
        }
        $data = setAffiliate($data);
        if (\Auth::check()) {
            $data['added_by'] = \Auth::user()->_id;
        }

        /** Generate a password reset. */
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $data['email']],
            ['email' => $data['email'], 'token' => str_random(60)]
        );
        /** Create the pass reset URL. */
        $url = env('FRONT_END_URL').'/auth/reset/'.$passwordReset->token;
        $set_password_data = ['url' => $url];
        /** Send welcoming email. */
        Mail::to($data['email'])->send(new SetUpPassword($set_password_data));

        /** Generate a radom password. */
        $data['password'] = Hash::make(str_random(16));
        /** Create the new user. */
        $user = User::create($data);

        /** Chech if the user is ngo-admin. */
        if ($user['role'] == 2) {
            /** Extract the organization and update the contact person. */
            $organisation = Organisation::query()->where('_id', '=', $user['organisation._id'])->first();
            $organisation->contact_person = (object)[
                '_id' => $user['_id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'],
            ];
            $organisation->save();
        }
        $response = ["message" => 'Password sent to email.', "user" => $user];

        return response()->json($response, 201);
    }


    /**
     * Function responsible of processing user update requests.
     *
     * @param object $request Contains all the data needed for updating a user.
     * @param string $id The ID of the user to be updated.
     *
     * @return object 201 and the JSON encoded user details if successful
     *                400 if validation fails
     *                500 if an error occurs
     *
     * @SWG\put(
     *   tags={"Users"},
     *   path="/api/users/{id}",
     *   summary="Update user",
     *   operationId="update",
     *   @SWG\Response(response=201, description="successful operation"),
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

        if (isset($data['institution']) && $data['institution']) {
            $institution = Institution::findOrFail($data['institution']);
            $data['institution'] = ['_id' => $institution->_id, 'name' => $institution->name];
        }

        if (isset($data['organisation']) && $data['organisation']) {
            $organisation = Organisation::findOrFail($data['organisation']);
            $data['organisation'] = ['_id' => $organisation->_id, 'name' => $organisation->name];
        }
        $user->update($data);

        return response()->json($user, 201);
    }


    /**
     * Function responsible of processing delete user requests.
     *
     * @param object $request Contains all the data needed for deleting a user.
     * @param string $id The ID of the user to be deleted.
     *
     * @return object 200 if deletion is successful
     *                500 if an error occurs
     *
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
        if (!isRole('dsu') && !isRole('ngo') && getAffiliationId($id) != \Auth::user()->institution['_id']) {
            isDenied();
        }

        if ($user->role == 2) {
            $ong = Organisation::query()->where('_id', '=', $user->organisation['_id'])->first();
            $ong->contact_person = (object)['_id' => null, 'name' => null, 'email' => null, 'phone' => null];
            $ong->save();
        }
        $user->delete();
        $response = ["message" => 'User deleted.'];

        return response()->json($response, 200);
    }

    /**
     * Function responsible of processing import rescue officers requests.
     *
     * @param object $request Contains all the data needed for importing a list of users.
     *
     * @return object 200 if import is successful
     *                500 if an error occurs
     *
     * @SWG\Post(
     *   tags={"Users"},
     *   path="/api/users/import",
     *   summary="Import CSV with users",
     *   operationId="post",
     *   @SWG\Parameter(
     *     name="file",
     *     in="query",
     *     description="CSV file.",
     *     required=true,
     *     type="File"
     *   ),
     *  @SWG\Parameter(
     *     name="Coloanele din CSV",
     *     in="query",
     *     description="nume,email,judet,localitate,telefon, institutie,organizatie",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=400, description="request invalid"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function importUsers(Request $request, Import $import): \Illuminate\Http\JsonResponse
    {

        /** @var User $authenticatedUser */
        $authenticatedUser = \Auth::check() ? \Auth::user() : isDenied();;

        $file = $request->file('file');

        if (!$file instanceof UploadedFile) {
            return response()->json(
                [
                    "message" => 'Trebuie sa incarcati un fisier',
                ],
                400
            );
        }

        if(!in_array($file->getClientOriginalExtension(), ['csv'] )){
            return response()->json(
                [
                    "message" => 'Fisierul trebuie sa fie de tip csv',
                ],
                400
            );
        }

        $parser = new CsvParser($file->getPathname());

        if (true === $import->importRescueOfficers($parser->parse(), $authenticatedUser)->hasErrors()) {

            if ($import->getTotalImported() === 0) {
                $message = "Importul nu a putut fi efectuat";
            } else {
                $message = "Import partial finalizat";
            }

        } else {
            $message = "Import finalizat cu success";
        }

        $importedUsers = $import->getCreatedUsers();

        App::terminating(function() use ($importedUsers){

            foreach($importedUsers as $createdUser){

                /** Generate a password reset. */
                $passwordReset = PasswordReset::updateOrCreate(
                    ['email' => $createdUser->email],
                    ['email' => $createdUser->email, 'token' => Str::random(60)]
                );

                /** Create the pass reset URL. */
                $url = env('FRONT_END_URL').'/auth/reset/'.$passwordReset->token;

                try {
                    /** Send welcoming email. */
                    Mail::to($createdUser->email)->send(new SetUpPassword(['url' => $url]));
                }catch (\Exception $e){
                    //fail silently
                    Log::error($e->getMessage());
                    Log::error($e->getTraceAsString());
                }
            }

        });

        return response()->json(
            [
                "has_errors" => $import->hasErrors(),
                "total_errors" => count($import->getErrors()),
                "rows_imported" => $import->getTotalImported(),
                "errors" => $import->getErrors(),
                "message" => $message,
            ]
        );
    }
}
