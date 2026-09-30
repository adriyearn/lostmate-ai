<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LostMate AI') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        @include('layouts.navigation')

        @isset($header)
            <header class="bg-white border-bottom py-3">
                <div class="container">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="py-4">
            <div class="container">
                <x-flash-messages />

                {{ $slot }}
            </div>
        </main>

        @stack('scripts')
    </body>
</html>
