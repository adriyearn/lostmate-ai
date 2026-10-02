<x-guest-layout>
    <div class="mb-4">
        <span class="lm-stat-icon lm-tone-amber mb-3"><i class="bi bi-envelope-check"></i></span>
        <h1 class="mb-1">Check your inbox</h1>
        <p class="text-muted mb-0">
            We sent a verification link to <strong class="text-dark">{{ auth()->user()->email }}</strong>.
            Click it to start using LostMate AI.
        </p>
    </div>

    <div class="alert alert-secondary small">
        <i class="bi bi-info-circle"></i>
        Verifying your email keeps the lost &amp; found limited to real school members,
        and makes sure match and claim alerts reach you.
    </div>

    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
        @csrf
        <x-primary-button><i class="bi bi-send"></i> Resend verification email</x-primary-button>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="text-center">
        @csrf
        <button type="submit" class="btn btn-link btn-sm">Wrong email? Log out and register again</button>
    </form>
</x-guest-layout>
