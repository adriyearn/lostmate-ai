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

        <div class="lm-auth">
            <aside class="lm-auth-brand">
                <div>
                    <span class="badge rounded-pill" style="background: rgba(255,255,255,.18); color: #fff;">
                        <i class="bi bi-stars"></i> AI-powered lost &amp; found
                    </span>
                </div>

                <div>
                    <h2 class="mb-3">Lost something on campus?<br>Let's bring it back.</h2>
                    <p class="mb-4">Report lost or found items, and our AI suggests likely matches so you can reunite things with their owners faster.</p>

                    <div class="lm-auth-feature"><i class="bi bi-cpu"></i> AI suggests possible matches automatically</div>
                    <div class="lm-auth-feature"><i class="bi bi-shield-check"></i> Private details verify real owners</div>
                    <div class="lm-auth-feature"><i class="bi bi-chat-heart"></i> Message finders without sharing contacts</div>
                </div>

                <p class="small mb-0" style="opacity:.75;">&copy; {{ date('Y') }} LostMate AI</p>
            </aside>

            <main class="lm-auth-form">
                <div class="lm-auth-card lm-fade-in">
                    <x-flash-messages />

                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
