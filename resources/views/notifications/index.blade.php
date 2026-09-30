<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0">Notifications</h1>
            @if ($notifications->contains(fn ($n) => is_null($n->read_at)))
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Mark all as read</button>
                </form>
            @endif
        </div>
    </x-slot>

    @if ($notifications->isEmpty())
        <p class="text-muted">No notifications yet.</p>
    @else
        <div class="list-group">
            @foreach ($notifications as $notification)
                <a href="{{ $notification->data['link'] ?? '#' }}"
                   class="list-group-item list-group-item-action {{ $notification->read_at ? '' : 'list-group-item-primary' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <span>{{ $notification->data['message'] ?? 'Notification' }}</span>
                        <span class="small text-muted text-nowrap ms-2">{{ $notification->created_at->diffForHumans() }}</span>
                    </div>
                </a>
            @endforeach
        </div>

        {{ $notifications->links() }}
    @endif
</x-app-layout>
