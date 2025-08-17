<?php

namespace App\Http\Controllers;

use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class LicenseController extends Controller
{
    public function showRequired()
    {
        return view('license.required');
    }

    public function showExpired()
    {
        $license = License::first();
        return view('license.expired', compact('license'));
    }

    public function showRenewalForm()
    {
        $license = License::first();
        return view('license.renew', compact('license'));
    }

    public function processRenewal(Request $request)
    {
        $request->validate([
            'duration_days' => 'required|integer|min:30|max:3650'
        ]);

        $license = License::first();
        
        if (!$license) {
            return redirect()->route('license.required')
                ->with('error', 'No license found to renew.');
        }

        $renewalDate = Carbon::now();
        $expiryDate = $renewalDate->copy()->addDays($request->duration_days);

        // Generate new encrypted key
        $newKeyString = sprintf("*%s* - *%s* - *%s*",
            str_pad($license->company_id, 6, '0', STR_PAD_LEFT), // Ensure 6-digit format
            $renewalDate->format('dmy'),
            $expiryDate->format('dmy')
        );

        // Update license
        $license->update([
            'encrypted_key' => Crypt::encrypt($newKeyString),
            'renewal_date' => $renewalDate,
            'expiry_date' => $expiryDate
        ]);

        return redirect('/')
            ->with('success', "License renewed successfully! New expiry date: {$expiryDate->format('d/m/Y')}");
    }

    public function validateManualKey(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string'
        ]);

        try {
            $inputKey = trim($request->license_key);
            $decryptedKey = '';
            
            // Try to decrypt if it's encrypted
            try {
                $decryptedKey = Crypt::decrypt($inputKey);
            } catch (\Exception $e) {
                $decryptedKey = $inputKey;
            }

             // Normalize key format
            $cleanKey = preg_replace('/\*/', '', $decryptedKey); // Remove asterisks
            $cleanKey = trim(preg_replace('/\s+/', ' ', $cleanKey)); // Normalize spaces
            
            // Define validation patterns - Updated to handle both 4-digit and 6-digit company IDs
             // Updated patterns to accept both formats
            $patterns = [
                '/^\*\s*(\d{4,6})\s*\*\s*-\s*\*\s*(\d{6})\s*\*\s*-\s*\*\s*(\d{6})\s*\*$/',
                '/^\*(\d{4,6})\*\s*-\s*\*(\d{6})\*\s*-\s*\*(\d{6})\*$/',
                '/^(\d{4,6})\s*-\s*(\d{6})\s*-\s*(\d{6})$/' // 👈 Allow 4-6 digits
            ];
            $cleanKey = preg_replace('/\s+/', ' ', $cleanKey); // Reduce multiple spaces
            $cleanKey = preg_replace('/(\*)\s*(\d)/', '$1$2', $cleanKey); // Remove spaces after *
            $cleanKey = preg_replace('/(\d)\s*(\*)/', '$1$2', $cleanKey); // Remove spaces before *
            
            $matches = null;
            $patternMatched = false;
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $cleanKey, $matches)) {
                    $patternMatched = true;
                    break;
                }
            }
            
            if (!$patternMatched) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid license key format. Expected format: *000001* - *180725* - *180825*',
                    'debug' => [
                        'input' => $inputKey,
                        'decrypted' => $decryptedKey,
                        'cleaned' => $cleanKey,
                        'matches' => $matches ?? []
                    ]
                ], 400);
            }

            // Extract components
            $companyId = $matches[1];
            // $companyId = str_pad($matches[1], 6, '0', STR_PAD_LEFT);
            $renewalDateStr = $matches[2];
            $expiryDateStr = $matches[3];
            
            // Normalize company ID to 6 digits
            $normalizedCompanyId = str_pad($companyId, 6, '0', STR_PAD_LEFT);
            
            // Format the key properly (always use 6-digit format)
            $formattedKey = sprintf("*%s* - *%s* - *%s*", $normalizedCompanyId, $renewalDateStr, $expiryDateStr);
            
            // Parse and validate dates
            try {
                $renewalYear = (int)substr($renewalDateStr, 4, 2);
                $renewalYear = $renewalYear <= 50 ? 2000 + $renewalYear : 1900 + $renewalYear;
                
                $expiryYear = (int)substr($expiryDateStr, 4, 2);
                $expiryYear = $expiryYear <= 50 ? 2000 + $expiryYear : 1900 + $expiryYear;
                
                $renewalDate = Carbon::createFromFormat('dmY', 
                    substr($renewalDateStr, 0, 2) . substr($renewalDateStr, 2, 2) . $renewalYear
                );
                
                $expiryDate = Carbon::createFromFormat('dmY', 
                    substr($expiryDateStr, 0, 2) . substr($expiryDateStr, 2, 2) . $expiryYear
                );
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format in license key'
                ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Key is valid',
                'formattedKey' => $formattedKey,
                'components' => [
                    'company_id' => $normalizedCompanyId,
                    'renewal_date' => $renewalDateStr,
                    'expiry_date' => $expiryDateStr,
                    'renewal_date_formatted' => $renewalDate->format('Y-m-d'),
                    'expiry_date_formatted' => $expiryDate->format('Y-m-d'),
                    'days_until_expiry' => Carbon::now()->diffInDays($expiryDate, false)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle manual key renewal
     */
    public function renewWithManualKey(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'company_id' => 'required|string'
        ]);

        try {
            $license = License::where('company_id', $request->company_id)->first();
            
            if (!$license) {
                return response()->json([
                    'success' => false,
                    'message' => 'License not found for this company'
                ], 404);
            }

            $inputKey = trim($request->license_key);
            $decryptedKey = '';
            
            // First, try to decrypt the key (in case it's an encrypted key from the system)
            try {
                $decryptedKey = Crypt::decrypt($inputKey);
            } catch (\Exception $e) {
                // If decryption fails, assume it's a plain formatted key
                $decryptedKey = $inputKey;
            }

            // Clean up the key format and normalize whitespace
            // Normalize key format
            $cleanKey = preg_replace('/\*/', '', $decryptedKey); // Remove asterisks
            $cleanKey = trim(preg_replace('/\s+/', ' ', $cleanKey)); // Normalize spaces
            
            // More flexible pattern matching to handle various formats
            // Pattern: *XXXX* - *DDMMYY* - *DDMMYY* or *XXXXXX* - *DDMMYY* - *DDMMYY*
               $patterns = [
                    '/^\*\s*(\d{4,6})\s*\*\s*-\s*\*\s*(\d{6})\s*\*\s*-\s*\*\s*(\d{6})\s*\*$/',
                    '/^\*(\d{4,6})\*\s*-\s*\*(\d{6})\*\s*-\s*\*(\d{6})\*$/',
                    '/^(\d{4,6})\s*-\s*(\d{6})\s*-\s*(\d{6})$/' // 👈 Allow 4-6 digits
                ];
            
            $matches = null;
            $patternMatched = false;
            
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $cleanKey, $matches)) {
                    $patternMatched = true;
                    break;
                }
            }
            $cleanKey = str_replace([' ', '-'], '', $cleanKey); // Remove all spaces and dashes
            $cleanKey = preg_replace('/(\d{6})(?=\d)/', '$1-', $cleanKey); // Add dashes back in correct positions
            $cleanKey = preg_replace('/(\*)(\d)/', '$1 $2', $cleanKey); // Add space after *
            $cleanKey = preg_replace('/(\d)(\*)/', '$1 $2', $cleanKey); // Add space before *

            if (!$patternMatched) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid license key format. Expected format: *000001* - *180725* - *180825*',
                    'debug' => [
                        'input' => $inputKey,
                        'decrypted' => $decryptedKey,
                        'cleaned' => $cleanKey
                    ]
                ], 400);
            }

            $keyCompanyId = $matches[1];
            $renewalDateStr = $matches[2];
            $expiryDateStr = $matches[3];
            
            // Normalize company IDs for comparison
            $normalizedKeyCompanyId = str_pad($keyCompanyId, 6, '0', STR_PAD_LEFT);
            $normalizedRequestCompanyId = str_pad($request->company_id, 6, '0', STR_PAD_LEFT);
            
            // Verify the company ID matches
            if ($normalizedKeyCompanyId !== $normalizedRequestCompanyId) {
                return response()->json([
                    'success' => false,
                    'message' => 'License key does not match your company ID',
                    'debug' => [
                        'key_company_id' => $normalizedKeyCompanyId,
                        'request_company_id' => $normalizedRequestCompanyId
                    ]
                ], 400);
            }

            // Parse dates (format: DDMMYY)
            try {
                // Convert DDMMYY to full year (assuming 20XX for years 00-50, 19XX for 51-99)
                $renewalYear = (int)substr($renewalDateStr, 4, 2);
                $renewalYear = $renewalYear <= 50 ? 2000 + $renewalYear : 1900 + $renewalYear;
                
                $expiryYear = (int)substr($expiryDateStr, 4, 2);
                $expiryYear = $expiryYear <= 50 ? 2000 + $expiryYear : 1900 + $expiryYear;
                
                $renewalDate = Carbon::createFromFormat('dmY', 
                    substr($renewalDateStr, 0, 2) . substr($renewalDateStr, 2, 2) . $renewalYear
                );
                
                $expiryDate = Carbon::createFromFormat('dmY', 
                    substr($expiryDateStr, 0, 2) . substr($expiryDateStr, 2, 2) . $expiryYear
                );
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid date format in license key'
                ], 400);
            }

            // Validate that expiry date is after renewal date
            if ($expiryDate->lte($renewalDate)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid license key: expiry date must be after renewal date'
                ], 400);
            }

            // Check if the key is already expired
            if ($expiryDate->lt(Carbon::now())) {
                return response()->json([
                    'success' => false,
                    'message' => 'This license key has already expired on ' . $expiryDate->format('d/m/Y')
                ], 400);
            }

            // Generate the properly formatted key string (same format as payment renewal)
            $newKeyString = sprintf("*%s* - *%s* - *%s*",
                str_pad($license->company_id, 6, '0', STR_PAD_LEFT),
                $renewalDate->format('dmy'),
                $expiryDate->format('dmy')
            );

            // Update the license with encrypted key (same as payment method)
            $license->update([
                'encrypted_key' => Crypt::encrypt($newKeyString),
                'renewal_date' => $renewalDate,
                'expiry_date' => $expiryDate
            ]);

            return response()->json([
                'success' => true,
                'message' => "License renewed successfully! New expiry date: {$expiryDate->format('d/m/Y')}"
            ]);

        } catch (\Exception $e) {
            \Log::error('Manual license renewal error', [
                'error' => $e->getMessage(),
                'company_id' => $request->company_id,
                'key_length' => strlen($request->license_key)
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error renewing license: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle payment-based renewal
     */
    public function renewWithPayment(Request $request)
    {
        $request->validate([
            'duration_days' => 'required|integer|min:30|max:3650',
            'payment_method' => 'required|in:card,momo'
        ]);

        try {
            $license = License::first();
            
            if (!$license) {
                return response()->json([
                    'success' => false,
                    'message' => 'No license found'
                ], 404);
            }

            // Calculate new dates
            $renewalDate = Carbon::now();
            $expiryDate = $renewalDate->copy()->addDays($request->duration_days);

            // Generate new key with 6-digit company ID format
            $newKeyString = sprintf("*%s* - *%s* - *%s*",
                str_pad($license->company_id, 6, '0', STR_PAD_LEFT),
                $renewalDate->format('dmy'),
                $expiryDate->format('dmy')
            );

            // Update license
            $license->update([
                'encrypted_key' => Crypt::encrypt($newKeyString),
                'renewal_date' => $renewalDate,
                'expiry_date' => $expiryDate
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment successful! License renewed until '.$expiryDate->format('d/m/Y')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: '.$e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payment (integrate with your payment provider)
     */
    private function processPayment(Request $request)
    {
        // This is a placeholder for payment processing logic
        // Integrate with your actual payment provider (Stripe, Flutterwave, etc.)
        
        try {
            if ($request->payment_method === 'card') {
                // Process card payment
                return $this->processCardPayment($request);
            } elseif ($request->payment_method === 'momo') {
                // Process mobile money payment
                return $this->processMobileMoneyPayment($request);
            }
            
            return ['success' => false, 'message' => 'Invalid payment method'];
            
        } catch (\Exception $e) {
            \Log::error('Payment processing error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Payment processing failed'];
        }
    }

    /**
     * Process card payment
     */
    private function processCardPayment(Request $request)
    {
        // Placeholder for card payment processing
        // In a real implementation, you would integrate with Stripe, Flutterwave, etc.
        
        // Simulate payment processing
        $cardNumber = str_replace(' ', '', $request->card_number);
        
        // Basic card validation (in real implementation, use proper payment gateway)
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
            return ['success' => false, 'message' => 'Invalid card number'];
        }
        
        // Simulate successful payment (replace with actual payment gateway integration)
        return [
            'success' => true,
            'reference' => 'CARD_' . uniqid(),
            'message' => 'Card payment processed successfully'
        ];
    }

    /**
     * Process mobile money payment
     */
    private function processMobileMoneyPayment(Request $request)
    {
        // Placeholder for mobile money payment processing
        // In a real implementation, you would integrate with MTN, Airtel, Tigo APIs
        
        $providers = [
            'mtn' => 'MTN Mobile Money',
            'airtel' => 'Airtel Money',
            'tigo' => 'Tigo Cash'
        ];
        
        $providerName = $providers[$request->momo_provider] ?? 'Unknown Provider';
        
        // Simulate payment processing (replace with actual mobile money API integration)
        return [
            'success' => true,
            'reference' => strtoupper($request->momo_provider) . '_' . uniqid(),
            'message' => "Payment processed successfully via {$providerName}"
        ];
    }

    /**
     * Check current license status (for debugging)
     */
    public function checkStatus()
    {
        $license = License::first();
        
        if (!$license) {
            return response()->json(['status' => 'No license found']);
        }

        return response()->json([
            'company_id' => $license->company_id,
            'renewal_date' => $license->renewal_date,
            'expiry_date' => $license->expiry_date,
            'is_expired' => $license->isExpired(),
            'is_in_grace_period' => $license->isInGracePeriod(),
            'grace_period_ended' => $license->isGracePeriodEnded(),
            'days_until_expiry' => Carbon::now()->diffInDays(Carbon::parse($license->expiry_date), false),
            'encrypted_key' => $license->encrypted_key
        ]);
    }
}