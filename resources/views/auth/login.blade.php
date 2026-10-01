<x-guest-layout>
    <div class="mb-4">
        <span class="lm-stat-icon lm-tone-indigo mb-3"><i class="bi bi-box-arrow-in-right"></i></span>
        <h1 class="mb-1">Welcome back</h1>
        <p class="text-muted mb-0">Log in to report items and see your AI matches.</p>
    </div>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <div class="lm-input-icon">
                <i class="bi bi-envelope"></i>
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="you@school.edu" />
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <x-input-label for="password" value="Password" />
                @if (Route::has('password.request'))
                    <a class="small fw-semibold" href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>
            <div class="lm-input-icon">
                <i class="bi bi-lock"></i>
                <x-text-input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
            </div>
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-4 form-check">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label small">Keep me logged in</label>
        </div>

        <x-primary-button>Log in <i class="bi bi-arrow-right"></i></x-primary-button>
    </form>

    <p class="mb-0 mt-4 small text-center text-muted">
        New to LostMate? <a href="{{ route('register') }}" class="fw-semibold">Create an account</a>
    </p>
</x-guest-layout>
