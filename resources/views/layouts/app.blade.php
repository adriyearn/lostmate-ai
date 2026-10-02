<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'LostMate AI') }}</title>

        @include('layouts.partials.theme')
        @include('layouts.partials.pwa')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        @include('layouts.navigation')

        @isset($header)
            <header class="lm-page-header">
                <div class="container">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="py-4">
            <div class="container lm-fade-in">
                <x-flash-messages />

                {{ $slot }}
            </div>
        </main>

        <footer class="lm-footer">
            <div class="container d-flex flex-wrap justify-content-between gap-2">
                <span class="d-inline-flex align-items-center gap-2"><x-logo style="width:22px;height:22px;" /> <span class="lm-mono">&copy; {{ date('Y') }} LostMate AI &middot; Campus lost &amp; found</span></span>
                <span><i class="bi bi-stars"></i> AI matches are suggestions &mdash; always verify ownership.</span>
            </div>
        </footer>

        @stack('scripts')
    </body>
</html>
