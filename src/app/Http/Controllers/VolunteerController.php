<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Mail\VolunteerAdd;
use App\Mail\VolunteerUpdate;
use App\Mail\VolunteerDelete;
use App\Volunteer;
use App\CourseName;
use App\CourseAccreditor;
use App\City;
use App\County;
use App\Organisation;
use App\Allocation;
use App\Rules\Cnp;
use Carbon\Carbon;
use App\DBViews\StaticCitiesBySlugAndNameView;
use App\DBViews\StaticCountiesBySlugAndNameView;


class VolunteerController extends Controller
{
    /**
     * Function responsible of processing get all volunteers requests.
     *
     * @param object $request Contains all the data needed for extracting all the volunteers list.
     *
     * @return object 200 and the list of volunteers if successful
     *                500 if an error occurs
     *
     * @SWG\Get(
     *   tags={"Volunteers"},
     *   path="/api/volunteers",
     *   summary="Return all volunteers",
     *   operationId="index",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function index(Request $request) {
        $params = $request->query();
        $volunteers = Volunteer::query();

        /** Restrict 'ngo' user to view only he volunteers he/she added. */
        if(isRole('ngo')) {
            $volunteers->where('organisation._id', '=', getAffiliationId());
        }

        /** Filter, sort and paginate the list of volunteers. */
        applyFilters($volunteers, $params, ['0' => ['county._id', 'ilike'], '1' => ['courses', 'elemmatch', "course_name._id", '$eq'], '2' => ['organisation._id', '='], '3' => ['name', 'ilike'], ]);
        applySort($volunteers, $params, array('1' => 'name', '2' => 'county', '3' => 'organisation.name'));
        $pager = applyPaginate($volunteers, $params);

