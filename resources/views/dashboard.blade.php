<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <p class="text-muted small mb-1">{{ auth()->user()->role->label() }}</p>
                <h1>Welcome back, {{ auth()->user()->name }}</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('lost-items.create') }}" class="btn btn-primary">Report lost item</a>
                <a href="{{ route('found-items.create') }}" class="btn btn-outline-secondary">Report found item</a>
            </div>
        </div>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
        <h2 class="lm-section-title">AI possible matches for your reports</h2>
        <a href="{{ route('my-reports.index') }}" class="small text-muted">My reports &rarr;</a>
    </div>
    @if ($possibleMatches->isEmpty())
        <div class="card mb-5">
            <div class="card-body text-muted small">
                No possible matches yet. When you report a lost or found item, the AI compares it
                against reports of the opposite type in the background and suggests matches here.
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 mb-5">
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
                    <a href="{{ $matchesRoute }}" class="card h-100 lm-item-card text-reset">
                        <div class="card-body p-3">
                            <p class="small text-muted mb-2">
                                Your {{ $mineIsLost ? 'lost' : 'found' }} "{{ $mine->item_name }}" may match:
                            </p>
                            <h3 class="h6 mb-1">{{ $other->item_name }}</h3>
                            <p class="small text-muted mb-3">
                                {{ $other->category->name }} &middot;
                                {{ $mineIsLost ? $other->location_found : $other->location_lost }}
                            </p>
                            <div class="d-flex justify-content-between small mb-1">
                                <span class="text-muted">Match confidence</span>
                                <span class="fw-semibold">{{ $match->score }}%</span>
                            </div>
                            <div class="progress mb-2">
                                <div class="progress-bar" style="width: {{ $match->score }}%"></div>
                            </div>
                            @if ($match->reason)
                                <p class="small text-muted mb-0">&ldquo;{{ $match->reason }}&rdquo;</p>
                            @endif
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="lm-section-title">Recently lost</h2>
        <a href="{{ route('browse.index', ['tab' => 'lost']) }}" class="small text-muted">View all &rarr;</a>
    </div>
    @if ($recentLostItems->isEmpty())
        <p class="text-muted">No lost items reported yet.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mb-5">
            @foreach ($recentLostItems as $item)
                <div class="col">
                    <x-item-card :item="$item" type="lost" />
                </div>
            @endforeach
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="lm-section-title">Recently found</h2>
        <a href="{{ route('browse.index', ['tab' => 'found']) }}" class="small text-muted">View all &rarr;</a>
    </div>
    @if ($recentFoundItems->isEmpty())
        <p class="text-muted">No found items reported yet.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
            @foreach ($recentFoundItems as $item)
                <div class="col">
                    <x-item-card :item="$item" type="found" />
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
