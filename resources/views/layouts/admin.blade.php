<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Admin &middot; {{ config('app.name', 'LostMate AI') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        @include('layouts.navigation')

        <div class="container-fluid">
            <div class="row">
                <div class="d-md-none py-2 border-bottom bg-white">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-expanded="false">
                        Admin menu
                    </button>
                </div>

                <nav id="adminSidebar" class="col-md-2 collapse d-md-block bg-white border-end sidebar py-3 px-0">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active fw-semibold' : '' }}" href="{{ route('admin.dashboard') }}">
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.users.index') }}">
                                Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.reports.index') }}">
                                Lost &amp; Found Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.claims.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.claims.index') }}">
                                Claims
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.categories.index') }}">
                                Categories
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.flags.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.flags.index') }}">
                                Flagged Content
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active fw-semibold' : '' }}" href="{{ route('admin.logs.index') }}">
                                Admin Logs
                            </a>
                        </li>
                    </ul>
                </nav>

                <main class="col-md-10 py-3">
                    @isset($header)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            {{ $header }}
                        </div>
                    @endisset

                    <x-flash-messages />

                    {{ $slot }}
                </main>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
