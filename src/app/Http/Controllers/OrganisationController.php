<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Mail\SetUpPassword;
use App\Mail\NotifyTheOrganisation;
use App\PasswordReset;
use App\Organisation;
use App\CourseAccreditor;
use App\CourseName;
use App\Volunteer;
use App\Resource;
use App\User;
use App\County;
use App\City;
use Carbon\Carbon;

class OrganisationController extends Controller
{
        /**
     * @SWG\Get(
     *   tags={"Organisations"},
     *   path="/api/organisations",
     *   summary="Return all organisations",
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
        $organisations = Organisation::query();
        if(isRole('ngo')) {
            $organisations->where('_id', '=', getAffiliationId());
        }
        applyFilters($organisations, $params, array(
            '1' => array( 'county._id', 'ilike' ),
            '2' => array( 'name', 'ilike')
        ));
        if(isset($request->filters[0])) {
            $organisations->where('cover', 'ilike', $request->filters[0]);
        }
        if(isset($request->filters[3])){
            $matchOrganisations = Volunteer::query()
                ->where('courses', 'elemmatch', array("course_name._id" => $request->filters[3]))
                ->get(['organisation._id'])
                ->pluck('organisation._id')
                ->unique('organisation._id')
                ->toArray();
            
            if(!$matchOrganisations){
                return response()->json(array(
                    "pager" =>  emptyPager($params),
                    "data" => []
                ), 200); 
            }

            $organisations->whereIn('_id', $matchOrganisations);
        }

        if(isset($request->filters[4])){
            $matchOrganisations2 = Resource::query()
                ->where('categories', 'elemmatch', array("_id" => $request->filters[4]))
                ->get(['organisation._id'])
                ->pluck('organisation._id')
                ->unique('organisation._id')
                ->toArray();

            if(!$matchOrganisations2){
                return response()->json(array(
                    "pager" => emptyPager($params),
                    "data" => []
                ), 200); 
            }

            $organisations->whereIn('_id', $matchOrganisations2);
        }

        applySort($organisations, $params, array(
            '1' => 'name',
            '2' => 'county',
        ));

        $pager = applyPaginate($organisations, $params);
        $organisations = $organisations->get();
        $organisationsIds = array_map(function($o){ return $o['_id']; }, $organisations->toArray());

        $volunteers = Volunteer::query()->whereIn('organisation._id', $organisationsIds)->get(['_id', 'organisation._id']);
        $resources = Resource::query()->whereIn('organisation._id', $organisationsIds)->get(['_id', 'organisation._id']);

        foreach($organisations as $organisation){
           $organisation->volunteers = $volunteers->where('organisation._id', '=', $organisation->_id)->count();
           $organisation->resources = $resources->where('organisation._id', '=', $organisation->_id)->count();
        }

        return response()->json(array(
            "pager" => $pager,
            "data" => $organisations
        ), 200); 
    }
     /**
     * @SWG\Get(
     *   tags={"Organisations"},
     *   path="/api/organisations/{id}",
     *   summary="Show organisation info ",
     *   operationId="show",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=404, description="not acceptable"),
     * )
     *
     */

    public function show($id)
    {
        $organisation = Organisation::findOrFail($id);
        if(!$organisation){
            return response()->json('Nu exista', 404);
        }

        if(isRole('ngo')){
            if($organisation->_id != getAffiliationId()){
                isDenied();
            }
        }
        return response()->json($organisation, 200); 
    }

    /**
    * @SWG\Get(
    *   tags={"Organisations"},
    *   path="/api/organisations/{id}/email",
    *   summary="Send notification via email to a organisation admin",
    *   operationId="send",
    *   @SWG\Response(response=200, description="successful operation"),
    *   @SWG\Response(response=404, description="not found")
    * )
    *
    */
    public function sendNotification($id) {
        $organisation = Organisation::findOrFail($id);
        if(!$organisation){
            return response()->json('Nu exista', 404);
        }

        if(isRole('ngo')){
            if($organisation->_id != getAffiliationId()){
                isDenied();
            }
        }
        $data = ['url' => env('FRONT_END_URL').'/auth'];
        Mail::to($organisation['email'])->send(new NotifyTheOrganisation($data));
        return response()->json('Email sent succesfully', 200); 
    }

