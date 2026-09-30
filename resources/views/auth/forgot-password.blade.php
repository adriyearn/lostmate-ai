<x-guest-layout>
    <h1 class="h4 mb-3">Forgot your password?</h1>

    <p class="text-muted small">
        No problem. Let us know your email address and we will email you a password reset link.
    </p>

    <x-auth-session-status class="mb-3" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="d-flex justify-content-end">
            <x-primary-button>Email Password Reset Link</x-primary-button>
        </div>
    </form>
</x-guest-layout>
