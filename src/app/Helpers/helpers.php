<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\Validator;
use Illuminate\Validation\ValidationRuleParser;
use App\Volunteer;
use App\Institution;
use App\Organisation;
/**
 * Pagination helper
 *
 * @param string $data Items to paginate
 * @return array of $data 
 */

function applyFilters($query, $params, $filterKeys = array()){
    $filters = isset($params['filters']) ? $params['filters'] : null;

    if($filters && count($filters) > 0 && $filterKeys && count($filterKeys) > 0){
        foreach($filters as $key => $filter_value){
            if(is_null($filter_value)){
                continue;
            }
            $values = explode(",", $filter_value);
            foreach ($values as $kval => $value) {
                if(isset($filterKeys[$key])){
                    if($filterKeys[$key][1]=='ilike' || $filterKeys[$key][1]=='like'){
                        $value = '%'.$value.'%';
                    }
                    if($kval < 1) {
                        $query->where($filterKeys[$key][0], $filterKeys[$key][1], $value);                        
                    } else {
                        $query->orWhere($filterKeys[$key][0], $filterKeys[$key][1], $value);
                    }
                }
            }
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
        if(is_string( $validator[$key])){
            $rules = explode("|", $validator[$key]);
            if(in_array('integer',$rules)){
                $val = intval($val);
            }
            $newData[$key] = $val;
            //Insert slug after name
            if($key === 'name') {
                $newData['slug'] = removeDiacritics($data['name']);
            }
        }
    }

    return $newData;
}

function countByOrgId($org_ids, $model) {
    foreach($org_ids as $id) {
        
    dd($id);
        $test = $model::where('organisation._id', '=', $id)->count();
        dd($test);
    }
}

function removeDiacritics($post_name) {
    $diacritics_array = array( 
        'Š'=>'S', 'š'=>'s', 
        'Ž'=>'Z', 'ž'=>'z',
        'À'=>'A', 'Á'=>'A',
        'Ã'=>'A', 'Ä'=>'A',
        'Å'=>'A', 'Æ'=>'A',
        'Ç'=>'C', 'È'=>'E',
        'É'=>'E', 'Ê'=>'E',
        'Ë'=>'E', 'Ì'=>'I',
        'Í'=>'I', 'Î'=>'I', 
        'Ï'=>'I', 'Ñ'=>'N',
        'Ò'=>'O', 'Ó'=>'O',
        'Ô'=>'O', 'Õ'=>'O',
        'Ö'=>'O', 'Ø'=>'O',
        'Ù'=>'U', 'Ú'=>'U',
        'Û'=>'U', 'Ü'=>'U',
        'Ý'=>'Y', 'Þ'=>'B',
        'ß'=>'Ss', 'à'=>'a',
        'á'=>'a', 'ã'=>'a',
        'ä'=>'a', 'å'=>'a',
        'æ'=>'a', 'ç'=>'c',
        'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e',
        'ì'=>'i', 'í'=>'i',
        'î'=>'i', 'ï'=>'i',
        'ð'=>'o', 'ñ'=>'n',
        'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o',
        'ö'=>'o', 'ø'=>'o',
        'ù'=>'u', 'ú'=>'u',
        'û'=>'u', 'ý'=>'y',
        'þ'=>'b', 'ÿ'=>'y',
        'ă'=>'a','Ă'=>'A',
        'â'=>'a','Â'=>'A',
        'ș'=>'s','ş'=>'s',
        'Ș'=>'S','Ş'=>'S',
        'ț'=>'t', 'ţ'=>'t',
        'Ț'=>'T', 'Ţ'=>'T'
    );

    $post_name = strtr( $post_name, $diacritics_array );

    return $post_name;
}

function allowResourceAccess($resource) {
    $r = is_array($resource) ? $resource : $resource->toArray();

    if(isRole('dsu')) {
        return true;
    }
    if(isRole('institution') && (!isset($r['institution']) || $r['institution']['_id'] != getAffiliationId())) {
        isDenied();
    }
    if(isRole('ngo') && (!isset($r['organisation']) ||  $r['organisation']['_id'] != getAffiliationId())) {
        isDenied();
    }

    return true;
}

function isRole($role, $user = null) {
    $user = $user ? $user : \Auth::user();

    $roleIds = config('roles.role');
    $roleId = $roleIds[$role];

    if($roleId === $user->role && $role=='institution' && (!isset($user->institution) || !$user->institution || !isset($user->institution['_id']))) return false;
    if($roleId === $user->role && $role=='ngo' && (!isset($user->organisation) || !$user->organisation || !isset($user->organisation['_id']))) return false;
    if($roleId === $user->role) return true;

    return false;
}

// Returns Institution id Organization id of the admin
function getAffiliationId() {
    $user = \Auth::user();
    if(isRole('institution')) {
        return $user->institution['_id'];
    }
    if(isRole('ngo')){
        return $user->organisation['_id'];
    }

    return null;
}

function isDenied() {
    abort(403, 'Permission denied');
}

function setAffiliate($data) {
    $affiliate= null;

    if(isRole('institution')) {
        $affiliate = Institution::where('_id', getAffiliationId())->first();
        if(is_array($data)) {
            $data['institution'] = array('_id' => $affiliate->_id, 'name' => $affiliate->name);
        } else if(is_object($data)) {
            $data->institution = array('_id' => $affiliate->_id, 'name' => $affiliate->name);
        }
    }

    if(isRole('ngo')) {
        $affiliate = Organisation::where('_id', getAffiliationId())->first();
        if(is_array($data)) {
            $data['organisation'] = array('_id' => $affiliate->_id, 'name' => $affiliate->name);
        } else if(is_object($data)) {
            $data->organisation = array('_id' => $affiliate->_id, 'name' => $affiliate->name);
        }
    } 
    
    return $data;
}