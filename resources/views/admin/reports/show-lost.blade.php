<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h1 class="h4 mb-0">{{ $lostItem->item_name }}</h1>
            <x-status-badge :status="$lostItem->status" />
        </div>
    </x-slot>

    <div class="row">
        <div class="col-md-7">
            @if ($lostItem->images->isNotEmpty())
                <div class="d-flex flex-wrap gap-2 mb-3">
                    @foreach ($lostItem->images as $image)
                        <img src="{{ $image->url }}" alt="Photo of {{ $lostItem->item_name }}" class="rounded" style="width: 150px; height: 150px; object-fit: cover;">
                    @endforeach
                </div>
            @endif
            <div class="card">
                <div class="card-body">
                    <h2 class="h6">Description</h2>
                    <p class="mb-0">{{ $lostItem->description }}</p>
                </div>
            </div>
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
                        <dt class="col-5">Location</dt>
                        <dd class="col-7">{{ $lostItem->location_lost }}</dd>
                        <dt class="col-5">Date Lost</dt>
                        <dd class="col-7">{{ $lostItem->date_lost->format('M j, Y') }}</dd>
                        <dt class="col-5">Reporter</dt>
                        <dd class="col-7">
                            <a href="{{ route('admin.users.show', $lostItem->user) }}">{{ $lostItem->user->name }}</a>
                            ({{ $lostItem->user->email }})
                        </dd>
                    </dl>
                </div>
            </div>

            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('lost-items.show', $lostItem) }}" class="btn btn-outline-secondary">View Public Page</a>

                @if ($lostItem->status->value !== 'closed')
                    <form method="POST" action="{{ route('admin.reports.close-lost', $lostItem) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-primary">Close Report</button>
                    </form>
                @endif

                <form method="POST" action="{{ route('admin.reports.destroy-lost', $lostItem) }}"
                      onsubmit="return confirm('Delete this lost item report? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
