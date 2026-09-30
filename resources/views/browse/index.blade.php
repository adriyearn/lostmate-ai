<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Browse Reports</h1>
    </x-slot>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'lost' ? 'active' : '' }}" href="{{ route('browse.index', ['tab' => 'lost']) }}">
                Lost Items
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'found' ? 'active' : '' }}" href="{{ route('browse.index', ['tab' => 'found']) }}">
                Found Items
            </a>
        </li>
    </ul>

    <form method="GET" action="{{ route('browse.index') }}" class="card mb-4">
        <div class="card-body">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small">Keyword</label>
                    <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control" placeholder="Item name, description, color, brand...">
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($filters['category_id'] == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Location</label>
                    <select name="location" class="form-select">
                        <option value="">All</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}" @selected($filters['location'] == $location)>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Sort</label>
                    <select name="sort" class="form-select">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest first</option>
                    </select>
                </div>
            </div>

            <div class="row g-2 mt-1">
                <div class="col-md-2">
                    <label class="form-label small">Date from</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Date to</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('browse.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </div>
    </form>

    @if ($items->isEmpty())
        <p class="text-muted">No {{ $tab }} items match your filters.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mb-3">
            @foreach ($items as $item)
                <div class="col">
                    <x-item-card :item="$item" :type="$tab" />
                </div>
            @endforeach
        </div>

        {{ $items->links() }}
    @endif
</x-app-layout>
