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

        $conversation = Conversation::findOrStartBetween(
            $aiMatch->lostItem->user,
            $aiMatch->foundItem->user,
            lostItem: $aiMatch->lostItem,
            foundItem: $aiMatch->foundItem,
            aiMatch: $aiMatch,
        );

        return redirect()->route('conversations.show', $conversation);
    }
}
