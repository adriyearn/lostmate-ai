<x-app-layout>
    <section class="lm-hero mt-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-4">
            <div style="max-width: 36rem;">
                <span class="badge rounded-pill mb-3" style="background: rgba(255,255,255,.18); color:#fff;">
                    <i class="bi bi-stars"></i> {{ auth()->user()->role->label() }}
                </span>
                <h1 class="mb-2">Hi {{ \Illuminate\Support\Str::of(auth()->user()->name)->before(' ') }}, welcome back</h1>
                <p class="mb-0">Report what you lost or found. Our AI compares every new report and suggests likely matches for you.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('lost-items.create') }}" class="btn btn-light btn-lg"><i class="bi bi-exclamation-circle"></i> I lost something</a>
                <a href="{{ route('found-items.create') }}" class="btn btn-ghost btn-lg"><i class="bi bi-box-seam"></i> I found something</a>
            </div>
        </div>
    </section>

    <div class="row g-3 mb-5">
        @foreach ([
            ['label' => 'My lost reports', 'value' => $stats['lost'], 'icon' => 'bi-exclamation-circle', 'tone' => 'lm-tone-red', 'href' => route('my-reports.index', ['tab' => 'lost'])],
            ['label' => 'My found reports', 'value' => $stats['found'], 'icon' => 'bi-box-seam', 'tone' => 'lm-tone-green', 'href' => route('my-reports.index', ['tab' => 'found'])],
            ['label' => 'AI matches', 'value' => $stats['matches'], 'icon' => 'bi-stars', 'tone' => 'lm-tone-violet', 'href' => '#matches'],
            ['label' => 'Claims to review', 'value' => $stats['pendingClaims'], 'icon' => 'bi-patch-question', 'tone' => 'lm-tone-amber', 'href' => route('my-reports.index', ['tab' => 'found'])],
        ] as $tile)
            <div class="col-6 col-lg-3">
                <a href="{{ $tile['href'] }}" class="card lm-item-card h-100 text-reset">
                    <div class="lm-stat-card">
                        <span class="lm-stat-icon {{ $tile['tone'] }}"><i class="bi {{ $tile['icon'] }}"></i></span>
                        <div>
                            <div class="lm-stat">{{ $tile['value'] }}</div>
                            <div class="lm-stat-label">{{ $tile['label'] }}</div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div id="matches" class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="lm-section-title"><i class="bi bi-stars"></i> AI possible matches for your reports</h2>
        <a href="{{ route('my-reports.index') }}" class="small fw-semibold">My reports <i class="bi bi-arrow-right"></i></a>
    </div>

    @if ($possibleMatches->isEmpty())
        <div class="card mb-5">
            <div class="lm-empty">
                <i class="bi bi-cpu"></i>
                <div class="fw-semibold text-dark mb-1">No possible matches yet</div>
                <div class="small">When you report a lost or found item, the AI compares it against reports of the opposite type and suggests matches here.</div>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 mb-5">
            @foreach ($possibleMatches as $match)
                @php
                    $mineIsLost = $match->lostItem->user_id === auth()->id();
                    $mine = $mineIsLost ? $match->lostItem : $match->foundItem;
                    $other = $mineIsLost ? $match->foundItem : $match->lostItem;
                    $matchesRoute = $mineIsLost
                        ? route('lost-items.matches', $mine)
                        : route('found-items.matches', $mine);
                @endphp
                <div class="col">
                    <a href="{{ $matchesRoute }}" class="card h-100 lm-item-card lm-match-card text-reset">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                <div class="min-w-0">
                                    <div class="small text-muted mb-1">
                                        Your {{ $mineIsLost ? 'lost' : 'found' }} &ldquo;{{ $mine->item_name }}&rdquo; may match:
                                    </div>
                                    <h3 class="h6 mb-1">{{ $other->item_name }}</h3>
                                    <div class="d-flex flex-wrap gap-3">
                                        <span class="lm-meta"><i class="bi bi-tag"></i> {{ $other->category->name }}</span>
                                        <span class="lm-meta"><i class="bi bi-geo-alt"></i> {{ $mineIsLost ? $other->location_found : $other->location_lost }}</span>
                                    </div>
                                </div>
                                <span class="lm-score-ring" style="--score: {{ $match->score }}">{{ $match->score }}%</span>
                            </div>
                            @if ($match->reason)
                                <div class="lm-quote"><i class="bi bi-stars"></i> <span>{{ $match->reason }}</span></div>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="lm-section-title"><i class="bi bi-exclamation-circle"></i> Recently lost</h2>
                <a href="{{ route('browse.index', ['tab' => 'lost']) }}" class="small fw-semibold">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            @if ($recentLostItems->isEmpty())
                <div class="card"><div class="lm-empty"><i class="bi bi-inbox"></i>No lost items reported yet.</div></div>
            @else
                <div class="row row-cols-1 row-cols-sm-2 g-3">
                    @foreach ($recentLostItems as $item)
                        <div class="col"><x-item-card :item="$item" type="lost" /></div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="col-lg-6">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="lm-section-title"><i class="bi bi-box-seam"></i> Recently found</h2>
                <a href="{{ route('browse.index', ['tab' => 'found']) }}" class="small fw-semibold">View all <i class="bi bi-arrow-right"></i></a>
            </div>
            @if ($recentFoundItems->isEmpty())
                <div class="card"><div class="lm-empty"><i class="bi bi-inbox"></i>No found items reported yet.</div></div>
            @else
                <div class="row row-cols-1 row-cols-sm-2 g-3">
                    @foreach ($recentFoundItems as $item)
                        <div class="col"><x-item-card :item="$item" type="found" /></div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
