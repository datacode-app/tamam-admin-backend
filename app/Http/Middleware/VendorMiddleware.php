<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\CentralLogics\Helpers;

class VendorMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Helper function to get proper vendor login URL
        $getVendorLoginUrl = function() {
            try {
                $loginUrl = Helpers::get_login_url('store_login_url');
                return $loginUrl ? route('login', $loginUrl) : route('login', 'vendor');
            } catch (\Exception $e) {
                // Fallback to direct vendor login if DataSettings fail
                return route('login', 'vendor');
            }
        };

        if (Auth::guard('vendor')->check()) {
            if(!auth('vendor')->user()->status)
            {
                auth()->guard('vendor')->logout();
                return redirect($getVendorLoginUrl());
            }
            return $next($request);
        }
        else if (Auth::guard('vendor_employee')->check()) {
            if(Auth::guard('vendor_employee')->user()->is_logged_in == 0)
            {
                auth()->guard('vendor_employee')->logout();
                return redirect($getVendorLoginUrl());
            }
            if(auth('vendor_employee')->user()->store && !auth('vendor_employee')->user()->store->status)
            {
                auth()->guard('vendor_employee')->logout();
                return redirect($getVendorLoginUrl());
            }
            return $next($request);
        }
        
        // Redirect unauthenticated users to vendor login
        return redirect($getVendorLoginUrl());
    }
}
