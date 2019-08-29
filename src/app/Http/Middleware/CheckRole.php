<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */

    public function handle($request, Closure $next, ...$roles)
    {
        $paramId = $request->route()->parameter('id');
        $roleIds = config('roles.role');

        $allowedRoleIds = [];
        foreach ($roles as $role)
        {
            if(isset($roleIds[$role])) {
               $allowedRoleIds[] = $roleIds[$role];
            }
        }
        $allowedRoleIds = array_unique($allowedRoleIds); 

        if(\Auth::check()) {
            if(!is_null($paramId)) {
                $userOrgId = \Auth::user()->organisation['_id'];
                $userInstId = \Auth::user()->institution['_id'];
                if($paramId == $userOrgId || $paramId == $userInstId) {
                    return $next($request);
                } elseif(\Auth::user()->role == config('roles.role.dsu')) {
                    return $next($request);
                }
            } elseif (in_array(\Auth::user()->role, $allowedRoleIds)) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
