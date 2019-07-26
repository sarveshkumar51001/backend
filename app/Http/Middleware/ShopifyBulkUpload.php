<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use App\Library\Permission\Permission;
use Illuminate\Support\Facades\Auth;

class ShopifyBulkUpload
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
        
        $HasBulkUploadAccess = Auth::user()->hasPermission(Permission::BULKUPLOAD_ACCESS);

        if (!$HasBulkUploadAccess) {
            return redirect();
        }
        
        return $next($request);
    }
}
