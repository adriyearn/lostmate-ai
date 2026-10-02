<x-app-layout>
    {{-- Public landing page. Shows only overall totals - never item details, names, or contacts. --}}

    <section class="lm-hero mt-4 mb-5">
        <div class="row g-5 align-items-center">
            <div class="col-lg-7">
                <div class="lm-mono lm-hero-eyebrow mb-3">AI-powered campus lost &amp; found</div>
                <h1 class="mb-3">Lost it? Someone probably <span class="lm-mark">found it.</span></h1>
                <p class="mb-4" style="max-width: 34rem;">
                    LostMate AI connects people who lost something on campus with the people who found it.
                    Report an item, and our AI compares it with every report of the opposite type to suggest likely matches.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn btn-light btn-lg"><i class="bi bi-person-plus"></i> Create an account</a>
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-lg">Log in <i class="bi bi-arrow-right"></i></a>
                </div>
            </div>

            <div class="col-lg-5 d-none d-lg-block">
                <div class="lm-tag-scene ms-auto" aria-hidden="true">
                    <div class="lm-float-tag is-lost">
                        <span class="lm-type-chip is-lost">Lost</span>
                        Blue tumbler, dented
                        <small>Gym bleachers &middot; Mon</small>
                    </div>
                    <div class="lm-match-chip"><i class="bi bi-stars"></i> 88% likely match</div>
                    <div class="lm-float-tag is-found">
                        <span class="lm-type-chip is-found">Found</span>
                        Blue water tumbler
                        <small>Gym entrance &middot; Mon</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="row g-3 mb-5">
        @foreach ([
            ['value' => $stats['reports'], 'label' => 'Items reported', 'icon' => 'bi-folder2-open', 'tone' => 'lm-tone-indigo'],
            ['value' => $stats['returned'], 'label' => 'Returned to owners', 'icon' => 'bi-arrow-return-left', 'tone' => 'lm-tone-green'],
            ['value' => $stats['recoveryRate'].'%', 'label' => 'Recovery rate', 'icon' => 'bi-graph-up-arrow', 'tone' => 'lm-tone-violet'],
        ] as $tile)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="lm-stat-card">
                        <span class="lm-stat-icon {{ $tile['tone'] }}"><i class="bi {{ $tile['icon'] }}"></i></span>
                        <div>
                            <div class="lm-stat">{{ $tile['value'] }}</div>
                            <div class="lm-stat-label">{{ $tile['label'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <h2 class="lm-section-title mb-3"><i class="bi bi-signpost-split"></i> How it works</h2>
    <div class="row g-3 mb-5">
        @foreach ([
            ['num' => '01', 'title' => 'Report it', 'text' => 'Lost something? Found something? Post it in a minute with a description, location, date, and up to 3 photos.'],
            ['num' => '02', 'title' => 'AI suggests matches', 'text' => 'In the background, AI compares your report with reports of the opposite type and alerts you by app and email.'],
            ['num' => '03', 'title' => 'Verify & return', 'text' => 'The owner describes private details only they would know. The finder checks them, then hands it over with a pickup code.'],
        ] as $step)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="lm-mono d-inline-block mb-2 px-2 py-1 rounded" style="background: var(--lm-night); color: var(--lm-ai);">{{ $step['num'] }}</span>
                        <h3 class="h5 mb-2">{{ $step['title'] }}</h3>
                        <p class="text-muted small mb-0">{{ $step['text'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <h2 class="lm-section-title mb-3"><i class="bi bi-shield-check"></i> Built to keep you safe</h2>
    <div class="row g-3 mb-5">
        @foreach ([
            ['icon' => 'bi-eye-slash', 'tone' => 'lm-tone-amber', 'title' => 'Hidden details', 'text' => 'Finders record private details (like what\'s inside a wallet) that only they and admins can see. Claimants must describe them to prove ownership.'],
            ['icon' => 'bi-chat-heart', 'tone' => 'lm-tone-sky', 'title' => 'No contact info shared', 'text' => 'Emails and phone numbers are never shown. Owners and finders talk through in-app messaging.'],
            ['icon' => 'bi-person-check', 'tone' => 'lm-tone-red', 'title' => 'Moderated by the school', 'text' => 'Only verified school accounts can join. Admins review flagged posts and messages and can deactivate abusive users.'],
        ] as $feature)
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-body">
                        <span class="lm-stat-icon {{ $feature['tone'] }} mb-3"><i class="bi {{ $feature['icon'] }}"></i></span>
                        <h3 class="h5 mb-2">{{ $feature['title'] }}</h3>
                        <p class="text-muted small mb-0">{{ $feature['text'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-2" style="border-color: var(--lm-ink); box-shadow: var(--lm-hard-lg);">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3 p-4">
            <div>
                <h2 class="h4 mb-1">Missing something right now?</h2>
                <p class="text-muted mb-0">The sooner you report it, the sooner the AI can start looking.</p>
            </div>
            <a href="{{ route('register') }}" class="btn btn-primary btn-lg"><i class="bi bi-plus-lg"></i> Report an item</a>
        </div>
    </div>
</x-app-layout>
