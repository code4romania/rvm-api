<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Resource;
use App\Organisation;
use App\Volunteer;
use App\CourseName;
use App\CourseAccreditor;
use App\Institution;
use App\County;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class FilterController extends Controller
{
    /**
     * Function responsible of extracting a list of organizations <ID, NAME> pairs
     *  based on the received name.
     *
     * @param object $request Contains the name to be used for extracting the <ID, NAME> pairs list.
     *
     * @return object 200 and the found list of <ID, NAME> pairs (JSON encoded) if no error occurs
     *                500 if an error occurs
     *
     * @SWG\Get(
     *   tags={"Filters"},
     *   path="/filter/organisations",
     *   summary="Show organisations name/id.",
     *   operationId="filter_org_name",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=404, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error") 
     * )
     *
     */
    public function filterOrganisationsName(Request $request) {
        return response()->json(getFiltersByIdAndName($request->name, Organisation::query()), 200);
    }


    /**
     * Function responsible of extracting a list of volunteer courses based on the received name.
     *
     * @param object $request Contains the name to be used for extracting the list.
     *
     * @return object 200 and the found list (JSON encoded) if no error occurs
     *                500 if an error occurs
     *
     * @SWG\Get(
     *   tags={"Filters"},
     *   path="/filter/volunteers/courses",
     *   summary="Show courses by name/id.",
     *   operationId="filter_courses",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=404, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function filterVolunteerCourses(Request $request) {
        $model = CourseName::query();
        if(isset($request->name) && $request->name) {
            $model->where('name', 'ilike', '%' . $request->name . '%');
        }
        return response()->json($model->get(), 200);
    }


    /**
     * Function responsible of extracting a list of institutions <ID, NAME> pairs
     *  based on the received name.
     *
     * @param object $request Contains the name to be used for extracting the <ID, NAME> pairs list.
     *
     * @return object 200 and the found list of <ID, NAME> pairs (JSON encoded) if no error occurs
     *                500 if an error occurs
     *
     * @SWG\Get(
     *   tags={"Filters"},
     *   path="/filter/users/institutions",
     *   summary="Show all institutions.",
     *   operationId="filter_institution_users",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=404, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function filterInstitutionUsers(Request $request) {
        return response()->json(getFiltersByIdAndName($request->name, Institution::query()), 200);
    }


    /**
     * Function responsible of extracting a list of course accreditors <ID, NAME> pairs
     *  based on the received name.
     *
     * @param object $request Contains the name to be used for extracting the <ID, NAME> pairs list.
     *
     * @return object 200 and the found list of <ID, NAME> pairs (JSON encoded) if no error occurs
     *                500 if an error occurs
     *
     * @SWG\Get(
     *   tags={"Filters"},
     *   path="/filter/accreditedby",
     *   summary="Show all Accreditors.",
     *   operationId="filter_accreditedby",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=404, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function filterAccreditedBy(Request $request) {
        return response()->json(getFiltersByIdAndName($request->name, CourseAccreditor::query()), 200);
    }


     /**
     * Function responsible of extracting a list of all the conties with the total number
     *  of volunteers and resources for each county individually.
     *
     * @return object 200 and the list of counties with the total number of volunteers and resources individually if no error occurs
     *                500 if an error occurs
     *
     * @SWG\Get(
     *   tags={"Filters"},
     *   path="/filter/map",
     *   summary="Show number of resources and volunteers for each county.",
     *   operationId="filter_accreditedby",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=404, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function filterMap() {
        $counties = County::query()->get();
        foreach ($counties as $county) {
            $county->nrResurse = Resource::query()->get()->where('county._id', '=', $county->_id)->count();
            $county->nrVoluntari = Volunteer::query()->get()->where('county._id', '=', $county->_id)->count();
        }
        return $counties;
    }
}
