<x-app-layout>
    @php $profile = $user->profile; @endphp

    <div class="card mb-4">
        <div class="card-body p-4 d-flex flex-column flex-sm-row align-items-center align-items-sm-start gap-4 text-center text-sm-start">
            <x-avatar :user="$user" size="xl" />

            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-sm-start gap-2 mb-1">
                    <h1 class="h3 mb-0">{{ $user->name }}</h1>
                    <span class="badge text-bg-light lm-mono">{{ $user->role->label() }}</span>
                    @unless ($user->is_active)
                        <span class="badge text-bg-danger">Deactivated</span>
                    @endunless
                </div>

                @if ($profile?->course_or_position || $profile?->department || $profile?->year_level)
                    <p class="text-muted mb-2">
                        {{ collect([$profile->course_or_position, $profile->year_level, $profile->department])->filter()->implode(' · ') }}
                    </p>
                @endif

                @if ($profile?->bio)
                    <p class="mb-3 text-dark" style="white-space: pre-line;">{{ $profile->bio }}</p>
                @endif

                <div class="d-flex flex-wrap justify-content-center justify-content-sm-start gap-3 small text-muted">
                    <span><i class="bi bi-calendar3"></i> Member since {{ $user->created_at->format('M Y') }}</span>
                    @if ($returnedCount > 0)
                        <span><i class="bi bi-heart"></i> Returned {{ $returnedCount }} {{ Str::plural('item', $returnedCount) }} to their owners</span>
                    @endif
                </div>
            </div>

            @if (auth()->id() === $user->id)
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary"><i class="bi bi-pencil"></i> Edit profile</a>
            @endif
        </div>
    </div>

    <p class="small text-muted mb-4">
        <i class="bi bi-shield-lock"></i>
        Emails and phone numbers are never shown. To contact {{ $user->id === auth()->id() ? 'someone' : $user->name }}, use
        <strong>"Contact"</strong> on one of their reports.
    </p>

    @foreach ([['Lost items', 'lost', $lostItems, 'bi-exclamation-circle'], ['Found items', 'found', $foundItems, 'bi-box-seam']] as [$title, $type, $items, $icon])
        <h2 class="lm-section-title mb-3"><i class="bi {{ $icon }}"></i> {{ $title }}</h2>

        @if ($items->isEmpty())
            <p class="text-muted small mb-4">No open {{ strtolower($title) }} right now.</p>
        @else
            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mb-4">
                @foreach ($items as $item)
                    <div class="col"><x-item-card :item="$item" :type="$type" /></div>
                @endforeach
            </div>
        @endif
    @endforeach
</x-app-layout>
