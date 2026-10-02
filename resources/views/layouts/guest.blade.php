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

        <div class="lm-auth">
            <aside class="lm-auth-brand">
                <div class="lm-mono lm-hero-eyebrow">AI-powered campus lost &amp; found</div>

                {{-- Decorative demo: a lost report, a found report, and the AI match between them. --}}
                <div class="lm-tag-scene" aria-hidden="true">
                    <div class="lm-float-tag is-lost">
                        <span class="lm-type-chip is-lost">Lost</span>
                        Black leather wallet
                        <small>Library, 2nd floor &middot; Mon</small>
                    </div>
                    <div class="lm-match-chip"><i class="bi bi-stars"></i> 92% likely match</div>
                    <div class="lm-float-tag is-found">
                        <span class="lm-type-chip is-found">Found</span>
                        Black wallet, red mark
                        <small>Library entrance &middot; Tue</small>
                    </div>
                </div>

                <div>
                    <h2 class="mb-3">Lost it? Someone probably <span class="lm-mark">found it.</span></h2>
                    <p class="mb-4">Report lost or found items, and our AI suggests likely matches so you can reunite things with their owners faster.</p>

                    <div class="lm-auth-feature"><i class="bi bi-stars"></i> AI suggests possible matches automatically</div>
                    <div class="lm-auth-feature"><i class="bi bi-shield-check"></i> Private details verify real owners</div>
                    <div class="lm-auth-feature"><i class="bi bi-chat-heart"></i> Message finders without sharing contacts</div>
                </div>

                <p class="lm-mono mb-0" style="opacity:.55;">&copy; {{ date('Y') }} LostMate AI</p>
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
