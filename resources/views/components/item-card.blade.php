@props(['item', 'type'])

@php
    $isLost = $type === 'lost';
    $routeName = $isLost ? 'lost-items.show' : 'found-items.show';
    $location = $isLost ? $item->location_lost : $item->location_found;
    $date = $isLost ? $item->date_lost : $item->date_found;
    $thumbnail = $item->images->first();
@endphp

<a href="{{ route($routeName, $item) }}" class="card h-100 lm-item-card text-reset">
    <div class="lm-thumb-wrap">
        @if ($thumbnail)
            <img src="{{ asset('storage/'.$thumbnail->path) }}" class="lm-thumb" alt="{{ $item->item_name }}" loading="lazy">
        @else
            <div class="lm-thumb-empty">
                <i class="bi {{ $isLost ? 'bi-question-diamond' : 'bi-box-seam' }}"></i>
                No photo
            </div>
        @endif

        <div class="lm-thumb-badges">
            <span class="lm-type-chip">
                <i class="bi {{ $isLost ? 'bi-exclamation-circle text-danger' : 'bi-check-circle text-success' }}"></i>
                {{ $isLost ? 'Lost' : 'Found' }}
            </span>
            <x-status-badge :status="$item->status" />
        </div>
    </div>

    <div class="card-body p-3">
        <h3 class="h6 mb-2 text-truncate" title="{{ $item->item_name }}">{{ $item->item_name }}</h3>
        <div class="d-flex flex-column gap-1">
            <span class="lm-meta"><i class="bi bi-tag"></i> {{ $item->category->name }}</span>
            <span class="lm-meta"><i class="bi bi-geo-alt"></i> <span class="text-truncate">{{ $location }}</span></span>
            <span class="lm-meta"><i class="bi bi-calendar3"></i> {{ $date->format('M j, Y') }}</span>
        </div>
    </div>
</a>
