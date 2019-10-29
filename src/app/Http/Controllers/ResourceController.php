<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Mail\ResourceAdd;
use App\Mail\ResourceUpdate;
use App\Mail\ResourceDelete;
use App\Resource;
use App\Organisation;
use App\ResourceCategory;
use App\City;
use App\County;

class ResourceController extends Controller
{
    /**
     * Function responsible of processing get all resources requests.
     * 
     * @param object $request Contains all the data needed for extracting all the resources list.
     * 
     * @return object 200 and the list of resources if successful
     *                500 if an error occurs
     *  
     * @SWG\Get(
     *   tags={"Resources"},
     *   path="/api/resources",
     *   summary="Return all resources",
     *   operationId="index",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function index(Request $request) {
        $params = $request->query();
        $resources = Resource::query();

        if(isRole('ngo')) {
            $resources->where('organisation._id', '=', getAffiliationId());
        }

        /** Filter, sort and paginate the list of volunteers. */
        applyFilters($resources, $params, ['0' => ['categories', 'elemmatch', '_id', '$eq'],'1' => ['county._id', 'ilike'],'2' => ['name', 'ilike']]);
        applySort($resources, $params, ['1' => 'name', '2' => 'resource_type', '3' => 'quantity', '4' => 'organisation',]);
        $resources = $resources->get()->groupBy('slug');
        $resources = applyCollectionPaginate($resources, $params);

        foreach($resources['data'] as $key => &$resource){
            $res = $resource->toArray();
            $categories = array_filter( $resource->pluck('categories')->toArray());
            $organisations = $resource->unique('organisation._id')->pluck('organisation');

            $resources['data'][$key] = array(
                "slug" => $key,
                "name" => $res[0]['name'],
                "resources" => $res,
                "quantity" => $resource->sum('quantity'),
                "organisations_total" => $organisations->count(),
                "organisations" => $organisations,
                "categories" =>  $categories ? array_values( array_unique( call_user_func_array('array_merge',$categories) , SORT_REGULAR) ) : array()
            );
        }

        $resources['data'] = array_values($resources['data']->toArray());

