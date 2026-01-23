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
<body class="font-sans antialiased bg-gray-50 text-gray-900">
<div class="min-h-screen flex">

    @include('layouts.sidebar')

    <div class="flex-1 flex flex-col min-h-screen transition-all duration-300 md:ml-64">

        @include('layouts.navigation')

        <main class="flex-1 p-6">
            @if (isset($header))
                <header class="mb-6">
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                        {{ $header }}
                    </h2>
                </header>
            @endif

            {{ $slot }}
        </main>

        @include('layouts.footer')
    </div>
</div>

{{-- Global Toast Notifications --}}
<x-toast />

</body>
</html>
