<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand" href="{{ auth()->check() ? route('dashboard') : route('home') }}">
            <x-logo />
            <span>LostMate<span class="lm-brand-ai">AI</span></span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-lg-auto mb-2 mb-lg-0 gap-lg-1">
                @auth
                    <li class="nav-item">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            <i class="bi bi-grid-1x2"></i> Dashboard
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('browse.index')" :active="request()->routeIs('browse.index')">
                            <i class="bi bi-compass"></i> Browse
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('my-reports.index')" :active="request()->routeIs('my-reports.index')">
                            <i class="bi bi-folder2-open"></i> My Reports
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('my-claims.index')" :active="request()->routeIs('my-claims.index')">
                            <i class="bi bi-patch-check"></i> My Claims
                        </x-nav-link>
                    </li>

                    @if (auth()->user()->isAdmin())
                        <li class="nav-item">
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                <i class="bi bi-shield-lock"></i> Admin
                            </x-nav-link>
                        </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-lg-center gap-1">
                <li class="nav-item">
                    {{-- Hidden until the browser says the app can be installed (resources/js/pwa.js). --}}
                    <button type="button" class="nav-link lm-icon-btn lm-install-app d-none border-0 bg-transparent" title="Install LostMate as an app" aria-label="Install LostMate as an app">
                        <i class="bi bi-phone"></i>
                        <span class="d-lg-none ms-2">Install app</span>
                    </button>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link lm-icon-btn lm-theme-toggle border-0 bg-transparent" title="Switch light/dark mode" aria-label="Switch light/dark mode">
                        <i class="bi bi-moon-stars"></i><i class="bi bi-sun"></i>
                        <span class="d-lg-none ms-2">Light / dark mode</span>
                    </button>
                </li>
                @guest
                    <li class="nav-item">
                        <x-nav-link :href="route('login')" :active="request()->routeIs('login')">
                            Log in
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm ms-lg-1" href="{{ route('register') }}">Get started</a>
                    </li>
                @else
                    <li class="nav-item dropdown d-none d-lg-block">
                        <a class="btn btn-primary btn-sm me-2" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-plus-lg"></i> Report
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('lost-items.create') }}">
                                    <i class="bi bi-exclamation-circle text-danger"></i> I lost something
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('found-items.create') }}">
                                    <i class="bi bi-box-seam text-success"></i> I found something
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item d-lg-none">
                        <x-nav-link :href="route('lost-items.create')" :active="request()->routeIs('lost-items.create')">
                            <i class="bi bi-exclamation-circle"></i> Report Lost
                        </x-nav-link>
                    </li>
                    <li class="nav-item d-lg-none">
                        <x-nav-link :href="route('found-items.create')" :active="request()->routeIs('found-items.create')">
                            <i class="bi bi-box-seam"></i> Report Found
                        </x-nav-link>
                    </li>

                    <li class="nav-item">
                        @php $unreadMessages = auth()->user()->unreadMessagesCount(); @endphp
                        <a class="nav-link lm-icon-btn {{ request()->routeIs('conversations.*') ? 'active' : '' }}"
                           href="{{ route('conversations.index') }}" title="Messages">
                            <i class="bi bi-chat-dots"></i>
                            <span class="d-lg-none ms-2">Messages</span>
                            @if ($unreadMessages > 0)
                                <span class="lm-dot">{{ $unreadMessages }}</span>
                            @endif
                        </a>
                    </li>

                    @php $unreadNotifications = auth()->user()->unreadNotifications; @endphp
                    <li class="nav-item dropdown">
                        <a class="nav-link lm-icon-btn" href="#" id="notificationsMenu" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
                            <i class="bi bi-bell"></i>
                            <span class="d-lg-none ms-2">Notifications</span>
                            @if ($unreadNotifications->isNotEmpty())
                                <span class="lm-dot">{{ $unreadNotifications->count() }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationsMenu" style="min-width: 22rem;">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            @forelse ($unreadNotifications->take(5) as $notification)
                                <li>
                                    <a class="dropdown-item text-wrap small align-items-start" href="{{ $notification->data['link'] ?? route('notifications.index') }}">
                                        <i class="bi bi-dot fs-4 text-primary lh-1"></i>
                                        <span>{{ $notification->data['message'] ?? 'Notification' }}</span>
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item-text small text-muted">You're all caught up.</span></li>
                            @endforelse
                            <li><hr class="dropdown-divider"></li>
                            @if ($unreadNotifications->isNotEmpty())
                                <li>
                                    <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item small"><i class="bi bi-check2-all"></i> Mark all as read</button>
                                    </form>
                                </li>
                            @endif
                            <li><a class="dropdown-item small" href="{{ route('notifications.index') }}"><i class="bi bi-list-ul"></i> View all notifications</a></li>
                        </ul>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link d-flex align-items-center gap-2" href="#" id="userMenu" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <x-avatar :user="auth()->user()" />
                            <span class="d-lg-none">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li class="px-3 py-2">
                                <div class="fw-bold text-dark small">{{ auth()->user()->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">{{ auth()->user()->role->label() }}</div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('users.show', auth()->user()) }}"><i class="bi bi-person-badge"></i> View my profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> Edit profile</a></li>
                            <li><a class="dropdown-item" href="{{ route('notifications.index') }}"><i class="bi bi-bell"></i> Notifications</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-box-arrow-right"></i> Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