        return response()->json($resources, 200); 
    }

     /**
     * Function responsible of processing get resource requests.
     * 
     * @param string $id The ID of the resource to be extracted.
     * 
     * @return object 200 and the resource details if successful
     *                404 if no resource is found
     *                500 if an error occurs
     *  
     * @SWG\Get(
     *   tags={"Resources"},
     *   path="/api/resources/{id}",
     *   summary="Show resource info ",
     *   operationId="show",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=404, description="empty resource"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function show($id) {
        $resource = Resource::find($id);

        if(empty($resource)) {
            return response()->json(404);
        }

        return $resource;
    }


     /**
     * Function responsible of processing get all resources with a slug requests.
     * 
     * @param object $request Contains all the data needed for extracting all the resources with a slug list.
     * 
     * @return object 200 and the list of resources if successful
     *                404 if no resource is found
     *                500 if an error occurs
     *  
     * @SWG\Get(
     *   tags={"Resources"},
     *   path="/api/resources/by_slug/{slug}",
     *   summary="Show resource info grouped by slug ",
     *   operationId="show",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=404, description="empty resource"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */

    public function by_slug(Request $request, $slug) {
        $params =  $request->query();
        $resources = Resource::query()->where('slug', '=', $slug);
        applySort($resources, $params, array(
            '1' => 'organisation.name',
            '2' => 'quantity',
            '3' => 'address',
            '4' => 'county._id',
            '5' => 'updated_at'
        ));
        $pager = applyPaginate($resources, $params);

        $resources = $resources->get();

        if(empty($resources)) {
            return response()->json(404);
        }

        return response()->json(array("pager" => $pager,"data" => $resources), 200);
    }


    /**
     * Function responsible of processing put resources requests.
     * 
     * @param object $request Contains all the data needed for saving a resource.
     * 
     * @return object 200 and the resource details if successful
     *                400 if validation fails
     *                500 if an error occurs
     *  
     * @SWG\Post(
     *   tags={"Resources"},
     *   path="/api/resources",
     *   summary="Create resource",
     *   operationId="store",
     *   @SWG\Parameter(
     *     name="organisation_id",
     *     in="query",
     *     description="Resource organisation id.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="name",
     *     in="query",
     *     description="Resource name.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="resource_type",
     *     in="query",
     *     description="Resource type.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="categories",
     *     in="query",
     *     description="Resource categories and subcategories.",
     *     required=true,
     *     type="array"
     *   ),
     *   @SWG\Parameter(
     *     name="quantity",
     *     in="query",
     *     description="Resource quantity.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="county",
     *     in="query",
     *     description="Resource county.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="city",
     *     in="query",
     *     description="Resource city.",
     *     required=true,
     *     type="string"
     *   ),
     *  @SWG\Parameter(
     *     name="address",
     *     in="query",
     *     description="Resource address.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="comments",
     *     in="query",
     *     description="Resource comments.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Parameter(
     *     name="added_by",
     *     in="query",
     *     description="Resource added by.",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function store(Request $request) {
        $data = $request->all();
        $rules = [
            'organisation_id' => 'required',
            'name' => 'required|string|max:255',
            'resource_type' => 'required|string',
            'quantity' => 'required|integer',
            'county' => 'required',
            'city' => 'required',
        ];
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response(['errors' => $validator->errors()->all()], 400);
        }
        $data = convertData($validator->validated(), $rules);

        if($request->has('categories')) {
            $data['categories'] = array();
            foreach ($request->categories as $key =>$val) {
                $resCat = ResourceCategory::query()->where('_id', '=', $val)->get(['_id', 'name', 'slug'])->first();

                $data['categories'][$key] = array('_id' => $resCat['_id'], 'name' => $resCat['name'], 'slug' => $resCat['slug']);
            }
        }

        $request->has('comments') ? $data['comments'] = $request->comments : '';
        $request->has('address') ? $data['address'] = $request->address : '';

        /** Add the 'county' and 'city' to the resource. */
        if ($request->has('county')) {
            $data['county'] = getCityOrCounty($request->county,County::query());
        }
        if ($request->has('city')) {            
            $data['city'] = getCityOrCounty($request->city,City::query());
        }

        /** Add the 'organisation' to the resource. */
        $organisation_id = $request->organisation_id;
        $organisation = \DB::connection('organisations')->collection('organisations')
                                                        ->where('_id', '=', $organisation_id)
                                                        ->get(['_id', 'name', 'slug', 'website'])
                                                        ->first();
        $data['organisation'] = $organisation;

        /** Add the 'added by' to the resource. */
        \Auth::check() ? $data['added_by'] = \Auth::user()->_id : '';
        $resource = Resource::create($data);

        if (!isRole('dsu')) {
            /** Notify the DSU admin of the add. */
            notifyUpdate('dsu', new ResourceAdd(['name' => $resource->organisation['name']]));
        }

        return response()->json($resource, 200); 
    }


    /**
     * Function responsible of processing resource update requests.
     * 
     * @param object $request Contains all the data needed for updating a resource.
     * @param string $id The ID of the resource to be updated.
     * 
     * @return object 200 and the JSON encoded resource details if successful
     *                404 if email or CNP/SSN are invalid fails
     *                500 if an error occurs
     *  
     * @SWG\put(
     *   tags={"Resources"},
     *   path="/api/resources/{id}",
     *   summary="Update resource",
     *   operationId="update",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function update(Request $request, $id) {
        $resource = Resource::findOrFail($id);
        $data = $request->all();
        if(isset($data['organisation_id']) && $data['organisation_id']) {
            $organisation_id = $data['organisation_id'];
            $organisation = Organisation::query()->where('_id', '=', $organisation_id)->get(['_id', 'name', 'website', 'address'])->first();

            $data['organisation'] = $organisation;
        }

        if(isset($data['categories']) && $data['categories']) {
            foreach ($data['categories'] as $key =>$val) {
                $resCat = ResourceCategory::query()->where('_id', '=', $val)->get(['_id', 'name', 'slug'])->first();

                $data['categories'][$key] = array('_id' => $resCat->_id, 'name' => $resCat->name, 'slug' => $resCat->slug);
            }
        }

        /** Add the 'county' and 'city' to the resource. */
        if($data['county']) {
            $data['county'] = getCityOrCounty($request['county'],County::query());
        }
        if($data['city']) {
            $data['city'] = getCityOrCounty($request['city'],City::query());
        }
        $resource->update($data);

        if (!isRole('dsu')) {
            /** Notify the DSU admin of the update. */
            notifyUpdate('dsu', new ResourceUpdate(['name' =>  $resource->organisation['name']]));
        }

        return response()->json($resource, 200);
    }


    /**
     * Function responsible of processing delete resource requests.
     * 
     * @param string $id The ID of the resource to be deleted.
     * 
     * @return object 200 if deletion is successful
     *                500 if an error occurs
     *  
     * @SWG\Delete(
     *   tags={"Resources"},
     *   path="/api/resources/{id}",
     *   summary="Delete resource",
     *   operationId="delete",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function delete($id) {
        /** Extract the resource. */
        $resource = Resource::findOrFail($id);
        /** Save the organisation name. */
        $organizationName = $resource->organisation['name'];

        /** Delete the volunteer. */
        $resource->delete();

        if (!isRole('dsu')) {
            /** Notify the DSU admin of the delete. */
            notifyUpdate('dsu', new ResourceDelete(['name' => $organizationName]));
        }

        $response = array("message" => 'Resource deleted.');

        return response()->json($response, 200);
    }


    /**
     * @SWG\Get(
     *   tags={"Resources"},
     *   path="/api/resources/list",
     *   summary="List resources",
     *   operationId="list",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function list(Request $request) {
        $items = [];
        $resOrgsIds = [];

        $resources = Resource::all();
        $resType = $resources->groupBy(['name']);

        foreach($resType as $key => $resource) {
            foreach($resource as $item) {
                $resOrgsIds[$key]['organisation_ids'][] = $item->organisation['_id'];
                if(isset($items[$key]) && array_key_exists($key, $items)) {
                    $items[$key]['resource_type'] = $item->resource_type;
                    $items[$key]['quantity'] +=  (int)$item->quantity;
                    $items[$key]['organisations_nr'] = count(array_unique($resOrgsIds[$key]['organisation_ids']));
                } else {
                    $items[$key]['resource_type'] = $item->resource_type;
                    $items[$key]['quantity']  =  (int)$item->quantity;
                    $items[$key]['organisations_nr'] =  1;
                }
            }
        }

        return response()->json($items, 200);
    }

    /**
     * Function responsible of processing get all resource categories requests.
     * 
     * @param object $request Contains all the data needed for geting all resource categories.
     * 
     * @return object 200 if extraction is successful
     *                500 if an error occurs
     *  
     * @SWG\Get(
     *   tags={"Resources"},
     *   path="/api/resources/categories",
     *   summary="List resource categories",
     *   operationId="category",
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function getAllResourceCategories(Request $request) {
        $params = $request->query();
        $resources = ResourceCategory::query();

        applyFilters($resources, $params, array('1' => array( 'slug', 'ilike' ),'2' => array( 'parent_id', '=' )));

        return response()->json($resources->get(['_id', 'parent_id', 'name', 'slug']), 200); 
    }


    /**
     * Function responsible of processing import resources requests.
     * 
     * @param object $request Contains all the data needed for importing a list of resources.
     * 
     * @return object 200 if import is successful
     *                500 if an error occurs
     *  
     * @SWG\Post(
     *   tags={"Resources"},
     *   path="/api/resources/import",
     *   summary="Import CSV with resources",
     *   operationId="import",
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
     *     description="nume,tip,categorie,subcategorie,cantitate,judet,localitate,adresa,comentarii",
     *     required=false,
     *     type="string"
     *   ),
     *   @SWG\Response(response=200, description="successful operation"),
     *   @SWG\Response(response=406, description="not acceptable"),
     *   @SWG\Response(response=500, description="internal server error")
     * )
     *
     */
    public function importResources(Request $request) {
        $file = $request->file('file');
        $filename = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $tempPath = $file->getRealPath();
        $fileSize = $file->getSize();
        $mimeType = $file->getMimeType();

        $valid_extension = array("csv");
        $errors = array();
        $imported = 0;
        if(in_array(strtolower($extension),$valid_extension)) {
            $location = 'uploads';
            $file->move($location,$filename);
            $filepath = public_path($location."/".$filename);
            $file = fopen($filepath,"r");
            $i = 0;
            $countys = County::all(['_id', "slug", "name"]);
            $county_map = array_column($countys->toArray(), null, 'slug');
            \Auth::check() ? $authenticatedUser = \Auth::user() : '';
            \Auth::check() ? $added_by = \Auth::user()->_id : '';
            $organisation = null;
            if(isset($authenticatedUser) && $authenticatedUser && $authenticatedUser['role']==2) {
                $organisationData = Organisation::find($authenticatedUser['organisation']['_id']);
                $organisation = (object) [
                    '_id' => $organisationData['_id'],
                    '_rev' => $organisationData['_rev'],
                    'name' => $organisationData['name'],
                    'website' => $organisationData['website'],
                    'type' => $organisationData['type']
                ];
            } else {
                if($request->organisation_id){
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
                $error = array();
                $num = count($setUpData);
                if($i == 0){
                   $i++;
                   continue; 
                }

                if(isset($setUpData[2]) && $setUpData[2]) {
                    $categories = [];
                    $category = ResourceCategory::query()
                        ->where('slug', '=', removeDiacritics($setUpData[2]))
                        ->first(['_id', 'name', 'slug']);
                        
                    if($category) {
                        $categories[] = $category->toArray();
                    
                        if(isset($setUpData[3]) && $setUpData[3]) {
                            $subcategory = ResourceCategory::query()
                                ->where('slug', '=', removeDiacritics($setUpData[3]))
                                ->where('parent_id', '=', $category['_id'])
                                ->first(['_id', 'name', 'slug']);
        
                            if($subcategory) {
                                $categories[] = $subcategory->toArray();
                            }else{
                                $error = addError($error, $setUpData[3], 'Subcategoria nu exista');
                            }
                        }
                    }else{
                        $error = addError($error, $setUpData[2], 'Categoria nu exista');
                    }
                } else {
                    $error = addError($error, $setUpData[2], 'Categoria este gresita');
                }

                $countySlug = removeDiacritics($setUpData[5]);
                $citySlug = removeDiacritics($setUpData[6]);
                if(isset($county_map[$countySlug]) && $county_map[$countySlug]) {
                    $getCity = \DB::connection('statics')->getCouchDBClient()
                        ->createViewQuery('cities', 'slug')
                        ->setKey(array($county_map[$countySlug]['_id'],$citySlug))
                        ->execute();

                    if($getCity->offsetExists(0)){
                        $city = array(
                            "_id" => $getCity->offsetGet(0)['id'],
                            "name" =>  $getCity->offsetGet(0)['value']
                        );
                    }else{
                        $error = addError($error, $citySlug, 'Orasul nu exista');
                    }
                } else {
                    $error = addError($error, $countySlug, 'Judetul nu exista');
                }

                if( count($error) == 0 ){
                    $insertData = array(
                        "name" => $setUpData[0],
                        "slug" => removeDiacritics($setUpData[0]),
                        "resource_type" => $setUpData[1],
                        "quantity" => $setUpData[4],
                        
                        "county" => array(
                            '_id' => $county_map[$countySlug]['_id'],
                            'name' => $county_map[$countySlug]['name']
                        ),
                        "city" =>  $city,
                        "categories" => $categories,
                        "comments" => $setUpData[8],
                        "address" => $setUpData[7],
                        "organisation" => $organisation,
                        'added_by' => $added_by
                    );

                     Resource::create($insertData);
                     $imported++;
                }else{
                    $errors[] =  [
                        'line' => $i,
                        'error' => $error
                    ];
                }
                $i++;
            }
            fclose($file);

            $total_errors = count($errors);

            if(count($errors) > 0 && $imported == 0){
                $message = "Importul nu a putut fi efectuat";
            }

            if(count($errors) > 0 && $imported > 0){
                $message = "Import partial finalizat";
            }

            if(count($errors) == 0 && $imported == $i-1){
                $message = "Import finalizat cu success";
            }

            return response(array(
                "has_errors" => $total_errors != 0,
                "total_errors" => $total_errors,
                "rows_imported" => $imported,
                "rows_discovered" => $i-1,
                "errors" => $errors,
                "message" => $message
            ));
        }
    }


    /**
     * Function responsible of returning the resources import template file.
     * 
     * @return object 200 and the template-resurse.csv file if successful
     *                500 if an error occurs
     */
    public function template() {
        return response()->download(storage_path("app/public/template-resurse.csv"));
    }
}
