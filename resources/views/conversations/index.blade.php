<x-app-layout>
    <x-slot name="header">
        <h1>Messages</h1>
        <p class="text-muted mb-0 mt-1">Talk to finders and owners without sharing your contact details.</p>
    </x-slot>

    @if ($conversations->isEmpty())
        <div class="card">
            <div class="lm-empty">
                <i class="bi bi-chat-dots"></i>
                <div class="fw-semibold text-dark mb-1">No conversations yet</div>
                <div class="small">Start one from an item page or a possible match.</div>
            </div>
        </div>
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
                   class="list-group-item list-group-item-action d-flex align-items-center gap-3">
                    <x-avatar :user="$other" size="lg" />
                    <div class="flex-grow-1 min-w-0" style="min-width: 0;">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <span class="fw-bold text-dark text-truncate">{{ $other->name }}</span>
                            @if ($conversation->last_message_at)
                                <span class="small text-muted text-nowrap">{{ $conversation->last_message_at->diffForHumans() }}</span>
                            @endif
                        </div>
                        @if ($relatedItem)
                            <div class="small fw-semibold" style="color: var(--lm-brand-dark);"><i class="bi bi-tag"></i> {{ $relatedItem->item_name }}</div>
                        @endif
                        <div class="small text-muted text-truncate">
                            {{ $lastMessage?->body ?? 'No messages yet.' }}
                        </div>
                    </div>
                    @if ($unread > 0)
                        <span class="lm-dot position-static">{{ $unread }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="mt-3">{{ $conversations->links() }}</div>
    @endif
</x-app-layout>
