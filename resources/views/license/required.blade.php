@extends('layouts.license')

@section('content')
<div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-8 text-center">
        <div class="mb-6">
            <div class="mx-auto w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2a2 2 0 01-2 2H5a2 2 0 01-2-2v-2a2 2 0 012-2h2v-2a2 2 0 012-2h2l1.257-2.257A6 6 0 0119 9z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800 mb-2">License Required</h1>
            <p class="text-gray-600 mb-6">No valid license found in the system. Please contact support to obtain a license key.</p>
        </div>

        <div class="space-y-4">
            <div class="bg-gray-50 p-4 rounded-lg text-sm text-left">
                <h3 class="font-semibold mb-2 text-gray-800">What to do:</h3>
                <ul class="space-y-1 text-gray-600">
                    <li>• Contact your system administrator</li>
                    <li>• Request a valid license key</li>
                    <li>• Ensure license is properly installed</li>
                </ul>
            </div>
            
            <div class="bg-blue-50 p-4 rounded-lg text-sm text-left">
                <h3 class="font-semibold mb-2 text-blue-800">Contact Support:</h3>
                <p class="text-blue-600">Email: admin@bedaconsult.com</p>
                <p class="text-blue-600">Phone: +250........</p>
            </div>
        </div>
    </div>
</div>
@endsection