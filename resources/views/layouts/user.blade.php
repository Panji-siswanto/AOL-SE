<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
 <body class="font-sans antialiased bg-white">
    <div class="min-h-screen relative">
            <x-user-navigation />

        <main>
            {{ $slot }}
        </main>
    </div>

    {{-- GLOBAL LOGIN PROMPT MODAL --}}
    <div x-data="{ loginModalOpen: false }" 
         @open-login-modal.window="loginModalOpen = true"
         x-show="loginModalOpen" 
         x-cloak 
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-sm"
         x-transition.opacity>
        
        <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl relative" @click.away="loginModalOpen = false">
            <button @click="loginModalOpen = false" class="absolute top-4 right-4 text-gray-400 hover:text-gray-900 text-2xl">&times;</button>
            
            <div class="w-16 h-16 bg-teal-50 text-teal-600 rounded-2xl flex items-center justify-center text-3xl mb-6 shadow-inner mx-auto">
                🔒
            </div>
            
            <h3 class="text-2xl font-black text-gray-900 text-center mb-2">Login Required</h3>
            <p class="text-gray-500 text-center text-sm font-medium mb-8">You need an account to save favorite spaces and request rents.</p>
            
            <div class="space-y-3">
                <a href="{{ route('login') }}" class="block w-full bg-teal-600 hover:bg-teal-700 text-white text-center py-3.5 rounded-xl font-bold shadow-md transition">Login to Lapak.in</a>
                <a href="{{ route('register') }}" class="block w-full bg-gray-50 hover:bg-gray-100 text-gray-700 border border-gray-200 text-center py-3.5 rounded-xl font-bold transition">Create an Account</a>
            </div>
        </div>
    </div>
</body>
</html>