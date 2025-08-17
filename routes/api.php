<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LicenseApiController; // Add this import

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('license')->name('api.license.')->group(function () {
    // Generate new license key
    Route::post('/generate', [LicenseApiController::class, 'generateLicenseKey'])->name('generate');
    // Generate license key only (without saving to database)
    Route::post('/generate-key', [LicenseApiController::class, 'generateKeyOnly'])->name('generateKeyOnly');
    
    
    // Get license info by company ID
    Route::get('/info', [LicenseApiController::class, 'getLicenseInfo'])->name('info');
    
    // List all licenses
    Route::get('/list', [LicenseApiController::class, 'listLicenses'])->name('list');
    
    // Delete license by company ID
    Route::delete('/delete', [LicenseApiController::class, 'deleteLicense'])->name('delete');
});