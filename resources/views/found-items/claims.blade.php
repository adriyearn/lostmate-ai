<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Claims on: {{ $foundItem->item_name }}</h1>
    </x-slot>

    <div class="card mb-3 border-warning">
        <div class="card-body">
            <h2 class="h6 mb-1">Your Hidden Details <span class="badge text-bg-warning">Private</span></h2>
            <p class="mb-0">{{ $foundItem->hidden_details ?: 'No hidden details recorded.' }}</p>
        </div>
    </div>

    @if ($claims->isEmpty())
        <p class="text-muted">No claims yet.</p>
    @else
        @foreach ($claims as $claim)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                        <div>
                            <span class="d-inline-flex align-items-center gap-2"><x-avatar :user="$claim->claimant" size="sm" /><span class="fw-semibold">{{ $claim->claimant->name }}</span></span>
                            <span class="badge {{ $claim->status->badgeClass() }} ms-1">{{ $claim->status->label() }}</span>
                            <div class="small text-muted">Submitted {{ $claim->created_at->format('M j, Y g:i A') }}</div>
                        </div>
                    </div>

                    <p class="mb-2"><strong>Claimant's identifying details:</strong><br>{{ $claim->identifying_details }}</p>

                    @if ($claim->proof_image_path)
                        <img src="{{ asset('storage/'.$claim->proof_image_path) }}" alt="Proof" class="rounded mb-2" style="max-width: 200px;">
                    @endif

                    @if ($claim->lostItem)
                        <p class="small text-muted mb-2">
                            Linked to their lost report:
                            <a href="{{ route('lost-items.show', $claim->lostItem) }}">{{ $claim->lostItem->item_name }}</a>
                        </p>
                    @endif

                    @if ($claim->finder_response)
                        <p class="small mb-2"><strong>Your response:</strong> {{ $claim->finder_response }}</p>
                    @endif

                    @if ($claim->status->value === 'pending')
                        <div class="row g-2">
                            <div class="col-md-8">
                                <form method="POST" action="{{ route('claims.approve', $claim) }}" class="d-flex gap-2">
                                    @csrf
                                    <input type="text" name="response" class="form-control form-control-sm" placeholder="Optional note to claimant">
                                    <button type="submit" class="btn btn-sm btn-success text-nowrap">Approve</button>
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form method="POST" action="{{ route('claims.reject', $claim) }}" class="d-flex gap-2"
                                      onsubmit="return confirm('Reject this claim?');">
                                    @csrf
                                    <input type="text" name="response" class="form-control form-control-sm" placeholder="Reason (required)" required>
                                    <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">Reject</button>
                                </form>
                            </div>
                        </div>
                    @elseif ($claim->status->value === 'approved')
                        <form method="POST" action="{{ route('claims.confirm-returned', $claim) }}"
                              onsubmit="return confirm('Confirm the item has been handed over to the claimant?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary">Confirm Returned</button>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    @endif
</x-app-layout>
