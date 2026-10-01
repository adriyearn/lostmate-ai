<x-app-layout>
    <x-slot name="header">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <div>
                <h1>Browse reports</h1>
                <p class="text-muted mb-0 mt-1">Search everything reported on campus. Spot your item? Open it to contact the finder.</p>
            </div>
            <ul class="nav nav-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'lost' ? 'active' : '' }}" href="{{ route('browse.index', ['tab' => 'lost']) }}">
                        <i class="bi bi-exclamation-circle"></i> Lost items
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab === 'found' ? 'active' : '' }}" href="{{ route('browse.index', ['tab' => 'found']) }}">
                        <i class="bi bi-box-seam"></i> Found items
                    </a>
                </li>
            </ul>
        </div>
    </x-slot>

    <form method="GET" action="{{ route('browse.index') }}" class="card mb-4">
        <div class="card-body p-3 p-md-4">
            <input type="hidden" name="tab" value="{{ $tab }}">

            <div class="d-flex flex-column flex-md-row gap-2 mb-3">
                <div class="lm-input-icon flex-grow-1">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" value="{{ $filters['q'] }}" class="form-control form-control-lg" style="font-size: .95rem;" placeholder="Search by item name, description, color, brand, or location...">
                </div>
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-search"></i> Search</button>
            </div>

            <div class="row g-2 align-items-end">
                <div class="col-6 col-md">
                    <label class="form-label small">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($filters['category_id'] == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md">
                    <label class="form-label small">Location</label>
                    <select name="location" class="form-select form-select-sm">
                        <option value="">All locations</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location }}" @selected($filters['location'] == $location)>{{ $location }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Any status</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md">
                    <label class="form-label small">From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md">
                    <label class="form-label small">To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md">
                    <label class="form-label small">Sort</label>
                    <select name="sort" class="form-select form-select-sm">
                        <option value="newest" @selected($filters['sort'] === 'newest')>Newest first</option>
                        <option value="oldest" @selected($filters['sort'] === 'oldest')>Oldest first</option>
                    </select>
                </div>
                <div class="col-12 col-md-auto d-flex gap-2">
                    <button type="submit" class="btn btn-outline-secondary btn-sm"><i class="bi bi-funnel"></i> Apply</button>
                    <a href="{{ route('browse.index', ['tab' => $tab]) }}" class="btn btn-link btn-sm">Reset</a>
                </div>
            </div>
        </div>
    </form>

    @if ($items->isEmpty())
        <div class="card">
            <div class="lm-empty">
                <i class="bi bi-search"></i>
                <div class="fw-semibold text-dark mb-1">No {{ $tab }} items match your filters.</div>
                <div class="small">Try a different keyword or reset the filters.</div>
            </div>
        </div>
    @else
        <p class="small text-muted mb-3">{{ $items->total() }} {{ \Illuminate\Support\Str::plural('result', $items->total()) }}</p>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 row-cols-xl-4 g-3 mb-4">
            @foreach ($items as $item)
                <div class="col">
                    <x-item-card :item="$item" :type="$tab" />
                </div>
            @endforeach
        </div>

        {{ $items->links() }}
    @endif
</x-app-layout>
