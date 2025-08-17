<?php

namespace App\Http\Middleware;

use App\Http\Controllers\AdminAuthController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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
        // Check if accessing license management routes
        if ($request->is('license-management*') || $request->routeIs('license-management.*')) {
            // Check admin authentication
            if (!AdminAuthController::isAdminAuthenticated()) {
                return redirect()->route('admin.login')
                               ->with('error', 'Please login with administrator credentials to access the License Management Portal.');
            }

            // Verify admin account is still active
            $admin = AdminAuthController::getCurrentAdmin();
            if (!$admin || !$admin->isActive()) {
                // Clear invalid session
                session()->forget(['admin_logged_in', 'admin_id', 'admin_username']);
                
                return redirect()->route('admin.login')
                               ->with('error', 'Administrator account is inactive. Please contact system administrator.');
            }

            return $next($request);
        }

        // For other routes, check regular user authentication
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Check if user has admin privileges (level 1)
        if (Auth::user()->level != 1) {
            Auth::logout();
            
            return redirect()->route('login')
                           ->with('error', 'Access denied. Administrator privileges required.');
        }

        return $next($request);
    }
}