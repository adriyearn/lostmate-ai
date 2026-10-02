<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <x-avatar :user="$otherUser" size="lg" />
                <div>
                    <h1 class="h4 mb-0">{{ $otherUser->name }}</h1>
                    @if ($conversation->lostItem || $conversation->foundItem)
                        <p class="small text-muted mb-0">
                            About:
                            @if ($conversation->lostItem)
                                <a href="{{ route('lost-items.show', $conversation->lostItem) }}">{{ $conversation->lostItem->item_name }}</a>
                            @endif
                            @if ($conversation->foundItem)
                                <a href="{{ route('found-items.show', $conversation->foundItem) }}">{{ $conversation->foundItem->item_name }}</a>
                            @endif
                        </p>
                    @endif
                </div>
            </div>
            <a href="{{ route('conversations.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Inbox</a>
        </div>
    </x-slot>

    <div class="card mb-3">
        <div class="card-body">
            <div id="messages" class="d-flex flex-column gap-2 mb-3 lm-chat"
                 data-conversation-id="{{ $conversation->id }}"
                 data-poll-url="{{ route('conversations.poll', $conversation) }}"
                 data-current-user-id="{{ auth()->id() }}">
                @foreach ($conversation->messages as $message)
                    <div class="d-flex {{ $message->sender_id === auth()->id() ? 'justify-content-end' : 'justify-content-start' }}" data-message-id="{{ $message->id }}">
                        <div class="lm-bubble {{ $message->sender_id === auth()->id() ? 'lm-bubble-mine' : '' }}">
                            @if ($message->sender_id !== auth()->id())
                                <div class="small fw-semibold">{{ $message->sender->name }}</div>
                            @endif
                            <div>{{ $message->body }}</div>
                            <div class="d-flex justify-content-between align-items-center gap-2 mt-1">
                                <span class="small {{ $message->sender_id === auth()->id() ? 'text-white-50' : 'text-muted' }}">
                                    {{ $message->created_at->format('M j, g:i A') }}
                                </span>
                                <button type="button"
                                        class="btn btn-sm btn-link p-0 report-message-btn {{ $message->sender_id === auth()->id() ? 'text-white-50' : 'text-muted' }}"
                                        data-bs-toggle="modal" data-bs-target="#reportMessageModal"
                                        data-message-id="{{ $message->id }}">
                                    Report
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <form method="POST" action="{{ route('conversations.store-message', $conversation) }}" id="sendMessageForm">
                @csrf
                <div class="input-group">
                    <input type="text" name="body" class="form-control" placeholder="Type a message..." maxlength="2000" required autofocus>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Send</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Report Message Modal -->
    <div class="modal fade" id="reportMessageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="" id="reportMessageForm">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title h5">Report Message</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <x-input-label for="reason" value="Reason" />
                            <select id="reason" name="reason" class="form-select" required>
                                <option value="spam">Spam</option>
                                <option value="inappropriate">Inappropriate content</option>
                                <option value="fraud">Fraud</option>
                                <option value="false_claim">False claim</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <x-input-label for="details" value="Details (optional)" />
                            <textarea id="details" name="details" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Report</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var reportModal = document.getElementById('reportMessageModal');
            var reportForm = document.getElementById('reportMessageForm');

            reportModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var messageId = button.getAttribute('data-message-id');
                reportForm.action = '/messages/' + messageId + '/report';
            });

            var container = document.getElementById('messages');
            var pollUrl = container.dataset.pollUrl;
            var currentUserId = container.dataset.currentUserId;

            function lastMessageId() {
                var last = container.querySelector('[data-message-id]:last-child');
                return last ? last.getAttribute('data-message-id') : 0;
            }

            function escapeHtml(text) {
                var div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            function appendMessage(message) {
                if (container.querySelector('[data-message-id="' + message.id + '"]')) {
                    return;
                }

                var isMine = String(message.is_mine) === '1' || message.is_mine === true;
                var wrapper = document.createElement('div');
                wrapper.className = 'd-flex ' + (isMine ? 'justify-content-end' : 'justify-content-start');
                wrapper.setAttribute('data-message-id', message.id);

                wrapper.innerHTML =
                    '<div class="lm-bubble' + (isMine ? ' lm-bubble-mine' : '') + '">' +
                        (isMine ? '' : '<div class="small fw-semibold">' + escapeHtml(message.sender_name) + '</div>') +
                        '<div>' + escapeHtml(message.body) + '</div>' +
                        '<div class="d-flex justify-content-between align-items-center gap-2 mt-1">' +
                            '<span class="small ' + (isMine ? 'text-white-50' : 'text-muted') + '">' + escapeHtml(message.sent_at) + '</span>' +
                            '<button type="button" class="btn btn-sm btn-link p-0 report-message-btn ' + (isMine ? 'text-white-50' : 'text-muted') + '" data-bs-toggle="modal" data-bs-target="#reportMessageModal" data-message-id="' + message.id + '">Report</button>' +
                        '</div>' +
                    '</div>';

                container.appendChild(wrapper);
                container.scrollTop = container.scrollHeight;
            }

            function poll() {
                fetch(pollUrl + '?after=' + lastMessageId())
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        (data.messages || []).forEach(appendMessage);
                    })
                    .catch(function () { /* silent - will retry on next interval */ });
            }

            container.scrollTop = container.scrollHeight;
            setInterval(poll, 10000);
        });
    </script>
    @endpush
</x-app-layout>
