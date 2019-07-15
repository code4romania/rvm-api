<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // $roles = config('roles.role');

        // if (!$user->role == $roles['dsu']) {
        //     $response = "Permission denied";
        //     return response($response, 401);
        // } else {

        // }

        return $next($request);
    }
}
