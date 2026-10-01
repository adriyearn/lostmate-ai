<x-guest-layout>
    <div class="mb-4">
        <span class="lm-stat-icon lm-tone-violet mb-3"><i class="bi bi-person-plus"></i></span>
        <h1 class="mb-1">Create your account</h1>
        <p class="text-muted mb-0">Join to report and recover lost items on campus.</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <x-input-label for="name" value="Full name" />
            <div class="lm-input-icon">
                <i class="bi bi-person"></i>
                <x-text-input id="name" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Juan Dela Cruz" />
            </div>
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div class="mb-3">
            <x-input-label for="email" value="Email" />
            <div class="lm-input-icon">
                <i class="bi bi-envelope"></i>
                <x-text-input id="email" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="you@school.edu" />
            </div>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div class="mb-3">
            <x-input-label for="student_id" value="Student / Employee ID (optional)" />
            <div class="lm-input-icon">
                <i class="bi bi-person-badge"></i>
                <x-text-input id="student_id" type="text" name="student_id" :value="old('student_id')" autocomplete="off" placeholder="2021-00123" />
            </div>
            <x-input-error :messages="$errors->get('student_id')" />
        </div>

        <div class="row g-3 mb-4">
            <div class="col-sm-6">
                <x-input-label for="password" value="Password" />
                <div class="lm-input-icon">
                    <i class="bi bi-lock"></i>
                    <x-text-input id="password" type="password" name="password" required autocomplete="new-password" />
                </div>
                <x-input-error :messages="$errors->get('password')" />
            </div>
            <div class="col-sm-6">
                <x-input-label for="password_confirmation" value="Confirm" />
                <div class="lm-input-icon">
                    <i class="bi bi-lock-fill"></i>
                    <x-text-input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" />
                </div>
                <x-input-error :messages="$errors->get('password_confirmation')" />
            </div>
        </div>

        <x-primary-button>Create account <i class="bi bi-arrow-right"></i></x-primary-button>
    </form>

    <p class="mb-0 mt-4 small text-center text-muted">
        Already have an account? <a href="{{ route('login') }}" class="fw-semibold">Log in</a>
    </p>
</x-guest-layout>
