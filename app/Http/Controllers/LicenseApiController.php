<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class LicenseApiController extends Controller
{
    /**
     * Generate a new license key via API
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateLicenseKey(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'company_id' => 'required|string|max:10',
                'renewal_date' => 'required|date|date_format:Y-m-d',
                'duration_days' => 'required|integer|min:1|max:3650'
            ]);

            $companyId = $request->company_id;
            $renewalDate = Carbon::createFromFormat('Y-m-d', $request->renewal_date);
            $durationDays = (int) $request->duration_days;
            $expiryDate = $renewalDate->copy()->addDays($durationDays);

            // Normalize company ID to 6 digits
            $normalizedCompanyId = str_pad($companyId, 6, '0', STR_PAD_LEFT);

            // Build key string in the same format as the command
            $keyString = sprintf("*%s* - *%s* - *%s*",
                $normalizedCompanyId,
                $renewalDate->format('dmy'),
                $expiryDate->format('dmy')
            );

            // Encrypt the key
            $encryptedKey = Crypt::encrypt($keyString);

            // Store in database (delete existing first)
            License::where('company_id', $normalizedCompanyId)->delete();
            
            $license = License::create([
                'company_id' => $normalizedCompanyId,
                'encrypted_key' => $encryptedKey,
                'renewal_date' => $renewalDate,
                'expiry_date' => $expiryDate
            ]);

            // Calculate days until expiry
            $daysUntilExpiry = Carbon::now()->diffInDays($expiryDate, false);
            $isExpired = Carbon::now()->gt($expiryDate);

            return response()->json([
                'success' => true,
                'message' => 'License generated successfully!',
                'data' => [
                    'license_id' => $license->id,
                    'company_id' => $normalizedCompanyId,
                    'original_company_id' => $companyId,
                    'renewal_date' => $renewalDate->format('Y-m-d'),
                    'expiry_date' => $expiryDate->format('Y-m-d'),
                    'duration_days' => $durationDays,
                    'days_until_expiry' => $daysUntilExpiry,
                    'is_expired' => $isExpired,
                    'formatted_key' => $keyString,
                    'encrypted_key' => $encryptedKey
                ],
                'warnings' => $isExpired ? ['This license is already EXPIRED!'] : []
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating license: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Generate a license key without storing in database
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateKeyOnly(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'company_id' => 'required|string|max:10',
                'renewal_date' => 'required|date|date_format:Y-m-d',
                'duration_days' => 'required|integer|min:1|max:3650'
            ]);

            $companyId = $request->company_id;
            $renewalDate = Carbon::createFromFormat('Y-m-d', $request->renewal_date);
            $durationDays = (int) $request->duration_days;
            $expiryDate = $renewalDate->copy()->addDays($durationDays);

            // Normalize company ID to 6 digits
            $normalizedCompanyId = str_pad($companyId, 6, '0', STR_PAD_LEFT);

            // Build key string in the same format as the command
            $keyString = sprintf("*%s* - *%s* - *%s*",
                $normalizedCompanyId,
                $renewalDate->format('dmy'),
                $expiryDate->format('dmy')
            );

            // Encrypt the key
            $encryptedKey = Crypt::encrypt($keyString);

            // Calculate days until expiry
            $daysUntilExpiry = Carbon::now()->diffInDays($expiryDate, false);
            $isExpired = Carbon::now()->gt($expiryDate);

            return response()->json([
                'success' => true,
                'message' => 'License key generated successfully (not stored in database)!',
                'data' => [
                    'company_id' => $normalizedCompanyId,
                    'original_company_id' => $companyId,
                    'renewal_date' => $renewalDate->format('Y-m-d'),
                    'expiry_date' => $expiryDate->format('Y-m-d'),
                    'duration_days' => $durationDays,
                    'days_until_expiry' => $daysUntilExpiry,
                    'is_expired' => $isExpired,
                    'formatted_key' => $keyString,
                    'encrypted_key' => $encryptedKey
                ],
                'warnings' => $isExpired ? ['This license is already EXPIRED!'] : []
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating license key: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get license information by company ID
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLicenseInfo(Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|string|max:10'
            ]);

            $companyId = str_pad($request->company_id, 6, '0', STR_PAD_LEFT);
            $license = License::where('company_id', $companyId)->first();

            if (!$license) {
                return response()->json([
                    'success' => false,
                    'message' => 'License not found for company ID: ' . $request->company_id
                ], 404);
            }

            $now = Carbon::now();
            $expiryDate = Carbon::parse($license->expiry_date);
            $renewalDate = Carbon::parse($license->renewal_date);

            // Decrypt the key for display
            $decryptedKey = '';
            try {
                $decryptedKey = Crypt::decrypt($license->encrypted_key);
            } catch (\Exception $e) {
                $decryptedKey = 'Unable to decrypt key';
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'license_id' => $license->id,
                    'company_id' => $license->company_id,
                    'renewal_date' => $renewalDate->format('Y-m-d'),
                    'expiry_date' => $expiryDate->format('Y-m-d'),
                    'days_until_expiry' => $now->diffInDays($expiryDate, false),
                    'is_expired' => $license->isExpired(),
                    'is_in_grace_period' => $license->isInGracePeriod(),
                    'grace_period_ended' => $license->isGracePeriodEnded(),
                    'decrypted_key' => $decryptedKey,
                    'encrypted_key' => $license->encrypted_key,
                    'created_at' => $license->created_at,
                    'updated_at' => $license->updated_at
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving license: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * List all licenses
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function listLicenses()
    {
        try {
            $licenses = License::all()->map(function ($license) {
                $now = Carbon::now();
                $expiryDate = Carbon::parse($license->expiry_date);
                
                // Decrypt key safely
                $decryptedKey = '';
                try {
                    $decryptedKey = Crypt::decrypt($license->encrypted_key);
                } catch (\Exception $e) {
                    $decryptedKey = 'Unable to decrypt';
                }

                return [
                    'license_id' => $license->id,
                    'company_id' => $license->company_id,
                    'renewal_date' => Carbon::parse($license->renewal_date)->format('Y-m-d'),
                    'expiry_date' => $expiryDate->format('Y-m-d'),
                    'days_until_expiry' => $now->diffInDays($expiryDate, false),
                    'status' => $license->isGracePeriodEnded() ? 'EXPIRED' : 
                               ($license->isExpired() ? 'GRACE_PERIOD' : 'ACTIVE'),
                    'decrypted_key' => $decryptedKey
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Licenses retrieved successfully',
                'data' => $licenses,
                'total' => $licenses->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving licenses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a license by company ID
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteLicense(Request $request)
    {
        try {
            $request->validate([
                'company_id' => 'required|string|max:10'
            ]);

            $companyId = str_pad($request->company_id, 6, '0', STR_PAD_LEFT);
            $deleted = License::where('company_id', $companyId)->delete();

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'License not found for company ID: ' . $request->company_id
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'License deleted successfully',
                'data' => [
                    'company_id' => $request->company_id,
                    'normalized_company_id' => $companyId
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting license: ' . $e->getMessage()
            ], 500);
        }
    }
}