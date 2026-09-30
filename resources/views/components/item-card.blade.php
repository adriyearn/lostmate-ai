@props(['item', 'type'])

@php
    $isLost = $type === 'lost';
    $routeName = $isLost ? 'lost-items.show' : 'found-items.show';
    $location = $isLost ? $item->location_lost : $item->location_found;
    $date = $isLost ? $item->date_lost : $item->date_found;
    $thumbnail = $item->images->first();
@endphp

<div class="card h-100">
    <a href="{{ route($routeName, $item) }}" class="text-decoration-none text-reset">
        @if ($thumbnail)
            <img src="{{ asset('storage/'.$thumbnail->path) }}" class="card-img-top" alt="{{ $item->item_name }}" style="height: 180px; object-fit: cover;">
        @else
            <div class="card-img-top bg-body-secondary d-flex align-items-center justify-content-center text-muted" style="height: 180px;">
                No photo
            </div>
        @endif

        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <h3 class="h6 mb-0">{{ $item->item_name }}</h3>
                <x-status-badge :status="$item->status" />
            </div>
            <p class="small text-muted mb-1">{{ $item->category->name }}</p>
            <p class="small text-muted mb-0">
                {{ $location }} &middot; {{ $date->format('M j, Y') }}
            </p>
        </div>
    </a>
</div>
