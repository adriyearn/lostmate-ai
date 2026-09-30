<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Dashboard</h1>
    </x-slot>

    <div class="card">
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
</x-app-layout>
