<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\LicenseKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class LicenseManagementController extends Controller
{
    public function index()
    {
        $currentLicense = License::first();
        $generatedKeys = LicenseKey::count();
        $activeKeys = LicenseKey::active()->count();
        $expiredKeys = LicenseKey::expired()->count();
        
        return view('license-management.dashboard', compact('currentLicense', 'generatedKeys', 'activeKeys', 'expiredKeys'));
    }

    public function viewKeys()
    {
        return view('license-management.view-keys');
    }

    public function data()
    {
        $keys = LicenseKey::select([
            'id', 'company_id', 'company_name', 'issue_date', 
            'expiry_date', 'status', 'generated_by', 'created_at'
        ])->get();

        return datatables()
            ->of($keys)
            ->addColumn('days_remaining', function($key) {
                return $key->daysRemaining();
            })
            ->addColumn('action', function($key) {
                return '
                    <div class="btn-group">
                        <a href="'.route('license-management.show', $key->id).'" class="btn btn-info btn-sm">
                            <i class="fa fa-eye"></i> View
                        </a>
                        <a href="'.route('license-management.regenerate', $key->id).'" class="btn btn-warning btn-sm"
                           onclick="return confirm(\'Are you sure you want to regenerate this key?\')">
                            <i class="fa fa-refresh"></i> Regenerate
                        </a>
                        <a href="'.route('license-management.extend', $key->id).'" class="btn btn-success btn-sm">
                            <i class="fa fa-calendar-plus-o"></i> Extend
                        </a>
                    </div>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function create()
    {
        return view('license-management.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'company_id' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'duration_days' => 'required|integer|min:1',
            'expiry_date' => 'nullable|date|after:today'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $expiryDate = $request->expiry_date 
            ? Carbon::parse($request->expiry_date)
            : now()->addDays($request->duration_days);

        $keyData = LicenseKey::generateKey(
            $request->company_id,
            $expiryDate->format('Y-m-d'),
            $request->duration_days
        );

        $licenseKey = LicenseKey::create([
            'company_id' => $request->company_id,
            'company_name' => $request->company_name,
            'encrypted_key' => $keyData['encrypted_key'],
            'plain_key' => $keyData['plain_key'],
            'issue_date' => now(),
            'expiry_date' => $expiryDate,
            'duration_days' => $request->duration_days,
            'generated_by' => Auth::user()->name,
            'metadata' => [
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]
        ]);

        return redirect()->route('license-management.show', $licenseKey->id)
            ->with('success', 'License key generated successfully!');
    }

    public function show($id)
    {
        $licenseKey = LicenseKey::findOrFail($id);
        return view('license-management.show', compact('licenseKey'));
    }

    // Manual renewal with provided key
    public function manualRenewal()
    {
        $currentLicense = License::first();
        return view('license-management.manual-renewal', compact('currentLicense'));
    }

    public function processManualRenewal(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Decrypt and validate the license key
            $decryptedData = Crypt::decryptString($request->license_key);
            $keyData = json_decode(base64_decode($decryptedData), true);

            if (!$keyData || !isset($keyData['company_id']) || !isset($keyData['expiry_date'])) {
                throw new \Exception('Invalid license key format');
            }

            // Update or create license
            $license = License::first();
            if ($license) {
                $license->update([
                    'company_id' => $keyData['company_id'],
                    'encrypted_key' => $request->license_key,
                    'renewal_date' => now(),
                    'expiry_date' => $keyData['expiry_date']
                ]);
            } else {
                License::create([
                    'company_id' => $keyData['company_id'],
                    'encrypted_key' => $request->license_key,
                    'renewal_date' => now(),
                    'expiry_date' => $keyData['expiry_date']
                ]);
            }

            return redirect()->route('license-management.index')
                ->with('success', 'License renewed successfully! New expiry date: ' . Carbon::parse($keyData['expiry_date'])->format('d/m/Y'));

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Invalid license key. Please check the key and try again.')
                ->withInput();
        }
    }

    public function currentLicense()
    {
        $license = License::first();
        return view('license-management.current-license', compact('license'));
    }

    public function search(Request $request)
    {
        if ($request->isMethod('post')) {
            $query = LicenseKey::query();

            if ($request->filled('company_id')) {
                $query->where('company_id', 'like', '%' . $request->company_id . '%');
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->where('issue_date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->where('issue_date', '<=', $request->date_to);
            }

            $keys = $query->orderBy('created_at', 'desc')->paginate(15);
            
            return view('license-management.search-results', compact('keys'));
        }

        return view('license-management.search');
    }

    public function regenerate($id)
    {
        $licenseKey = LicenseKey::findOrFail($id);
        
        $newKeyData = LicenseKey::generateKey(
            $licenseKey->company_id,
            $licenseKey->expiry_date->format('Y-m-d'),
            $licenseKey->duration_days
        );

        $licenseKey->update([
            'encrypted_key' => $newKeyData['encrypted_key'],
            'plain_key' => $newKeyData['plain_key'],
            'generated_by' => Auth::user()->name
        ]);

        return redirect()->route('license-management.show', $licenseKey->id)
            ->with('success', 'License key regenerated successfully!');
    }

    public function extend(Request $request, $id)
    {
        $licenseKey = LicenseKey::findOrFail($id);
        
        if ($request->isMethod('post')) {
            $validator = Validator::make($request->all(), [
                'extend_days' => 'required|integer|min:1'
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator);
            }

            $newExpiryDate = $licenseKey->expiry_date->addDays($request->extend_days);
            
            // Regenerate key with new expiry date
            $newKeyData = LicenseKey::generateKey(
                $licenseKey->company_id,
                $newExpiryDate->format('Y-m-d'),
                $licenseKey->duration_days + $request->extend_days
            );

            $licenseKey->update([
                'encrypted_key' => $newKeyData['encrypted_key'],
                'plain_key' => $newKeyData['plain_key'],
                'expiry_date' => $newExpiryDate,
                'duration_days' => $licenseKey->duration_days + $request->extend_days,
                'status' => 'active',
                'generated_by' => Auth::user()->name
            ]);

            return redirect()->route('license-management.show', $licenseKey->id)
                ->with('success', 'License extended successfully!');
        }

        return view('license-management.extend', compact('licenseKey'));
    }

    public function exportCsv()
    {
        $keys = LicenseKey::all();
        
        $filename = 'license_keys_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($keys) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'ID', 'Company ID', 'Company Name', 'Issue Date', 
                'Expiry Date', 'Duration (Days)', 'Status', 'Generated By'
            ]);

            foreach ($keys as $key) {
                fputcsv($file, [
                    $key->id,
                    $key->company_id,
                    $key->company_name,
                    $key->issue_date->format('Y-m-d'),
                    $key->expiry_date->format('Y-m-d'),
                    $key->duration_days,
                    $key->status,
                    $key->generated_by
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}