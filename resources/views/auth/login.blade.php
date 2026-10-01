<x-guest-layout>
    <h1 class="mb-1">Welcome back</h1>
    <p class="text-muted small mb-4">Log in to report and find lost items on campus.</p>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between">
                <x-input-label for="password" value="Password" />
                @if (Route::has('password.request'))
                    <a class="small text-muted" href="{{ route('password.request') }}">Forgot?</a>
                @endif
            </div>
            <x-text-input id="password" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-4 form-check">
            <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
            <label for="remember_me" class="form-check-label small">Remember me</label>
        </div>

        <x-primary-button>Log in</x-primary-button>
    </form>

    <p class="mb-0 mt-4 small text-center text-muted">
        Don't have an account? <a href="{{ route('register') }}" class="fw-medium">Register</a>
    </p>
</x-guest-layout>