    /**
    * @SWG\Get(
    *   tags={"Organisations"},
    *   path="/api/organisations/{id}/volunteers",
    *   summary="Show all volunteers of an Organisation ",
    *   operationId="show",
    *   @SWG\Response(response=200, description="successful operation"),
    *   @SWG\Response(response=404, description="not found")
    * )
    *
    */

    public function showVolunteers(Request $request, $id)
    {
        $params = $request->query();

        if(isRole('ngo')){
            if( $id != getAffiliationId()){
                isDenied();
            }
        }

        $volunteers = Volunteer::query()
            ->where('organisation._id', 'ilike', $id);

        applyFilters($volunteers, $params, array(
            '0' => array( 'county._id', 'ilike' ),
            '1' => array( 'courses', 'elemmatch' , "course_name._id", '$eq'),
            '2' => array ( 'name', 'ilike')
        ));
    
        applySort($volunteers, $params, array(
            '1' => 'name',
            '2' => 'courses',
            '3' => 'county._id',
            '4' => 'updated_at'
        ));

        $pager = applyPaginate($volunteers, $params);

        return response()->json(array(
            "pager" => $pager,
            "data" => $volunteers->get()
        ), 200); 
    }

    /**
    * @SWG\Get(
    *   tags={"Organisations"},
    *   path="/api/organisations/{id}/resources",
    *   summary="Show all resources of an Organisation ",
    *   operationId="show",
    *   @SWG\Response(response=200, description="successful operation"),
    *   @SWG\Response(response=404, description="not found")
    * )
    *
    */

    public function showResources(Request $request, $id)
    {
        $params = $request->query();
        
        if(isRole('ngo')){
            if( $id != getAffiliationId()){
                isDenied();
            }
        }

        $resources = Resource::query()
            ->where('organisation._id', '=', $id);

        applyFilters($resources, $params, array(
            '0' => array( 'categories', 'elemmatch', '_id', '$eq' ),
            '1' => array( 'county._id', 'ilike' ),
            '2' => array( 'name', 'ilike')
        ));

        applySort($resources, $params, array(
            '1' => 'name',
            '2' => 'categories',
            '3' => 'quantity',
            '4' => 'county'
        ));

        $pager = applyPaginate($resources, $params);

        return response()->json(array(
            "pager" => $pager,
            "data" => $resources->get()
        ), 200); 
    }


