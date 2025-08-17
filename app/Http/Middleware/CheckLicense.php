<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\License;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckLicense
{
    public function handle(Request $request, Closure $next)
    {
        // Debug logging - remove this after testing
        \Log::info('CheckLicense middleware triggered for URL: ' . $request->fullUrl());
        \Log::info('Request path: ' . $request->path());
        \Log::info('Route name: ' . $request->route()?->getName());

        // Skip license check for license-related routes and license management
        if ($request->is('license*') || $request->is('license-management*')) {
            \Log::info('Skipping license check for: ' . $request->path());
            return $next($request);
        }

        \Log::info('Proceeding with license check for: ' . $request->path());

         $license = License::first();
    
    // If no license exists, redirect to license required page
    if (!$license) {
        return redirect()->route('license.required');
    }

    $now = Carbon::now()->startOfDay(); // Normalize to start of day
    $expiry = Carbon::parse($license->expiry_date)->startOfDay(); // Normalize to start of day
    $graceEnd = $expiry->copy()->addDays(2)->startOfDay(); // Normalize to start of day

    // Check if license has completely expired (past grace period)
    if ($license->isGracePeriodEnded()) {
        return redirect()->route('license.expired');
    }

    // Calculate days until expiry (date-only comparison)
    $daysUntilExpiry = $now->diffInDays($expiry, false);

    // Check if license is expired but still in grace period
    if ($license->isExpired() && $license->isInGracePeriod()) {
        $daysLeft = $now->diffInDays($graceEnd, false);
        session()->flash('license_critical', 
            "🚨 CRITICAL: Your license expired on {$expiry->format('d/m/Y')}. You have {$daysLeft} day(s) left in grace period!");
    }
    // Check if license is expiring within 3 days
    elseif ($daysUntilExpiry <= 3 && $daysUntilExpiry >= 0) {
        session()->flash('license_critical', 
            "🚨 URGENT: License expires in {$daysUntilExpiry} day(s) on {$expiry->format('d/m/Y')}. Generate new key immediately!");
    }
    // Check if license is expiring soon (within 7 days)
    elseif ($daysUntilExpiry <= 7 && $daysUntilExpiry >= 0) {
        session()->flash('license_warning', 
            "⚠️ WARNING: License expires in {$daysUntilExpiry} day(s) on {$expiry->format('d/m/Y')}. Please renew soon!");
    }

    return $next($request);
}
}