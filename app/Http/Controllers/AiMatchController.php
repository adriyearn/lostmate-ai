<?php

namespace App\Http\Controllers;

use App\Enums\AiMatchStatus;
use App\Models\AiMatch;
use App\Models\Conversation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AiMatchController extends Controller
{
    public function dismiss(Request $request, AiMatch $aiMatch): RedirectResponse
    {
        $this->authorize('manage', $aiMatch);

        $aiMatch->update([
            'status' => AiMatchStatus::Dismissed,
            'actioned_by' => $request->user()->id,
        ]);

        return back()->with('success', 'That match has been dismissed.');
    }

    public function startConversation(Request $request, AiMatch $aiMatch): RedirectResponse
    {
        $this->authorize('manage', $aiMatch);

        $aiMatch->loadMissing(['lostItem.user', 'foundItem.user']);

        $lostReporter = $aiMatch->lostItem->user;
        $finder = $aiMatch->foundItem->user;

        $conversation = Conversation::firstOrCreate([
            'user_one_id' => $lostReporter->id,
            'user_two_id' => $finder->id,
            'lost_item_id' => $aiMatch->lost_item_id,
            'found_item_id' => $aiMatch->found_item_id,
        ], [
            'ai_match_id' => $aiMatch->id,
            'last_message_at' => now(),
        ]);

        return redirect()->route('conversations.show', $conversation);
    }
}
