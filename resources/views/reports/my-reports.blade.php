<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">My Reports</h1>
    </x-slot>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'lost' ? 'active' : '' }}" href="{{ route('my-reports.index', ['tab' => 'lost']) }}">
                Lost ({{ $lostItems->total() }})
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'found' ? 'active' : '' }}" href="{{ route('my-reports.index', ['tab' => 'found']) }}">
                Found ({{ $foundItems->total() }})
            </a>
        </li>
    </ul>

    @if ($tab === 'found')
        @if ($foundItems->isEmpty())
            <p class="text-muted">You haven't reported any found items yet. <a href="{{ route('found-items.create') }}">Report one</a>.</p>
        @else
            <div class="list-group mb-3">
                @foreach ($foundItems as $item)
                    <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <a href="{{ route('found-items.show', $item) }}" class="fw-semibold text-decoration-none">{{ $item->item_name }}</a>
                            <x-status-badge :status="$item->status" class="ms-2" />
                            <div class="small text-muted">{{ $item->category->name }} &middot; {{ $item->location_found }} &middot; {{ $item->date_found->format('M j, Y') }}</div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('found-items.matches', $item) }}" class="btn btn-sm btn-outline-secondary">
                                Matches
                                @if ($item->suggested_matches_count > 0)
                                    <span class="badge text-bg-primary">{{ $item->suggested_matches_count }}</span>
                                @endif
                            </a>
                            <a href="{{ route('found-items.claims', $item) }}" class="btn btn-sm {{ $item->pending_claims_count > 0 ? 'btn-warning' : 'btn-outline-secondary' }}">
                                Claims
                                @if ($item->pending_claims_count > 0)
                                    <span class="badge text-bg-dark">{{ $item->pending_claims_count }}</span>
                                @endif
                            </a>
                            <a href="{{ route('found-items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('found-items.destroy', $item) }}"
                                  onsubmit="return confirm('Delete this found item report? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            {{ $foundItems->links() }}
        @endif
    @else
        @if ($lostItems->isEmpty())
            <p class="text-muted">You haven't reported any lost items yet. <a href="{{ route('lost-items.create') }}">Report one</a>.</p>
        @else
            <div class="list-group mb-3">
                @foreach ($lostItems as $item)
                    <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <a href="{{ route('lost-items.show', $item) }}" class="fw-semibold text-decoration-none">{{ $item->item_name }}</a>
                            <x-status-badge :status="$item->status" class="ms-2" />
                            <div class="small text-muted">{{ $item->category->name }} &middot; {{ $item->location_lost }} &middot; {{ $item->date_lost->format('M j, Y') }}</div>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <a href="{{ route('lost-items.matches', $item) }}" class="btn btn-sm btn-outline-secondary">
                                Matches
                                @if ($item->suggested_matches_count > 0)
                                    <span class="badge text-bg-primary">{{ $item->suggested_matches_count }}</span>
                                @endif
                            </a>
                            <a href="{{ route('lost-items.edit', $item) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            <form method="POST" action="{{ route('lost-items.destroy', $item) }}"
                                  onsubmit="return confirm('Delete this lost item report? This cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            {{ $lostItems->links() }}
        @endif
    @endif
</x-app-layout>
