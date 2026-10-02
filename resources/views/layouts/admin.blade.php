<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Admin &middot; {{ config('app.name', 'LostMate AI') }}</title>

        @include('layouts.partials.theme')
        @include('layouts.partials.pwa')
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-light">
        @include('layouts.navigation')

        <div class="container-fluid">
            <div class="row">
                <div class="d-md-none py-2 border-bottom bg-white">
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#adminSidebar" aria-controls="adminSidebar" aria-expanded="false">
                        <i class="bi bi-list"></i> Admin menu
                    </button>
                </div>

                <nav id="adminSidebar" class="col-md-2 collapse d-md-block bg-white border-end sidebar py-3 px-0">
                    <div class="lm-sidebar-label">Back office</div>
                    <ul class="nav flex-column">
                        @foreach ([
                            ['pattern' => 'admin.dashboard', 'route' => 'admin.dashboard', 'icon' => 'bi-speedometer2', 'label' => 'Dashboard'],
                            ['pattern' => 'admin.users.*', 'route' => 'admin.users.index', 'icon' => 'bi-people', 'label' => 'Users'],
                            ['pattern' => 'admin.reports.*', 'route' => 'admin.reports.index', 'icon' => 'bi-folder2-open', 'label' => 'Lost & Found Reports'],
                            ['pattern' => 'admin.claims.*', 'route' => 'admin.claims.index', 'icon' => 'bi-patch-check', 'label' => 'Claims'],
                            ['pattern' => 'admin.office.*', 'route' => 'admin.office.index', 'icon' => 'bi-building', 'label' => 'Office & Unclaimed'],
                            ['pattern' => 'admin.categories.*', 'route' => 'admin.categories.index', 'icon' => 'bi-tags', 'label' => 'Categories'],
                            ['pattern' => 'admin.flags.*', 'route' => 'admin.flags.index', 'icon' => 'bi-flag', 'label' => 'Flagged Content'],
                            ['pattern' => 'admin.exports.*', 'route' => 'admin.exports.index', 'icon' => 'bi-file-earmark-bar-graph', 'label' => 'Reports & Export'],
                            ['pattern' => 'admin.logs.*', 'route' => 'admin.logs.index', 'icon' => 'bi-journal-text', 'label' => 'Admin Logs'],
                        ] as $link)
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs($link['pattern']) ? 'active' : '' }}" href="{{ route($link['route']) }}">
                                    <i class="bi {{ $link['icon'] }}"></i> {{ $link['label'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </nav>

                <main class="col-md-10 py-4 px-md-4 lm-fade-in">
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
