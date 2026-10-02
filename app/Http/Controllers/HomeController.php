<?php

namespace App\Http\Controllers;

use App\Models\FoundItem;
use App\Models\LostItem;
use App\Services\ReportSummaryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Public landing page for visitors. Logged-in users go straight to
     * their dashboard. Only overall totals are shown here - never item
     * details, photos, names, or contact information.
     */
    public function __invoke(ReportSummaryService $summary): RedirectResponse|View
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('welcome', [
            'stats' => [
                'reports' => LostItem::count() + FoundItem::count(),
                'returned' => $summary->recoveredCount(),
                'recoveryRate' => $summary->recoveryRate(),
            ],
        ]);
    }
}
