<?php

namespace App\Http\Middleware;

use Closure;

class Permission
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @param $permission
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        $userPermissions = !empty(\Auth::user()->permissions) ? \Auth::user()->permissions : [];

        if(! in_array($permission, $userPermissions)) {
            return abort(403);
        }

        return $next($request);
    }
}
