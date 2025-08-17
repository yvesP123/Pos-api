<?php

namespace App\Http\Middleware;

use App\Models\License;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class LicenseMiddleware
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
        $showLicenseWarning = false;
        $daysUntilExpiry = null;
        $licenseExpiryDate = null;

        if ($license) {
            $showLicenseWarning = $license->isAboutToExpire(3);
            if ($showLicenseWarning) {
                $now = Carbon::now()->startOfDay();
                $expiryDate = Carbon::parse($license->expiry_date)->startOfDay();
                $daysUntilExpiry = $now->diffInDays($expiryDate, false);
                $licenseExpiryDate = $expiryDate->format('M d, Y');
            }
        }

        // Share data with all views
        view()->share([
            'showLicenseWarning' => $showLicenseWarning,
            'daysUntilExpiry' => $daysUntilExpiry,
            'licenseExpiryDate' => $licenseExpiryDate
        ]);

        return $next($request);
    }
}