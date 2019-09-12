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
    public function filterOrganisationsName(Request $request)
    {
        return response()->json(getFiltersByIdAndName($request->name, Organisation::query()), 200);
    }

    /**
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
    public function filterVolunteerCourses(Request $request) 
    {
        return response()->json(getFiltersByIdAndName($request->name, CourseName::query()), 200);
    }

    /**
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
    public function filterInstitutionUsers(Request $request) 
    {
        return response()->json(getFiltersByIdAndName($request->name, Institution::query()), 200);
    }
    
    /**
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
    public function filterAccreditedBy(Request $request)
    {
        return response()->json(getFiltersByIdAndName($request->name, CourseAccreditor::query()), 200);
    }

     /**
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
    public function filterMap(Request $request)
    {
        $countys = County::query()->get();
        foreach ($countys as $county) {
            $nr_resources = Resource::query()->get()->where('county._id', '=', $county->_id)->count();
            $nr_voluntari = Volunteer::query()->get()->where('county._id', '=', $county->_id)->count();
            $county->nrResurse = $nr_resources;
            $county->nrVoluntari = $nr_voluntari;
        }
        return $countys;
    }
}
