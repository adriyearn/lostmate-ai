<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">{{ $lostItem->item_name }}</h1>
            <x-status-badge :status="$lostItem->status" />
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-7">
            @if ($lostItem->images->isNotEmpty())
                <div id="itemGallery" class="carousel slide mb-3" data-bs-ride="false">
                    <div class="carousel-inner rounded border bg-body-secondary">
                        @foreach ($lostItem->images as $image)
                            <div class="carousel-item @if ($loop->first) active @endif">
                                <img src="{{ asset('storage/'.$image->path) }}" class="d-block w-100" alt="{{ $lostItem->item_name }}" style="max-height: 420px; object-fit: contain;">
                            </div>
                        @endforeach
                    </div>
                    @if ($lostItem->images->count() > 1)
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
                        <dd class="col-7">{{ $lostItem->category->name }}</dd>

                        <dt class="col-5">Color</dt>
                        <dd class="col-7">{{ $lostItem->color ?? '—' }}</dd>

                        <dt class="col-5">Brand</dt>
                        <dd class="col-7">{{ $lostItem->brand ?? '—' }}</dd>

                        <dt class="col-5">Location Lost</dt>
                        <dd class="col-7">{{ $lostItem->location_lost }}</dd>

                        <dt class="col-5">Date Lost</dt>
                        <dd class="col-7">
                            {{ $lostItem->date_lost->format('M j, Y') }}
                            @if ($lostItem->time_lost)
                                at {{ \Illuminate\Support\Carbon::parse($lostItem->time_lost)->format('g:i A') }}
                            @endif
                        </dd>

                        <dt class="col-5">Reported by</dt>
                        <dd class="col-7">{{ $lostItem->user->name }}</dd>
                    </dl>
                </div>
            </div>

            @can('viewMatches', $lostItem)
                <a href="{{ route('lost-items.matches', $lostItem) }}" class="btn btn-info mb-2 w-100">
                    View Possible Matches
                </a>
            @endcan

            @if (auth()->id() !== $lostItem->user_id)
                <form method="POST" action="{{ route('lost-items.contact', $lostItem) }}" class="mb-2">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100">
                        I found this / Contact owner
                    </button>
                </form>
            @endif

            @can('update', $lostItem)
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('lost-items.edit', $lostItem) }}" class="btn btn-outline-secondary">Edit</a>
                    <form method="POST" action="{{ route('lost-items.destroy', $lostItem) }}"
                          onsubmit="return confirm('Delete this lost item report? This cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>Delete</x-danger-button>
                    </form>

                    @can('rerunMatching', $lostItem)
                        <form method="POST" action="{{ route('lost-items.rerun-matching', $lostItem) }}">
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
            <p class="mb-0">{{ $lostItem->description }}</p>
        </div>
    </div>
</x-app-layout>
