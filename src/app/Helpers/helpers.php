<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Illuminate\Validation\ValidationRuleParser;
use App\Volunteer;
/**
 * Pagination helper
 *
 * @param string $data Items to paginate
 * @return array of $data 
 */

// function rvmPaginate($data) 
// {
//     $size = Request::has('size') ? Request::query('size')  : '15';
//     $currentPage = LengthAwarePaginator::resolveCurrentPage();

//     //Create a new Laravel collection from the array data
//     $data = ($data instanceof Collection) ? $data : Collection::make($data);

//     //Slice the collection to get the items to display in current page
//     $currentPageItems = $data->slice(($currentPage - 1) * $size, $size);

//     $paginate = new LengthAwarePaginator(
//         $currentPageItems,
//         count($data),
//         $size,
//         $currentPage,
//         ['path' => LengthAwarePaginator::resolveCurrentPath()]
//     );

//     return $paginate;
// }

function applyFilters($query, $params, $filterKeys = array()){
    $filters = isset($params['filters']) ? $params['filters'] : null;

    //var_dump($query->toSql());
    if($filters && count($filters) > 0 && $filterKeys && count($filterKeys) > 0){
        foreach($filters as $key => $filter_value){
            $values = explode(",", $filter_value);
            foreach ($values as $kval => $value) {
                if(isset($filterKeys[$key])){
                    if($filterKeys[$key][1]=='ilike' || $filterKeys[$key][1]=='like'){
                        $value = '%'.$value.'%';
                    }
                    
                    // var_dump($filter_value);
                    if($kval < 1) {
                        $query->where($filterKeys[$key][0], $filterKeys[$key][1], $value);                        
                    } else {
                        $query->orWhere($filterKeys[$key][0], $filterKeys[$key][1], $value);
                    }
                    
                   // var_dump($query->toSql());
                }
            }
            // if(isset($filterKeys[$key])){
            //     if($filterKeys[$key][1]=='ilike' || $filterKeys[$key][1]=='like'){
            //         $filter_value = '%'.$filter_value.'%';
            //     }
            //     $query->where($filterKeys[$key][0], $filterKeys[$key][1], $filter_value);
            // }
        }
    }

    return $query;
}

function applySort($query, $params, $sortKeys = array()){
    $sort = isset($params['sort']) ? $params['sort'] : null;
    $method = isset($params['method']) ? $params['method'] : 'asc';

    if($sort && $sortKeys && isset($sortKeys[$sort])){
        $query->orderBy($sortKeys[$sort],  $method);
    }

    //dd($query->get());
    return $query;
}

function applyPaginate($query, $params){
    $page = isset($params['page']) && $params['page'] ? $params['page'] : 1;
    $size = isset($params['size']) && $params['size'] ? $params['size'] : 15;
    $total = $query->count();

    $query->skip(intval(($page - 1) * $size))
        ->take(intval($size));

    return array(
        'page' => $page,
        'size' => $size,
        'total' => $total
    );
}


function convertData($data, $validator){
    $newData = array();
    foreach($data as $key => $val){
        $rules = explode("|", $validator[$key]);
        if(in_array('integer',$rules)){
            $val = intval($val);
        }
        $newData[$key] = $val;
    }

    return $newData;
}

function countByOrgId($org_ids, $model) {
    foreach($org_ids as $id) {
        
    dd($id);
        $test = $model::where('organisation._id', '=', $id)->count();
        dd($test);
    }
   //dd($test->toSql());
}

// function countProducts()
//     {
//         $cv = array_count_values(Volunteer::query()->pluck('_id')->toArray());


//         dd($cv);
//         return collect($cv)->map(function ($v, $k) {
//             return ['id' => $k, 'quantity' => $v];
//         })->values();
//     }

