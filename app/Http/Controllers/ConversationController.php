<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMessageRequest;
use App\Models\Conversation;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Notifications\NewMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $conversations = $user->conversations()
            ->with(['userOne.profile', 'userTwo.profile', 'lostItem', 'foundItem', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->orderByDesc('last_message_at')
            ->paginate(15);

        return view('conversations.index', [
            'conversations' => $conversations,
        ]);
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->authorize('view', $conversation);

        $user = $request->user();

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->load(['userOne.profile', 'userTwo.profile', 'lostItem', 'foundItem', 'messages.sender']);

        return view('conversations.show', [
            'conversation' => $conversation,
            'otherUser' => $conversation->otherParticipant($user),
        ]);
    }

    public function storeMessage(StoreMessageRequest $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();

        $message = $conversation->messages()->create([
            'sender_id' => $user->id,
            'body' => $request->validated('body'),
        ]);

        $conversation->update(['last_message_at' => $message->created_at]);

        $conversation->otherParticipant($user)->notify(new NewMessage($message));

        return back();
    }

    /**
     * Polled by the conversation page every 10 seconds for new messages.
     */
    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $user = $request->user();

        $messages = $conversation->messages()
            ->with('sender')
            ->when($request->integer('after'), fn ($q, $afterId) => $q->where('id', '>', $afterId))
            ->orderBy('id')
            ->get();

        $conversation->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $messages->map(fn ($message) => [
                'id' => $message->id,
                'body' => $message->body,
                'sender_name' => $message->sender->name,
                'is_mine' => $message->sender_id === $user->id,
                'sent_at' => $message->created_at->format('M j, g:i A'),
            ]),
        ]);
    }

    public function startFromLostItem(Request $request, LostItem $lostItem): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->id === $lostItem->user_id, 403, "You can't message yourself about your own report.");

        $conversation = Conversation::findOrStartBetween($user, $lostItem->user, lostItem: $lostItem);

        return redirect()->route('conversations.show', $conversation);
    }

    public function startFromFoundItem(Request $request, FoundItem $foundItem): RedirectResponse
    {
        $user = $request->user();
        abort_if($user->id === $foundItem->user_id, 403, "You can't message yourself about your own report.");

        $conversation = Conversation::findOrStartBetween($user, $foundItem->user, foundItem: $foundItem);

        return redirect()->route('conversations.show', $conversation);
    }
}
