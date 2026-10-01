<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Possible Matches: {{ $foundItem->item_name }}</h1>
    </x-slot>

    <div class="alert alert-info">
        These are AI-generated suggestions only &mdash; they do not confirm ownership.
        Always verify identifying details with the claimant before arranging a handover.
    </div>

    @if ($matches->isEmpty())
        <p class="text-muted">No possible matches yet. The AI checks reports in the background (usually within a minute of submitting) and you'll get a notification when a strong match turns up.</p>
    @else
        <div class="row row-cols-1 row-cols-md-2 g-3">
            @foreach ($matches as $match)
                <div class="col">
                    <div class="card h-100">
                        <div class="card-body">
                            <x-item-card :item="$match->lostItem" type="lost" />

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
                                    <button type="submit" class="btn btn-sm btn-primary">Contact owner</button>
                                </form>

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