    /**
     * @SWG\Post(
     *   tags={"Organisations"},
     *   path="/api/organisations",
     *   summary="Create organisation",
     *   operationId="store",
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Organisation name.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="website",
     *     in="query",
     *     description="Organisation website.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="contact_person",
     *     in="query",
     *     description="Organisation Contact Person.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="Organisation email.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="phone",
     *     in="query",
     *     description="Organisation phone.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="county",
     *     in="query",
     *     description="Organisation county.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="city",
     *     in="query",
     *     description="Organisation city.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="address",
     *     in="query",
     *     description="Organisation address.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="comments",
     *     in="query",
     *     description="Organisation comments.",
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
            'website' => 'required|max:255',
            'contact_person' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:organisations.organisations',
            'phone' => 'required|string|min:6|',
            'cover' => 'required'
        ];
        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 400);
        }

        $data = convertData($validator->validated(), $rules);
        $data['status'] = 'Active';

        if ($request->has('county')) {
            $data['county'] = getCityOrCounty($request->county,County::query());
        }
        if ($request->has('city')) {            
            $data['city'] = getCityOrCounty($request->city,City::query());
        }
        
        $data['comments'] = $request->has('comments') ? $request->comments : '';
        $data['address'] = $request->has('address') ? $request->address : '';
        \Auth::check() ? $data['added_by'] = \Auth::user()->_id : '';
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
        $data['password'] = Hash::make(str_random(16));
        Mail::to($data['email'])->send(new SetUpPassword($set_password_data));
        $organisation = Organisation::create($data);
        if(!isRole('dsu')){
            if(isset($data['organisation'])){
                unset($data['organisation']);
            }
        }
        $data = setAffiliate($data);
        $newNgoAdmin = User::firstOrNew([
            'email' => $data['email'],
        ]);
        $newNgoAdmin->name = $data['contact_person'];
        $newNgoAdmin->role = config('roles.role.ngo');
        $newNgoAdmin->phone = $data['phone'];
        $newNgoAdmin->organisation = array('_id' => $organisation->_id, 'name' => $organisation->name);
        $newNgoAdmin->added_by = $data['added_by'];
        $newNgoAdmin->password = Hash::make(str_random(16));
        $newNgoAdmin->save();

        $response = array(
            "message" => 'Password sent to email.',
            "organisation" => $organisation
        );
        return response()->json($response, 201); 
    }

    /**
     * @SWG\put(
     *   tags={"Organisations"},
     *   path="/api/organisations/{id}",
     *   summary="Update organisation",
     *   operationId="update",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function update(Request $request, $id)
    {
        $organisation = Organisation::findOrFail($id);

        if(!$organisation){
            return response()->json('Nu exista', 404);
        }

        if(isRole('ngo')){
            if($organisation->_id != getAffiliationId()){
                isDenied();
            }
        }

        $data = $request->all();
        if ($data['county']) {
            $data['county'] = getCityOrCounty($request['county'],County::query());
        }
        if ($data['city']) {
            $data['city'] = getCityOrCounty($request['city'],City::query());
        }
        $organisation->update($data);
        
        return $organisation;
    }

    /**
     * @SWG\Delete(
     *   tags={"Organisations"},
     *   path="/api/organisations/{id}",
     *   summary="Delete organisation",
     *   operationId="delete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function delete(Request $request, $id)
    {
        $organisation = Organisation::findOrFail($id);
        if(!$organisation){
            return response()->json('Nu exista', 404);
        }

        if(isRole('ngo')){
            if($organisation->_id != getAffiliationId()){
                isDenied();
            }
        }

        if(isRole('ngo') && isRole('ngo', $organisation)){
            isDenied();
        }

        $volunteers = Volunteer::query()->where('organisation._id', '=', $organisation->_id)->get();
        if($volunteers) {
            foreach ($volunteers as $volunteer) {
                $volunteer->delete();
            }
        }
        $resources = Resource::query()->where('organisation._id', '=', $organisation->_id)->get();
        if($resources) {
            foreach ($resources as $resource) {
                $resource->delete();
            }
        }
        $users = User::query()->where('organisation._id', '=', $organisation->_id)->get();
        if($users) {
            foreach ($users as $user) {
                $user->delete();
            }
        }
        $organisation->delete();

        $response = array("message" => 'Organisation deleted.');

        return response()->json($response, 200);
    }


    /**
     * @SWG\Get(
     *   tags={"Organisations"},
     *   path="/api/organisations/{id}/validate",
     *   summary="Validate organisation data",
     *   operationId="validate",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function validateData(Request $request, $id)
    {
        $organisation = Organisation::findOrFail($id);
        if(!$organisation){
            return response()->json('Nu exista', 404);
        }

        if(isRole('ngo')){
            if($organisation->_id != getAffiliationId()){
                isDenied();
            }
        }

        $organisation->updated_at =  (string) Carbon::now()->format('Y-m-d H:i:s');
        $organisation->save();

        return response()->json(array('success' => true), 200);
    }
}
