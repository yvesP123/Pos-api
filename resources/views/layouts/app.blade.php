<?php
// Update your resources/views/layouts/app.blade.php
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/app.css') }}">

        @livewireStyles

        <!-- Scripts -->
        <script src="{{ asset('js/app.js') }}" defer></script>
        
        <style>
            .license-alert {
                position: sticky;
                top: 0;
                z-index: 50;
                animation: slideDown 0.3s ease-out;
            }
            .alert.rounded-0 {
                border-radius: 0 !important;
                position: sticky;
                top: 0;
                z-index: 9999;
            }
            
            @keyframes slideDown {
                from {
                    transform: translateY(-100%);
                    opacity: 0;
                }
                to {
                    transform: translateY(0);
                    opacity: 1;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <x-jet-banner />
        
        <!-- License Status Alerts - Show at the very top -->
         @if(session('license_warning'))
        <div class="alert alert-warning text-center mb-0 rounded-0">
            <i class="fa fa-exclamation-circle"></i> 
            {{ session('license_warning') }}
        </div>
    @endif

         @if(session('license_critical'))
        <div class="alert alert-danger text-center mb-0 rounded-0">
            <i class="fa fa-exclamation-triangle"></i> 
            {{ session('license_critical') }}
        </div>
    @endif

        @if(session('success'))
            <div class="license-alert bg-green-100 border-l-4 border-green-500 p-4">
                <div class="max-w-7xl mx-auto flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="min-h-screen bg-gray-100">
            {{-- Only show navigation if we're not on license pages --}}
            @if(!request()->is('license/*'))
                @livewire('navigation-menu')
            @endif

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif
            
            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @livewireScripts
    </body>
</html>