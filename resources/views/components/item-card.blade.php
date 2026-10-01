@props(['item', 'type'])

@php
    $isLost = $type === 'lost';
    $routeName = $isLost ? 'lost-items.show' : 'found-items.show';
    $location = $isLost ? $item->location_lost : $item->location_found;
    $date = $isLost ? $item->date_lost : $item->date_found;
    $thumbnail = $item->images->first();
@endphp

<a href="{{ route($routeName, $item) }}" class="card h-100 lm-item-card text-reset overflow-hidden">
    @if ($thumbnail)
        <img src="{{ asset('storage/'.$thumbnail->path) }}" class="lm-thumb" alt="{{ $item->item_name }}">
    @else
        <div class="lm-thumb-empty">No photo</div>
    @endif

    <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
            <h3 class="h6 mb-0 text-truncate">{{ $item->item_name }}</h3>
            <x-status-badge :status="$item->status" />
        </div>
        <p class="small text-muted mb-0">
            {{ $item->category->name }} &middot; {{ $location }}
        </p>
        <p class="small text-muted mb-0">{{ $date->format('M j, Y') }}</p>
    </div>
</a>
