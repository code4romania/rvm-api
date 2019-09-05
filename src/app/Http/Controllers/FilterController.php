<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Resource;
use App\Organisation;
use App\Volunteer;
use App\Course;
use App\CourseName;
use App\Institution;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class FilterController extends Controller
{
    /**
     * @SWG\Get(
     *   tags={"Filters"},
     *   path="/filter/organisations",
     *   summary="Show organisations name.",
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
     *   summary="Show all courses names from a volunteer.",
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
     *   summary="Show all users from a institution.",
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
}
