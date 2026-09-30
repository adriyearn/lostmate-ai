<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Inbox</h1>
    </x-slot>

    @if ($conversations->isEmpty())
        <p class="text-muted">No conversations yet. Start one from an item page or a possible match.</p>
    @else
        <div class="list-group">
            @foreach ($conversations as $conversation)
                @php
                    $other = $conversation->otherParticipant(auth()->user());
                    $lastMessage = $conversation->messages->first();
                    $unread = $conversation->unreadCountFor(auth()->user());
                    $relatedItem = $conversation->lostItem ?? $conversation->foundItem;
                @endphp
                <a href="{{ route('conversations.show', $conversation) }}"
                   class="list-group-item list-group-item-action d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <div class="fw-semibold">
                            {{ $other->name }}
                            @if ($relatedItem)
                                <span class="text-muted fw-normal">&middot; {{ $relatedItem->item_name }}</span>
                            @endif
                        </div>
                        <div class="small text-muted text-truncate" style="max-width: 40rem;">
                            {{ $lastMessage?->body ?? 'No messages yet.' }}
                        </div>
                    </div>
                    @if ($unread > 0)
                        <span class="badge text-bg-primary rounded-pill">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        {{ $conversations->links() }}
    @endif
</x-app-layout>
