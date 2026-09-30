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

        <div class="d-flex justify-content-center align-items-start py-5">
            <div class="w-100" style="max-width: 28rem;">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <x-flash-messages />

                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
