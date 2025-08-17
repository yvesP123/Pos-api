<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\License;
use Carbon\Carbon;

class RestrictExpiredFeatures
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
     {
        $license = License::first();
        $expiry = Carbon::parse($license->expiry_date);
        $today = Carbon::today();

        // Block critical features after expiry
        if ($today->gt($expiry)) {
            if ($request->routeIs('exports.*') || $request->routeIs('data.save.*')) {
                abort(403, 'Feature disabled. License expired!');
            }
        }
    

        return $next($request);
    }
}
