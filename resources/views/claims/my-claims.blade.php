<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">My Claims</h1>
    </x-slot>

    @if ($claims->isEmpty())
        <p class="text-muted">You haven't submitted any claims yet.</p>
    @else
        <div class="list-group mb-3">
            @foreach ($claims as $claim)
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <a href="{{ route('found-items.show', $claim->foundItem) }}" class="fw-semibold text-decoration-none">
                                {{ $claim->foundItem->item_name }}
                            </a>
                            <span class="badge {{ $claim->status->badgeClass() }} ms-1">{{ $claim->status->label() }}</span>
                            <div class="small text-muted">Submitted {{ $claim->created_at->format('M j, Y') }}</div>
                        </div>

                        @if ($claim->status === App\Enums\ClaimStatus::Pending)
                            <form method="POST" action="{{ route('claims.cancel', $claim) }}"
                                  onsubmit="return confirm('Cancel this claim? The finder will no longer review it.');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-x-lg"></i> Cancel claim</button>
                            </form>
                        @endif
                    </div>

                    <p class="small mb-1 mt-2"><strong>Your identifying details:</strong> {{ $claim->identifying_details }}</p>

                    @if ($claim->finder_response)
                        <p class="small mb-0"><strong>Finder's response:</strong> {{ $claim->finder_response }}</p>
                    @endif

                    @if ($claim->status === App\Enums\ClaimStatus::Approved)
                        {{-- Only the claimant ever sees this code. The finder types it in to confirm the handover. --}}
                        <div class="lm-pickup mt-3">
                            <div>
                                <div class="lm-mono text-muted">Your pickup code</div>
                                <div class="lm-pickup-code">{{ $claim->pickupCode() }}</div>
                            </div>
                            <p class="small mb-0">
                                Show this code to the finder when you collect your item.
                                <strong>Don't share it before you have the item in hand.</strong>
                            </p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{ $claims->links() }}
    @endif
</x-app-layout>
