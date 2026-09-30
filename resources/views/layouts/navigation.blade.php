<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="{{ auth()->check() ? route('dashboard') : route('login') }}">
            LostMate AI
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
                aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                @auth
                    <li class="nav-item">
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            Dashboard
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('browse.index')" :active="request()->routeIs('browse.index')">
                            Browse
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('lost-items.create')" :active="request()->routeIs('lost-items.create')">
                            Report Lost
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('found-items.create')" :active="request()->routeIs('found-items.create')">
                            Report Found
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('my-reports.index')" :active="request()->routeIs('my-reports.index')">
                            My Reports
                        </x-nav-link>
                    </li>

                    @if (auth()->user()->isAdmin())
                        <li class="nav-item">
                            <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.*')">
                                Admin
                            </x-nav-link>
                        </li>
                    @endif
                @endauth
            </ul>

            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                @guest
                    <li class="nav-item">
                        <x-nav-link :href="route('login')" :active="request()->routeIs('login')">
                            Login
                        </x-nav-link>
                    </li>
                    <li class="nav-item">
                        <x-nav-link :href="route('register')" :active="request()->routeIs('register')">
                            Register
                        </x-nav-link>
                    </li>
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button"
                           data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                            <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Log Out</button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </div>
</nav>
