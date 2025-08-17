<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    /**
     * Show admin login form
     */
    public function showAdminLogin()
    {
        // If already logged in as admin, redirect to license management
        if (Session::has('admin_logged_in') && Session::get('admin_logged_in') === true) {
            return redirect()->route('license-management.index');
        }

        return view('admin.login');
    }

    /**
     * Handle admin login
     */
    public function adminLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ]);

        // Find admin by username
        $admin = Admin::where('username', $request->username)
                     ->where('is_active', true)
                     ->first();

        // Check if admin exists and password is correct
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'username' => ['The provided credentials are incorrect or account is inactive.'],
            ]);
        }

        // Update last login timestamp
        $admin->updateLastLogin();

        // Set admin session
        Session::put('admin_logged_in', true);
        Session::put('admin_id', $admin->id);
        Session::put('admin_username', $admin->username);

        // Also authenticate as regular user if exists (for middleware compatibility)
        $user = \App\Models\User::where('level', 1)->first();
        if ($user) {
            Auth::login($user);
        }

        return redirect()->route('license-management.index')
                        ->with('success', 'Welcome to License Management Portal, ' . $admin->username . '!');
    }

    /**
     * Handle admin logout
     */
    public function adminLogout(Request $request)
    {
        // Clear admin session data
        Session::forget('admin_logged_in');
        Session::forget('admin_id');
        Session::forget('admin_username');

        // Logout regular user as well
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
                        ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Check if current session is admin authenticated
     */
    public static function isAdminAuthenticated()
    {
        return Session::has('admin_logged_in') && Session::get('admin_logged_in') === true;
    }

    /**
     * Get current admin data
     */
    public static function getCurrentAdmin()
    {
        if (self::isAdminAuthenticated()) {
            return Admin::find(Session::get('admin_id'));
        }
        return null;
    }
}