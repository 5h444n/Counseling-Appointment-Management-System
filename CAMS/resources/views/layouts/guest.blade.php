<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CAMS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans text-gray-900 antialiased">
<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-slate-900">
    <div class="mb-6 text-center">
        <a href="/">
            <h1 class="text-4xl font-bold tracking-wider text-white">
                CAMS <span class="text-orange-500">UIU</span>
            </h1>
            <p class="text-xs text-gray-300 uppercase tracking-widest mt-1">Counseling Portal</p>
        </a>
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-8 bg-white shadow-2xl overflow-hidden sm:rounded-xl border-t-4 border-orange-500">
        {{ $slot }}
    </div>

    <div class="mt-8 text-center text-sm text-gray-400">
        &copy; {{ date('Y') }} United International University
    </div>
</div>
</body>
</html>
