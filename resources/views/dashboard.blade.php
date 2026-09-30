<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Dashboard</h1>
    </x-slot>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5">Welcome back, {{ auth()->user()->name }}!</h2>
            <p class="text-muted mb-0">
                You're logged in as
                <span class="badge {{ auth()->user()->isAdmin() ? 'text-bg-danger' : 'text-bg-secondary' }}">
                    {{ auth()->user()->role->label() }}
                </span>.
            </p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Recently Lost</h2>
        <a href="{{ route('browse.index', ['tab' => 'lost']) }}" class="small">Browse all &rarr;</a>
    </div>
    @if ($recentLostItems->isEmpty())
        <p class="text-muted">No lost items reported yet.</p>
    @else
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3 mb-4">
            @foreach ($recentLostItems as $item)
                <div class="col">
                    <x-item-card :item="$item" type="lost" />
                </div>
            @endforeach
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Recently Found</h2>
        <a href="{{ route('browse.index', ['tab' => 'found']) }}" class="small">Browse all &rarr;</a>
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
