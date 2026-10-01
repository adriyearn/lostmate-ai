<?php

namespace App\Http\Controllers;

use App\Enums\AiMatchStatus;
use App\Models\AiMatch;
use App\Models\FoundItem;
use App\Models\LostItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;

        // AI suggestions involving any of this user's reports, from either side.
        $possibleMatches = AiMatch::query()
            ->where('status', AiMatchStatus::Suggested)
            ->has('lostItem')
            ->has('foundItem')
            ->where(function ($query) use ($userId) {
                $query->whereHas('lostItem', fn ($q) => $q->where('user_id', $userId))
                    ->orWhereHas('foundItem', fn ($q) => $q->where('user_id', $userId));
            })
            ->with(['lostItem.category', 'lostItem.images', 'foundItem.category', 'foundItem.images'])
            ->orderByDesc('score')
            ->take(6)
            ->get();

        return view('dashboard', [
            'possibleMatches' => $possibleMatches,
            'recentLostItems' => LostItem::with('category', 'images')->latest()->take(6)->get(),
            'recentFoundItems' => FoundItem::with('category', 'images')->latest()->take(6)->get(),
        ]);
    }
}
