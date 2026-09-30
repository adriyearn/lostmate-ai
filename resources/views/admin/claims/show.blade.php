<x-admin-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Claim #{{ $claim->id }}</h1>
    </x-slot>

    <div class="row">
        <div class="col-md-7">
            <div class="card mb-3">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-4">Found Item</dt>
                        <dd class="col-8"><a href="{{ route('admin.reports.show-found', $claim->foundItem) }}">{{ $claim->foundItem->item_name }}</a></dd>

                        <dt class="col-4">Claimant</dt>
                        <dd class="col-8"><a href="{{ route('admin.users.show', $claim->claimant) }}">{{ $claim->claimant->name }}</a></dd>

                        <dt class="col-4">Finder</dt>
                        <dd class="col-8"><a href="{{ route('admin.users.show', $claim->foundItem->user) }}">{{ $claim->foundItem->user->name }}</a></dd>

                        <dt class="col-4">Status</dt>
                        <dd class="col-8"><span class="badge {{ $claim->status->badgeClass() }}">{{ $claim->status->label() }}</span></dd>

                        @if ($claim->lostItem)
                            <dt class="col-4">Linked Lost Item</dt>
                            <dd class="col-8"><a href="{{ route('admin.reports.show-lost', $claim->lostItem) }}">{{ $claim->lostItem->item_name }}</a></dd>
                        @endif

                        <dt class="col-4">Submitted</dt>
                        <dd class="col-8">{{ $claim->created_at->format('M j, Y g:i A') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="h6">Claimant's Identifying Details</h2>
                    <p class="mb-0">{{ $claim->identifying_details }}</p>
                </div>
            </div>

            <div class="card mb-3 border-warning">
                <div class="card-body">
                    <h2 class="h6">Finder's Hidden Details <span class="badge text-bg-warning">Private</span></h2>
                    <p class="mb-0">{{ $claim->foundItem->hidden_details ?: 'None recorded.' }}</p>
                </div>
            </div>

            @if ($claim->finder_response)
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="h6">Finder's Response</h2>
                        <p class="mb-0">{{ $claim->finder_response }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6">Override Status</h2>
                    <p class="small text-muted">
                        Manually sets the claim's status. This does not change the linked item's status -
                        use the report pages for that.
                    </p>
                    <form method="POST" action="{{ route('admin.claims.override', $claim) }}">
                        @csrf
                        <div class="mb-2">
                            <select name="status" class="form-select form-select-sm">
                                @foreach (\App\Enums\ClaimStatus::cases() as $s)
                                    <option value="{{ $s->value }}" @selected($claim->status === $s)>{{ $s->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea name="note" class="form-control form-control-sm" rows="2" placeholder="Required note explaining the override" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Override</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
