@props(['item', 'type'])

@php
    $isLost = $type === 'lost';
    $routeName = $isLost ? 'lost-items.show' : 'found-items.show';
    $location = $isLost ? $item->location_lost : $item->location_found;
    $date = $isLost ? $item->date_lost : $item->date_found;
    $thumbnail = $item->images->first();

    // Tag number printed on the card, e.g. "L-0012" for lost item #12.
    $tagNumber = ($isLost ? 'L-' : 'F-').str_pad($item->id, 4, '0', STR_PAD_LEFT);
@endphp

{{-- Each report is drawn as a paper claim tag: eyelet hole, photo, tear-off line, details. --}}
<a href="{{ route($routeName, $item) }}" class="card h-100 lm-item-card lm-tag text-reset">
    <div class="lm-thumb-wrap">
        <span class="lm-eyelet"></span>

        @if ($thumbnail)
            <img src="{{ $thumbnail->url }}" class="lm-thumb" alt="{{ $item->item_name }}" loading="lazy">
        @else
            <div class="lm-thumb-empty">
                <i class="bi {{ $isLost ? 'bi-question-diamond' : 'bi-box-seam' }}"></i>
                No photo
            </div>
        @endif

        <div class="lm-thumb-badges">
            <span class="lm-type-chip {{ $isLost ? 'is-lost' : 'is-found' }}">
                <i class="bi {{ $isLost ? 'bi-exclamation-lg' : 'bi-check-lg' }}"></i>
                {{ $isLost ? 'Lost' : 'Found' }}
            </span>
            <x-status-badge :status="$item->status" />
        </div>
    </div>

    <div class="card-body lm-tag-body p-3 pt-3">
        <div class="d-flex justify-content-between align-items-baseline gap-2 mb-2">
            <h3 class="lm-tag-title mb-0 text-truncate" title="{{ $item->item_name }}">{{ $item->item_name }}</h3>
            <span class="lm-tag-no">№ {{ $tagNumber }}</span>
        </div>
        <div class="d-flex flex-column gap-1">
            <span class="lm-meta"><i class="bi bi-tag"></i> {{ $item->category->name }}</span>
            <span class="lm-meta"><i class="bi bi-geo-alt"></i> <span class="text-truncate">{{ $location }}</span></span>
            <span class="lm-meta"><i class="bi bi-calendar3"></i> {{ $date->format('M j, Y') }}</span>
        </div>
    </div>
</a>
