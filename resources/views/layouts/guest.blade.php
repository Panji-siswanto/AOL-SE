{{-- resources/views/layouts/guest.blade.php --}}

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

    {{-- Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased text-gray-900">

    <div class="min-h-screen flex flex-col">

        {{-- Navbar --}}
        <header class="px-12 pt-8">
            <x-auth.navbar />
        </header>

        {{-- Main Content --}}
        <main class="flex-1 flex items-center justify-center px-6 -mt-20">
            {{ $slot }}
        </main>

    </div>

</body>

</html>