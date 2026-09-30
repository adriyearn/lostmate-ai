<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Possible Matches: {{ $lostItem->item_name }}</h1>
    </x-slot>

    <div class="alert alert-info">
        These are AI-generated suggestions only &mdash; they do not confirm ownership.
        Always verify identifying details with the finder before arranging a handover.
    </div>

    @if ($matches->isEmpty())
        <p class="text-muted">No possible matches yet. We'll notify you when one turns up.</p>
    @else
        <div class="row row-cols-1 row-cols-md-2 g-3">
            @foreach ($matches as $match)
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <x-item-card :item="$match->foundItem" type="found" />

                            <div class="mt-3">
                                <div class="d-flex justify-content-between small mb-1">
                                    <span>Match confidence</span>
                                    <span>{{ $match->score }}%</span>
                                </div>
                                <div class="progress" role="progressbar" aria-valuenow="{{ $match->score }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $match->score }}%"></div>
                                </div>
                            </div>

                            @if ($match->reason)
                                <p class="small text-muted mt-2 mb-0">{{ $match->reason }}</p>
                            @endif

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <form method="POST" action="{{ route('ai-matches.start-conversation', $match) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary">This might be mine / Contact finder</button>
                                </form>

                                <a href="{{ route('claims.create', ['foundItem' => $match->foundItem, 'ai_match_id' => $match->id, 'lost_item_id' => $lostItem->id]) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    Submit claim
                                </a>

                                <form method="POST" action="{{ route('ai-matches.dismiss', $match) }}"
                                      onsubmit="return confirm('Dismiss this match?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Not a match</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
