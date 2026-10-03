<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('found-items.show', $foundItem) }}" class="small fw-semibold text-muted d-inline-flex align-items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> Back to report
        </a>
        <div class="d-flex align-items-center gap-3">
            <span class="lm-stat-icon lm-tone-violet"><i class="bi bi-stars"></i></span>
            <div>
                <div class="small text-muted fw-semibold">AI possible matches for your found item</div>
                <h1>{{ $foundItem->item_name }}</h1>
            </div>
        </div>
    </x-slot>

    <x-matching-status :item="$foundItem" class="mb-4" />

    <div class="alert alert-info d-flex gap-2 align-items-start">
        <i class="bi bi-info-circle mt-1"></i>
        <div>
            These are AI-generated suggestions only &mdash; they do not confirm ownership.
            Always verify identifying details with the claimant before arranging a handover.
        </div>
    </div>

    @if ($matches->isEmpty())
        <div class="card">
            <div class="lm-empty">
                <i class="bi bi-hourglass-split"></i>
                <div class="fw-semibold text-dark mb-1">No possible matches yet</div>
                <div class="small">No possible matches yet. The AI checks reports in the background (usually within a minute of submitting) and you'll get a notification when a strong match turns up.</div>
            </div>
        </div>
    @else
        <div class="row row-cols-1 row-cols-lg-2 g-4">
            @foreach ($matches as $match)
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                <div>
                                    <div class="small text-muted fw-semibold">Match confidence</div>
                                    <div class="fw-bold text-dark">{{ $match->score >= 80 ? 'Strong match' : ($match->score >= 65 ? 'Likely match' : 'Possible match') }}</div>
                                </div>
                                <span class="lm-score-ring" style="--score: {{ $match->score }}">{{ $match->score }}%</span>
                            </div>

                            <x-item-card :item="$match->lostItem" type="lost" />

                            @if ($match->reason)
                                <div class="lm-quote mt-3"><i class="bi bi-stars"></i> <span>{{ $match->reason }}</span></div>
                            @endif

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <form method="POST" action="{{ route('ai-matches.start-conversation', $match) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-chat-dots"></i> Contact owner</button>
                                </form>

                                <form method="POST" action="{{ route('ai-matches.dismiss', $match) }}"
                                      onsubmit="return confirm('Dismiss this match?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Not a match</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
