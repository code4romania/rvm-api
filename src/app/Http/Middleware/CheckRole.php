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
            if(in_array(\Auth::user()->role, $allowedRoleIds)) {
                return $next($request);
            }
        }

        return response()->json(['error' => 'Unauthorized'], 401);
    }
}
