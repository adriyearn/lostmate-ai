<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-3">
            <div>
                <p class="text-muted small mb-1">{{ auth()->user()->role->label() }}</p>
                <h1>Welcome back, {{ auth()->user()->name }}</h1>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('lost-items.create') }}" class="btn btn-primary">Report lost item</a>
                <a href="{{ route('found-items.create') }}" class="btn btn-outline-secondary">Report found item</a>
            </div>
        </div>
    </x-slot>

    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
        <h2 class="lm-section-title">Recently lost</h2>
        <a href="{{ route('browse.index', ['tab' => 'lost']) }}" class="small text-muted">View all &rarr;</a>
    </div>
    @if ($recentLostItems->isEmpty())
        <p class="text-muted">No lost items reported yet.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mb-5">
            @foreach ($recentLostItems as $item)
                <div class="col">
                    <x-item-card :item="$item" type="lost" />
                </div>
            @endforeach
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="lm-section-title">Recently found</h2>
        <a href="{{ route('browse.index', ['tab' => 'found']) }}" class="small text-muted">View all &rarr;</a>
    </div>
    @if ($recentFoundItems->isEmpty())
        <p class="text-muted">No found items reported yet.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
            @foreach ($recentFoundItems as $item)
                <div class="col">
                    <x-item-card :item="$item" type="found" />
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
