<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">{{ $foundItem->item_name }}</h1>
            <x-status-badge :status="$foundItem->status" />
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-7">
            @if ($foundItem->images->isNotEmpty())
                <div id="itemGallery" class="carousel slide mb-3" data-bs-ride="false">
                    <div class="carousel-inner rounded border bg-body-secondary">
                        @foreach ($foundItem->images as $image)
                            <div class="carousel-item @if ($loop->first) active @endif">
                                <img src="{{ asset('storage/'.$image->path) }}" class="d-block w-100" alt="{{ $foundItem->item_name }}" style="max-height: 420px; object-fit: contain;">
                            </div>
                        @endforeach
                    </div>
                    @if ($foundItem->images->count() > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#itemGallery" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#itemGallery" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
            @else
                <div class="rounded border bg-body-secondary d-flex align-items-center justify-content-center text-muted mb-3" style="height: 300px;">
                    No photos provided
                </div>
            @endif
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

                        <dt class="col-5">Location Found</dt>
                        <dd class="col-7">{{ $foundItem->location_found }}</dd>

                        <dt class="col-5">Date Found</dt>
                        <dd class="col-7">
                            {{ $foundItem->date_found->format('M j, Y') }}
                            @if ($foundItem->time_found)
                                at {{ \Illuminate\Support\Carbon::parse($foundItem->time_found)->format('g:i A') }}
                            @endif
                        </dd>

                        <dt class="col-5">Currently At</dt>
                        <dd class="col-7">{{ $foundItem->current_location ?? '—' }}</dd>

                        <dt class="col-5">Reported by</dt>
                        <dd class="col-7">{{ $foundItem->user->name }}</dd>
                    </dl>
                </div>
            </div>

            @can('viewHiddenDetails', $foundItem)
                <div class="card mb-3 border-warning">
                    <div class="card-body">
                        <h2 class="h6 mb-1">Hidden Details <span class="badge text-bg-warning">Private</span></h2>
                        <p class="small text-muted mb-2">Only you and administrators can see this.</p>
                        <p class="mb-0">{{ $foundItem->hidden_details ?: 'No hidden details recorded.' }}</p>
                    </div>
                </div>
            @endcan

            @can('viewMatches', $foundItem)
                <a href="{{ route('found-items.matches', $foundItem) }}" class="btn btn-info mb-2 w-100">
                    View Possible Matches
                </a>
            @endcan

            @if (auth()->id() !== $foundItem->user_id)
                <form method="POST" action="{{ route('found-items.contact', $foundItem) }}" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">
                        This might be mine / Contact finder
                    </button>
                </form>
            @endif

            @can('update', $foundItem)
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('found-items.edit', $foundItem) }}" class="btn btn-outline-secondary">Edit</a>
                    <form method="POST" action="{{ route('found-items.destroy', $foundItem) }}"
                          onsubmit="return confirm('Delete this found item report? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>Delete</x-danger-button>
                    </form>

                    @can('rerunMatching', $foundItem)
                        <form method="POST" action="{{ route('found-items.rerun-matching', $foundItem) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary">Re-run Matching</button>
                        </form>
                    @endcan
                </div>
            @endcan
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2 class="h6">Description</h2>
            <p class="mb-0">{{ $foundItem->description }}</p>
        </div>
    </div>
</x-app-layout>
