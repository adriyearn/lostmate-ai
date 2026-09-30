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

                        @if ($claim->status->value === 'approved')
                            <span class="badge text-bg-info">Approved &mdash; coordinate with the finder for handover</span>
                        @endif
                    </div>

                    <p class="small mb-1 mt-2"><strong>Your identifying details:</strong> {{ $claim->identifying_details }}</p>

                    @if ($claim->finder_response)
                        <p class="small mb-0"><strong>Finder's response:</strong> {{ $claim->finder_response }}</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{ $claims->links() }}
    @endif
</x-app-layout>
