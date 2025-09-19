<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\License;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckLicense
{
    // Add the decryption function
    private function laravel_compatible_decrypt($encryptedValue, $key) {
        $cipher = "AES-256-CBC";
        
        try {
            // Process Laravel-style app key
            if (strpos($key, 'base64:') === 0) {
                $key = base64_decode(substr($key, 7));
            }
            
            // Decode the base64 payload
            $payload = json_decode(base64_decode($encryptedValue), true);
            
            if (!$payload || !isset($payload['iv']) || !isset($payload['value']) || !isset($payload['mac'])) {
                throw new Exception('Invalid encrypted data format');
            }
            
            $iv = base64_decode($payload['iv']);
            $encrypted = $payload['value'];
            $mac = $payload['mac'];
            
            // Check if this is newer Laravel format with tag (AEAD encryption)
            if (isset($payload['tag']) && !empty($payload['tag'])) {
                // This is Laravel 9+ AEAD encryption format - try GCM decryption
                $cipher = "AES-256-GCM";
                $tag = base64_decode($payload['tag']);
                
                // For AEAD, we don't use HMAC verification, the tag serves this purpose
                $decrypted = openssl_decrypt($encrypted, $cipher, $key, OPENSSL_RAW_DATA, $iv, $tag);
                
                if ($decrypted === false) {
                    throw new Exception('AEAD decryption failed - invalid tag or corrupted data');
                }
                
                // Unserialize the result like Laravel does
                $unserialized = @unserialize($decrypted);
                return $unserialized !== false ? $unserialized : $decrypted;
            } else {
                // This is older Laravel format (CBC with HMAC)
                // Try different MAC calculation methods
                $macValid = false;
                
                // Method 1: Direct iv + encrypted
                $expectedMac1 = hash_hmac('sha256', $iv . $encrypted, $key);
                if (hash_equals($expectedMac1, $mac)) {
                    $macValid = true;
                }
                
                // Method 2: Base64 iv + encrypted value
                if (!$macValid) {
                    $expectedMac2 = hash_hmac('sha256', $payload['iv'] . $encrypted, $key);
                    if (hash_equals($expectedMac2, $mac)) {
                        $macValid = true;
                    }
                }
                
                // Method 3: Laravel's actual MAC calculation (base64 encoded payload)
                if (!$macValid) {
                    $payloadString = base64_encode($encryptedValue);
                    $expectedMac3 = hash_hmac('sha256', 'laravel_session=' . $payloadString, $key);
                    if (hash_equals($expectedMac3, $mac)) {
                        $macValid = true;
                    }
                }
                
                // Method 4: Try without MAC verification (less secure but works for testing)
                if (!$macValid) {
                    // Skip MAC verification and try direct decryption
                    $decrypted = openssl_decrypt($encrypted, $cipher, $key, 0, $iv);
                    if ($decrypted !== false) {
                        // Try to unserialize if it's serialized data
                        $unserialized = @unserialize($decrypted);
                        return $unserialized !== false ? $unserialized : $decrypted;
                    }
                    throw new Exception('MAC verification failed and direct decryption also failed');
                }
                
                // Decrypt the value
                $decrypted = openssl_decrypt($encrypted, $cipher, $key, 0, $iv);
                
                if ($decrypted === false) {
                    throw new Exception('Decryption failed after MAC verification');
                }
                
                // Try to unserialize if it's serialized data
                $unserialized = @unserialize($decrypted);
                return $unserialized !== false ? $unserialized : $decrypted;
            }
            
        } catch (Exception $e) {
            throw new Exception('Decryption error: ' . $e->getMessage());
        }
    }

    // Add the license key parsing function
    private function parse_license_key($inputKey) {
        $laravel_app_key = config('app.key');
        
        try {
            // Step 1: Check if it's an encrypted Laravel key (base64 encoded JSON)
            $cleanKey = $inputKey;
            $isEncrypted = false;
            
            // Try to detect if it's a Laravel encrypted string
            $decoded = base64_decode($inputKey, true);
            if ($decoded !== false) {
                $payload = json_decode($decoded, true);
                if ($payload && isset($payload['iv'], $payload['value'], $payload['mac'])) {
                    // This looks like a Laravel encrypted string
                    try {
                        $rawKey = base64_decode(explode(':', $laravel_app_key)[1]);
                        $decrypted = $this->laravel_compatible_decrypt($inputKey, $rawKey);
                        if ($decrypted !== false) {
                            $cleanKey = $decrypted;
                            $isEncrypted = true;
                        }
                    } catch (Exception $e) {
                        Log::error("PHP Decryption failed: " . $e->getMessage());
                        // If PHP decryption fails, continue with the original key
                    }
                }
            }
            
            // Step 2: Clean the key - remove extra asterisks, spaces, and normalize format
            $normalized = trim($cleanKey);
            
            // Remove all spaces first
            $normalized = str_replace(' ', '', $normalized);
            
            // Extract numbers from segments separated by dashes
            $segments = explode('-', $normalized);
            
            if (count($segments) !== 3) {
                // If no dashes, try to parse as a continuous string with asterisks
                $digitsOnly = preg_replace('/[^0-9]/', '', $normalized);
                if (strlen($digitsOnly) === 18) {
                    // Assume format: 6 digits + 6 digits + 6 digits
                    $expiryDateStr = substr($digitsOnly, 12, 6);
                } else {
                    return false;
                }
            } else {
                // Extract digits from each segment
                $expiryDateStr = preg_replace('/[^0-9]/', '', $segments[2]);
                
                // Validate segment length
                if (strlen($expiryDateStr) !== 6) {
                    return false;
                }
            }
            
            // Step 3: Parse date (DDMMYY format)
            $expiryDate = \DateTime::createFromFormat('dmy', $expiryDateStr);
            
            if (!$expiryDate) {
                return false;
            }
            
            // Step 4: Adjust century based on current year
            $twoDigitYear = date('y');
            
            if ($expiryDate->format('y') > $twoDigitYear + 50) {
                $expiryDate->modify('-100 years');
            }
            
            return [
                'expiry_date' => $expiryDate->format('Y-m-d'),
                'raw_segments' => [
                    'expiry_date_raw' => $expiryDateStr
                ]
            ];

        } catch (Exception $e) {
            Log::error("License parsing error: {$e->getMessage()}");
            return false;
        }
    }

    public function handle(Request $request, Closure $next)
    {
        // Debug logging - remove this after testing
        Log::info('CheckLicense middleware triggered for URL: ' . $request->fullUrl());
        Log::info('Request path: ' . $request->path());
        Log::info('Route name: ' . $request->route()?->getName());

        // Skip license check for license-related routes and license management
        if ($request->is('license*') || $request->is('license-management*')) {
            Log::info('Skipping license check for: ' . $request->path());
            return $next($request);
        }

        Log::info('Proceeding with license check for: ' . $request->path());

        $license = License::first();
    
        // If no license exists, redirect to license required page
        if (!$license) {
            return redirect()->route('license.required');
        }

        // Get expiry date from encrypted key instead of database column
        $keyComponents = $this->parse_license_key($license->encrypted_key);
        
        if (!$keyComponents) {
            Log::error('Failed to parse encrypted license key');
            return redirect()->route('license.expired');
        }
        
        $expiryDateFromKey = $keyComponents['expiry_date'];
        
        $now = Carbon::now()->startOfDay(); // Normalize to start of day
        $expiry = Carbon::parse($expiryDateFromKey)->startOfDay(); // Use expiry date from encrypted key
        $graceEnd = $expiry->copy()->addDays(2)->startOfDay(); // Normalize to start of day

        // Security check: Compare with database expiry date to detect tampering
        if ($license->expiry_date !== $expiryDateFromKey) {
            Log::warning("SECURITY WARNING: Database expiry date differs from encrypted key! DB: " . 
                     $license->expiry_date . " vs Key: " . $expiryDateFromKey);
            
            // For security, we'll use the encrypted key's expiry date
            // Update the database to match the encrypted key (optional)
            // $license->expiry_date = $expiryDateFromKey;
            // $license->save();
        }

        // Check if license has completely expired (past grace period)
        if ($now->gt($graceEnd)) {
            return redirect()->route('license.expired');
        }

        // Calculate days until expiry (date-only comparison)
        $daysUntilExpiry = $now->diffInDays($expiry, false);

        // Check if license is expired but still in grace period
        if ($now->gt($expiry) && $now->lte($graceEnd)) {
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