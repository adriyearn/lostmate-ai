<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h1 class="h4 mb-0">{{ $foundItem->item_name }}</h1>
            <x-status-badge :status="$foundItem->status" />
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-7">
            @if ($foundItem->images->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach ($foundItem->images as $image)
                        <img src="{{ $image->url }}" alt="Photo of {{ $foundItem->item_name }}" class="rounded" style="width: 150px; height: 150px; object-fit: cover;">
                    @endforeach
                </div>
            @endif
            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="h6">Public Description</h2>
                    <p class="mb-0">{{ $foundItem->description }}</p>
                </div>
            </div>
            <div class="card border-warning">
                <div class="card-body">
                    <h2 class="h6 mb-1">Hidden Details <span class="badge text-bg-warning">Private</span></h2>
                    <p class="mb-0">{{ $foundItem->hidden_details ?: 'No hidden details recorded.' }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card mb-3">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-5">Category</dt>
                        <dd class="col-7">{{ $foundItem->category->name }}</dd>
                        <dt class="col-5">Color</dt>
                        <dd class="col-7">{{ $foundItem->color ?? '—' }}</dd>
                        <dt class="col-5">Brand</dt>
                        <dd class="col-7">{{ $foundItem->brand ?? '—' }}</dd>
                        <dt class="col-5">Location</dt>
                        <dd class="col-7">{{ $foundItem->location_found }}</dd>
                        <dt class="col-5">Date Found</dt>
                        <dd class="col-7">{{ $foundItem->date_found->format('M j, Y') }}</dd>
                        <dt class="col-5">Currently At</dt>
                        <dd class="col-7">
                            {{ $foundItem->current_location ?? '—' }}
                            @if ($foundItem->isAtOffice())
                                <div class="small text-muted">Received {{ $foundItem->surrendered_at->format('M j, Y') }} by {{ $foundItem->surrenderedTo?->name ?? 'former staff' }}</div>
                            @endif
                        </dd>
                        <dt class="col-5">Reporter</dt>
                        <dd class="col-7">
                            <a href="{{ route('admin.users.show', $foundItem->user) }}">{{ $foundItem->user->name }}</a>
                            ({{ $foundItem->user->email }})
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('found-items.show', $foundItem) }}" class="btn btn-outline-secondary">View Public Page</a>
                <a href="{{ route('admin.office.tag', $foundItem) }}" class="btn btn-outline-secondary"><i class="bi bi-qr-code"></i> Print claim tag</a>

                @if (! $foundItem->isAtOffice() && ! in_array($foundItem->status->value, ['returned', 'closed'], true))
                    <form method="POST" action="{{ route('admin.office.receive', $foundItem) }}"
                          onsubmit="return confirm('Confirm the finder handed this item in at the office?');">
                        @csrf
                        <button type="submit" class="btn btn-dark"><i class="bi bi-building-check"></i> Received at office</button>
                    </form>
                @endif

                @if ($foundItem->status->value !== 'closed')
                    <form method="POST" action="{{ route('admin.reports.close-found', $foundItem) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">Close Report</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.reports.destroy-found', $foundItem) }}"
                      onsubmit="return confirm('Delete this found item report? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
