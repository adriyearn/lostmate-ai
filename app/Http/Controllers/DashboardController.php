<?php

namespace App\Http\Controllers;

use App\Enums\AiMatchStatus;
use App\Enums\ClaimStatus;
use App\Models\AiMatch;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $userId = $user->id;

        // AI suggestions involving any of this user's reports, from either side.
        $matchesQuery = AiMatch::query()
            ->where('status', AiMatchStatus::Suggested)
            ->has('lostItem')
            ->has('foundItem')
            ->where(function ($query) use ($userId) {
                $query->whereHas('lostItem', fn ($q) => $q->where('user_id', $userId))
                    ->orWhereHas('foundItem', fn ($q) => $q->where('user_id', $userId));
            });

        $possibleMatches = (clone $matchesQuery)
            ->with(['lostItem.category', 'lostItem.images', 'foundItem.category', 'foundItem.images'])
            ->orderByDesc('score')
            ->take(6)
            ->get();

        return view('dashboard', [
            'stats' => [
                'lost' => $user->lostItems()->count(),
                'found' => $user->foundItems()->count(),
                'matches' => $matchesQuery->count(),
                'pendingClaims' => Claim::where('status', ClaimStatus::Pending)
                    ->whereHas('foundItem', fn ($q) => $q->where('user_id', $userId))
                    ->count(),
            ],
            'possibleMatches' => $possibleMatches,
            'recentLostItems' => LostItem::with('category', 'images')->latest()->take(4)->get(),
            'recentFoundItems' => FoundItem::with('category', 'images')->latest()->take(4)->get(),
        ]);
    }
}