        return response()->json(array("pager" => $pager,"data" => $volunteers->get()), 200);
    }


     /**
     * Function responsible of processing get volunteer requests.
     *
     * @param string $id The ID of the volunteer to be extracted.
     *
     * @return object 200 and the volunteer details if successful
     *                500 if an error occurs
     *
     * @SWG\Get(
     *   tags={"Volunteers"},
     *   path="/api/volunteers/{id}",
     *   summary="Show volunteer info ",
     *   operationId="show",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function show($id) {
        return Volunteer::find($id);
    }


    /**
     * Function responsible of processing put volunteer requests.
     *
     * @param object $request Contains all the data needed for saving a new volunteer.
     *
     * @return object 201 and the volunteer details if successful
     *                400 if validation fails
     *                404 if organization not found fails
     *                500 if an error occurs
     *
     * @SWG\Post(
     *   tags={"Volunteers"},
     *   path="/api/volunteers",
     *   summary="Create volunteer",
     *   operationId="store",
     *   @SWG\Parameter(
     *     name="organisation_id",
     *     in="query",
     *     description="Volunteer organisation id.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Volunteer name.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="ssn",
     *     in="query",
     *     description="Volunteer ssn.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="email",
     *     in="query",
     *     description="Volunteer email.",
     *     required=true,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="phone",
     *     in="query",
     *     description="Volunteer phone.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="county",
     *     in="query",
     *     description="Volunteer county.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="city",
     *     in="query",
     *     description="Volunteer city.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="course_name_id",
     *     in="query",
     *     description="Name of course from DB .",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="obtained",
     *     in="query",
     *     description="Obtained.",
     *     required=true,
     *     type="date-time"
     *   ),
     *  @SWG\Parameter(
     *     name="accredited_by",
     *     in="query",
     *     description="Accredited by.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="address",
     *     in="query",
     *     description="Volunteer address.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="comments",
     *     in="query",
     *     description="Volunteer comments.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="job",
     *     in="query",
     *     description="Volunteer job.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="added_by",
     *     in="query",
     *     description="Volunteer added by.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(response=201, description="successful operation"),
     *   @SWG\Response(response=400, description="validation fails"),
     *   @SWG\Response(response=404, description="organization not found"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function store(Request $request) {
        $data = $request->all();
        $rules = [
            'organisation_id' => 'required',
            'email' => 'required|string|email|max:255|unique:volunteers.volunteers',
            'phone' => 'required|string|min:6|',
            'ssn' => new Cnp,
            'name' => 'required|string|max:255',
        ];

        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 400);
        }

        /** Validate SSN uniqueness. */
        $data = convertData($validator->validated(), $rules);
        if (isset($validator->validated()['ssn']) && $validator->validated()['ssn']) {
            $data['ssn'] = $validator->validated()['ssn'];
        } else {
            return response(['errors' => 'CNP'], 400);
        }

        $data['allocation'] = '';
        $data['comments'] = $request->has('comments') ? $request->comments : '';
        $data['job'] = $request->has('job') ? $request->job : '';

        /** Add 'organisation' to the volunteer. */
        $organisation_id = $request->organisation_id;
        $organisation = \DB::connection('organisations')->collection('organisations')->where('_id', '=', $organisation_id)->get(['_id', 'name', 'website'])->first();
        if (!$organisation) {
            return response()->json('Organizatia nu exista', 404);
        }

        $data['organisation'] = $organisation;

        /** Add 'city' and 'county' to the volunteer. */
        if ($request->has('county')) {
            $data['county'] = getCityOrCounty($request->county,County::query());
        }
        if ($request->has('city')) {
            $data['city'] = getCityOrCounty($request->city,City::query());
        }

        /** Add 'adden by' to the the volunteer */
        $data['added_by'] = \Auth::check() ? \Auth::user()->_id : '';
        $data['courses'] = [];
        /** Create the volunteer. */
        $volunteer = Volunteer::create($data);

        /** Extract all courses from request and process them. */
        $courses =  $request->has('courses') ? $request->courses : '';
        if ($courses && !is_null($courses) && !empty($courses)) {
            foreach ($courses as $course) {
                if (isset($course['course_name_id']) && !is_null($course['course_name_id'])) {
                    $course_name = CourseName::find($course['course_name_id']);
                    if ($course_name) {
                        /** Create a new course. */
                        $newCourse = [
                            'course_name' => [
                                '_id' => $course_name['_id'],
                                'name' => $course_name['name'],
                                'slug' => removeDiacritics($course_name['name'])],
                            'obtained' => Carbon::parse($course['obtained'])->format('Y-m-d H:i:s')
                        ];

                        /** Check if the accreditor already exists in DB. */
                        $courseAccreditor = CourseAccreditor::query()->where('name', '=', $course['accredited_by'])->first();
                        if (!$courseAccreditor) {
                            $courseAccreditor = CourseAccreditor::create(['name' => $course['accredited_by'], 'courses' => [$course_name['_id']]]);
                        } else {
                            if (is_array($courseAccreditor->courses) && !in_array($course_name['_id'], $courseAccreditor->courses)) {
                                $courseAccreditor->courses = array_merge( $courseAccreditor->courses, [$course_name['_id']]);
                                $courseAccreditor->save();
                            }
                        }
                        /** Save the accreditor and add the course to the volunteer. */
                        $newCourse['accredited'] = ['_id' => $courseAccreditor->_id,'name' => $courseAccreditor->name];
                        $data['courses'][] = $newCourse;
                    }
                }
            }
            /** Add the 'courses' to the volunteer. */
            $volunteer->courses = $data['courses'];
        }
        $volunteer->save();

        if (!isRole('dsu')) {
            /** Notify the DSU admin of the add. */
            notifyUpdate('dsu', new VolunteerAdd(['name' => $volunteer->organisation['name']]));
        }

        return response()->json($volunteer, 201);
    }


    /**
     * Function responsible of processing a volunteer update requests.
     *
     * @param object $request Contains all the data needed for updating a volunteer.
     * @param string $id The ID of the volunteer to be updated.
     *
     * @return object 201 and the JSON encoded volunteer details if successful
     *                404 if email or CNP/SSN are invalid fails
     *                500 if an error occurs
     *
     * @SWG\put(
     *   tags={"Volunteers"},
     *   path="/api/volunteers/{id}",
     *   summary="Update volunteer",
     *   operationId="update",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=404, description="not valid"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function update(Request $request, $id) {
        $volunteer = Volunteer::findOrFail($id);
        $data = $request->all();

        /** Validate email uniqueness. */
        if($volunteer->email != $data['email']) {
            $volunteer_same_email = Volunteer::query()->where('email', '=', $data['email'])->first();
            if(isset($volunteer_same_email)) {
                return response()->json('Email invalid', 404);
            }
        }
        /** Validate CNP/SSN uniqueness. */
        if($volunteer->ssn != $data['ssn']) {
            $volunteer_same_ssn = Volunteer::query()->where('ssn', '=', $data['ssn'])->first();
            if(isset($volunteer_same_ssn)) {
                return response()->json('CNP invalid', 404);
            }
        }
        /** Extract 'county' and 'city'. */
        if ($data['county'] && !is_null($data['county'])) {
            $data['county'] = getCityOrCounty($request['county'],County::query());
        }
        if ($data['city'] && !is_null($data['city'])) {
            $data['city'] = getCityOrCounty($request['city'],City::query());
        }
        /** Extract and set 'organisation_id'. */
        $organisation_id = $request['organisation_id'];
        $organisation = \DB::connection('organisations')->collection('organisations')->where('_id', '=', $organisation_id)->get(['_id', 'name', 'website'])->first();
        $data['organisation'] = $organisation;
        /** Extract and set 'added_by'. */
        \Auth::check() ? $data['added_by'] = \Auth::user()->_id : '';

        /** Extract all courses from request and process them. */
        $courses =  $request->has('courses') ? $request->courses : '';
        $data['courses'] = [];
        if($courses && !is_null($courses) && !empty($courses)){
            foreach ($courses as $course) {
                if(isset($course['course_name_id']) && !is_null($course['course_name_id'])) {
                    $course_name = CourseName::find($course['course_name_id']);
                    if($course_name){
                        /** Create a new course. */
                        $newCourse = [
                            'course_name' => [
                                '_id' => $course_name['_id'],
                                'name' => $course_name['name'],
                                'slug' => removeDiacritics($course_name['name'])
                            ],
                            'obtained' => Carbon::parse($course['obtained'])->format('Y-m-d H:i:s')
                        ];

                        /** Check if the accreditor already exists in DB. */
                        $courseAccreditor = CourseAccreditor::query()->where('name', '=', $course['accredited_by'])->first();
                        if(!$courseAccreditor) {
                            $courseAccreditor = CourseAccreditor::create([
                                'name' => $course['accredited_by'],
                                'courses' => [$course_name['_id']]
                            ]);
                        } else {
                            if(is_array($courseAccreditor->courses) && !in_array($course_name['_id'], $courseAccreditor->courses)){
                                $courseAccreditor->courses = array_merge( $courseAccreditor->courses, [$course_name['_id']]);
                                $courseAccreditor->save();
                            }
                        }

                        /** Save the accreditor and add the course to the volunteer. */
                        $newCourse['accredited'] = ['_id' => $courseAccreditor->_id,'name' => $courseAccreditor->name];
                        $data['courses'][] = $newCourse;
                    }
                }
            }
            /** Add the courses. */
            $volunteer->courses = $data['courses'];
        }
        $volunteer->update($data);

        if (!isRole('dsu')) {
            /** Notify the DSU admin of the update. */
            notifyUpdate('dsu', new VolunteerUpdate(['name' => $volunteer->organisation['name']]));
        }

        return $volunteer;
    }


    /**
     * Function responsible of processing delete volunteers requests.
     *
     * @param string $id The ID of the volunteer to be deleted.
     *
     * @return object 200 if deletion is successful
     *                500 if an error occurs
     *
     * @SWG\Delete(
     *   tags={"Volunteers"},
     *   path="/api/volunteers/{id}",
     *   summary="Delete volunteer",
     *   operationId="delete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function delete($id) {
        /** Extract the volunteer. */
        $volunteer = Volunteer::findOrFail($id);
        /** Save the organisation name. */
        $organizationName = $volunteer->organisation['name'];

        /** Delete the volunteer. */
        $volunteer->delete();

        if (!isRole('dsu')) {
            /** Notify the DSU admin of the delete. */
            notifyUpdate('dsu', new VolunteerDelete(['name' => $organizationName]));
        }

        /** Prepare the response and respond. */
        $response = array("message" => 'Volunteer deleted.');
        return response()->json($response, 200);
    }


    /**
     * Function responsible of processing import volunteers requests.
     *
     * @param object $request Contains all the data needed for importing a list of volunteers.
     *
     * @return object 200 if import is successful
     *                500 if an error occurs
     *
     * @SWG\Post(
     *   tags={"Volunteers"},
     *   path="/api/volunteers/import",
     *   summary="Import CSV with volunteers",
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
     *     description="nume,cnp,email,telefon,judet,localitate,profesie,comentarii,acreditari",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function importVolunteers(Request $request) {
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        $valid_extension = array("csv");
        $errors = array();
        if(in_array(strtolower($extension), $valid_extension)) {
            $location = 'uploads';
            $file->move($location, $filename);
            $filepath = public_path($location . "/" . $filename);
            $file = fopen($filepath, "r");
            $i = 0;
            $countys = County::all(['_id', "slug", "name"]);
            $county_map = array_column($countys->toArray(), null, 'slug');
            $imported = 0;
            $organisation = null;

            \Auth::check() ? $authenticatedUser = \Auth::user() : '';

            if (isset($authenticatedUser) && $authenticatedUser && $authenticatedUser['role'] == 2) {
                /** Extract the authenticated user organisation data. */
                $organisationData = Organisation::find($authenticatedUser['organisation']['_id']);

                $organisation = (object) [
                    '_id' => $organisationData['_id'],
                    '_rev' => $organisationData['_rev'],
                    'name' => $organisationData['name'],
                    'website' => $organisationData['website'],
                    'type' => $organisationData['type']
                ];
            } else {
                if ($request->organisation_id) {
                    /** Extract the request organisation data. */
                    $organisationData = Organisation::find($request->organisation_id);

                    $organisation = (object) [
                        '_id' => $organisationData['_id'],
                        '_rev' => $organisationData['_rev'],
                        'name' => $organisationData['name'],
                        'website' => $organisationData['website'],
                        'type' => $organisationData['type']
                    ];
                }
            }

            /** Check NO organization has been found. */
            if(!$organisation){
                return response(array(
                    "has_errors" => true,
                    "total_errors" => 0,
                    "rows_imported" => 0,
                    "rows_discovered" => 0,
                    "errors" => [],
                    "message" => "Va rugam selectati organizatia"
                ));
            }

            while (($setUpData = fgetcsv($file, 1000, ",")) !== FALSE) {
                /** Skip the first row. */
                if (0 == $i) {
                   $i++;
                   continue;
                }

                /** Reset the errors array. */
                $error = array();
                /** Get the row length. */
                $num = count($setUpData);

                /** Check if columns 1 to 5 are NOT ser, NOT null or empty. */
                $error = verifyErrors($error, $setUpData[0], 'Nume');
                $error = verifyErrors($error, $setUpData[1], 'CNP');
                $error = verifyErrors($error, $setUpData[2], 'Email');
                $error = verifyErrors($error, $setUpData[3], 'Telefon');
                $error = verifyErrors($error, $setUpData[4], 'Judet');
                $error = verifyErrors($error, $setUpData[5], 'Localitate');

                /** Define the array for the courses. */
                $coursesData = array();

                /** Check if any courses are specified. */
                if (isset($setUpData[8]) && $setUpData[8]) {
                    /** Extract the list of specified courses and separate it by the ';' character. */
                    $courses = explode(';', $setUpData[8]);

                    /** Chech if the courses have been added correctlly. */
                    $error = verifyErrors($error, $courses, 'Acreditarile au fost separate gresit.');

                    /** Check if any courses have been specified. */
                    if (isset($courses) && $courses) {
                        /** Parse all specified courses. */
                        foreach ($courses as $course) {
                            /** Extract the course details. */
                            $each = explode(':', $course);

                            /** Check course details have been added correctlly. */
                            $error = verifyErrors($error, $each, 'Datele acreditarii separate gresit.');

                            /** Save course details in <KEY>:<VALUE> pair. */
                            $course_values = [
                                'course_name_id' => removeDiacritics($each[0]),
                                'obtained' => $each[1],
                                'accredited_by' => $each[2]
                            ];

                            /** Check the course name is specified. */
                            if (isset($course_values['course_name_id']) && !is_null($course_values['course_name_id'])) {
                                /** Search and extract the course from the DB. */
                                $course_name = CourseName::query()->where('slug', '=', $course_values['course_name_id'])->first();

                                /** Check if the course was found in the DB. */
                                if ($course_name) {
                                    /** Create new DB entry. */
                                    $newCourse = (object) [
                                        'course_name' =>(object) [
                                            '_id' => $course_name['_id'],
                                            'name' => $course_name['name'],
                                            'slug' => removeDiacritics($course_name['name'])
                                        ],
                                        'obtained' => Carbon::parse($course_values['obtained'])->format('Y-m-d H:i:s')
                                    ];

                                    /** Search and extract the course accreditor from the DB. */
                                    $courseAccreditor = CourseAccreditor::query()->where('name', '=', $course_values['accredited_by'])->first();

                                    /** Check if the course accreditor was NOT found in the DB. */
                                    if (!$courseAccreditor) {
                                        /** Create a new course accreditor. */
                                        $courseAccreditor = CourseAccreditor::create([
                                            'name' => $course_values['accredited_by'],
                                            'courses' => [$course_name['_id']]
                                        ]);
                                    } else {
                                        /** Check that a link between the course accreditor and the course does NOT exists. */
                                        if (is_array($courseAccreditor->courses) && !in_array($course_name['_id'], $courseAccreditor->courses)) {
                                            /** Create link between the course accreditor and the course. */
                                            $courseAccreditor->courses = array_merge( $courseAccreditor->courses, [$course_name['_id']]);
                                            $courseAccreditor->save();
                                        }
                                    }

                                    /** Add the link between the course accreditor and the course to the course. */
                                    $newCourse->accredited = (object) ['_id' => $courseAccreditor['_id'],'name' => $courseAccreditor['name']];

                                    /** Add the new course to the list of courses. */
                                    $coursesData[] = (object) $newCourse;
                                } else {
                                    $error = addError($error, $course_name, 'Numele acreditarii nu se afla in baza de date.');
                                }
                            } else {
                                $error = addError($error, $course_values['course_name_id'], 'Numele acreditarii setat gresit.');
                            }
                        }
                    }
                }

                /** Remove county diacritics. */
                $countySlug = removeDiacritics($setUpData[4]);
                /** Remove city diacritics. */
                $citySlug = removeDiacritics($setUpData[5]);

                /** Check the county is valid (exists in DB). */
                if (isset($county_map[$countySlug]) && $county_map[$countySlug]) {
                    /** Create get city query. */
                    $getCity = \DB::connection('statics')->getCouchDBClient()
                                                         ->createViewQuery('cities', 'slug')
                                                         ->setKey(array($county_map[$countySlug]['_id'], $citySlug))
                                                         ->execute();

                    /** Check the city exists inside the county. */
                    if ($getCity->offsetExists(0)) {
                        /** Extract the city details. */
                        $city = array(
                            "_id" => $getCity->offsetGet(0)['id'],
                            "name" =>  $getCity->offsetGet(0)['value']
                        );
                    } else {
                        $error = addError($error, $citySlug, 'Orasul nu exista');
                    }
                } else {
                    $error = addError($error, $countySlug, 'Judetul nu exista');
                }

                /** Extract the number of volunteers that have the same email and CNP. */
                $email = Volunteer::query()->where('email', '=', $setUpData[2])->get()->count();
                $cnp = Volunteer::query()->where('ssn', '=', $setUpData[1])->get()->count();

                /** Check that NO errors occured and NO volunteer with the same email or CNP has been found. */
                if ((0 == count($error)) && (0 == $email) && (0 == $cnp)) {
                    /** Construct the volunteer data. */
                    $insertData = array(
                        "name" => $setUpData[0],
                        "ssn" => $setUpData[1],
                        "email" => $setUpData[2],
                        "phone" => $setUpData[3],
                        "organisation" => $organisation,
                        "county" => array(
                            '_id' => $county_map[$countySlug]['_id'],
                            'name' => $county_map[$countySlug]['name']
                        ),
                        "city" =>  $city,
                        "job" => $setUpData[6],
                        "courses" => $coursesData,
                        "comments" => $setUpData[7],
                        "allocation" => ""
                    );

                    /** Save the new volunteer to the DB. */
                    Volunteer::create($insertData);

                    /** Increment the imported number of volunteers. */
                    $imported++;
                } else {
                    $error = addError($error, $setUpData[2], 'Email-ul exista deja sau cnp-ul exista deja');
                    $errors[] =  [
                        'line' => $i,
                        'error' => $error
                    ];
                }

                /** Move to the next volunteer. */
                $i++;
            }
            /** Close the CSV. */
            fclose($file);

            /** Count the total errors number. */
            $total_errors = count($errors);

            /** Compose the response message. */
            $message = "";

            if ((0 < count($errors)) && (0 == $imported)) {
                $message = "Importul nu a putut fi efectuat";
            }

            if ((0 < count($errors)) && (0 < $imported)) {
                $message = "Import partial finalizat";
            }

            if ((0 == count($errors)) && (($i - 1) == $imported)) {
                $message = "Import finalizat cu success";
            }

            /** Send the response. */
            return response(array(
                "has_errors" => ($total_errors != 0),
                "total_errors" => $total_errors,
                "rows_imported" => $imported,
                "rows_discovered" => ($i - 1),
                "errors" => $errors,
                "message" => $message
            ));
        }
    }


    /**
     * Function responsible of extracting the list of allocations of a volunteer.
     *
     * @param string $id The ID of the volunteer for which to extract the allocations list.
     *
     * @return object 200 if extraction is successful
     *                500 if an error occurs
     */
    public function allocations($id) {
        $allocations = Allocation::query()->where('volunteer._id', '=', $id)->get();

        return response()->json($allocations, 200);
    }


    /**
     * Function responsible of returning the volunteers import template file.
     *
     * @return object 200 and the template-voluntari.csv file if successful
     *                500 if an error occurs
     */
    public function template() {
        return response()->download(storage_path("app/public/template-voluntari.csv"));
    }
}
