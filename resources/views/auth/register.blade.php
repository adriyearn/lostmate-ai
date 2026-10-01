<x-guest-layout>
    <h1 class="mb-1">Create an account</h1>
    <p class="text-muted small mb-4">Join to report and recover lost items on campus.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="name" value="Name" />
            <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="student_id" value="Student / Employee ID (optional)" />
            <x-text-input id="student_id" type="text" name="student_id" :value="old('student_id')" autocomplete="off" />
            <x-input-error :messages="$errors->get('student_id')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-3">
            <x-input-label for="password_confirmation" value="Confirm Password" />
            <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button>Create account</x-primary-button>
    </form>

    <p class="mb-0 mt-4 small text-center text-muted">
        Already have an account? <a href="{{ route('login') }}" class="fw-medium">Log in</a>
    </p>
</x-guest-layout>
