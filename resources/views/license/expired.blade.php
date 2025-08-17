{{-- resources/views/license/expired.blade.php --}}
@extends('layouts.license')

@section('content')
<div class="min-h-screen bg-red-50 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-red-600 mb-2">LICENSE EXPIRED</h1>
            <p class="text-gray-600 mb-4">Your software license has expired and access has been restricted.</p>
            
            @if($license)
                <div class="bg-red-50 p-4 rounded-lg mb-4 text-sm">
                    <p><strong>Company ID:</strong> {{ $license->company_id }}</p>
                    <p><strong>Expired on:</strong> {{ \Carbon\Carbon::parse($license->expiry_date)->format('d/m/Y') }}</p>
                    <p><strong>Days overdue:</strong> {{ \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($license->expiry_date)) }} days</p>
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <a href="{{ route('license.renew') }}" 
               class="w-full bg-red-600 text-white py-3 px-4 rounded-lg hover:bg-red-700 transition duration-200 inline-block font-semibold">
                RENEW LICENSE NOW
            </a>
            
            <p class="text-sm text-gray-500">
                Contact support if you need assistance with license renewal.
            </p>
        </div>
    </div>
</div>
@endsection

{{-- resources/views/license/renew.blade.php --}}
@extends('layouts.license')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8">
        <div class="text-center mb-6">
            <div class="mx-auto w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Renew License</h1>
            <p class="text-gray-600">Extend your software license to continue using all features.</p>
        </div>

        @if($license)
            <div class="bg-yellow-50 p-4 rounded-lg mb-6 text-sm">
                <p><strong>Company ID:</strong> {{ $license->company_id }}</p>
                <p><strong>Current Expiry:</strong> {{ \Carbon\Carbon::parse($license->expiry_date)->format('d/m/Y') }}</p>
                @if($license->isExpired())
                    <p class="text-red-600 font-semibold">Status: EXPIRED</p>
                @else
                    <p class="text-green-600 font-semibold">Status: Active</p>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ route('license.renew') }}">
            @csrf
            
            <div class="mb-6">
                <label for="duration_days" class="block text-sm font-medium text-gray-700 mb-2">
                    License Duration (Days)
                </label>
                <select name="duration_days" id="duration_days" 
                        class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    <option value="">Select Duration</option>
                    <option value="30">30 Days (1 Month)</option>
                    <option value="90">90 Days (3 Months)</option>
                    <option value="180">180 Days (6 Months)</option>
                    <option value="365">365 Days (1 Year)</option>
                    <option value="730">730 Days (2 Years)</option>
                </select>
                @error('duration_days')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg hover:bg-blue-700 transition duration-200 font-semibold">
                RENEW LICENSE
            </button>
        </form>

        <div class="mt-6 text-center">
            <a href="/" class="text-sm text-gray-500 hover:text-gray-700">
                ← Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection

{{-- resources/views/license/required.blade.php --}}
@extends('layouts.license')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <div class="mx-auto w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 012-2h2v-2a2 2 0 012-2h2l1.257-2.257A6 6 0 0119 9z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">License Required</h1>
            <p class="text-gray-600 mb-6">No valid license found. Please contact support to obtain a license key.</p>
        </div>

        <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-lg text-sm text-left">
                <h3 class="font-semibold mb-2">Contact Support:</h3>
                <p>Email: admin@bedaconsult.com/</p>
                <p>Phone: +250......</p>
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Add this to your main layout file (resources/views/layouts/app.blade.php) --}}
{{-- Add this in the <body> tag, preferably after opening <body> --}}
<script>
// License notification alerts
@if(session('license_warning'))
    <div class="license-alert bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3 flex-grow">
                <p class="text-sm text-yellow-700">{{ session('license_warning') }}</p>
            </div>
            <div class="ml-4">
                <a href="{{ route('license.renew') }}" class="bg-yellow-600 text-white px-3 py-1 rounded text-sm hover:bg-yellow-700">RENEW</a>
            </div>
        </div>
    </div>
@endif

@if(session('license_critical'))
    <div class="license-alert bg-red-100 border-l-4 border-red-500 p-4 mb-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3 flex-grow">
                <p class="text-sm text-red-700 font-medium">{{ session('license_critical') }}</p>
            </div>
            <div class="ml-4">
                <a href="{{ route('license.renew') }}" class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 font-semibold">RENEW NOW</a>
            </div>
        </div>
    </div>
@endif
</script>