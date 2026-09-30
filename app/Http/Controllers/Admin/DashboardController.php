<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStatus;
use App\Enums\ItemStatus;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $recoveredFoundItems = FoundItem::whereIn('status', [ItemStatus::Returned, ItemStatus::Closed])->count();
        $totalFoundItems = FoundItem::count();

        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'lostItems' => LostItem::count(),
                'foundItems' => FoundItem::count(),
                'openReports' => LostItem::where('status', ItemStatus::Open)->count()
                    + FoundItem::where('status', ItemStatus::Open)->count(),
                'returnedItems' => LostItem::where('status', ItemStatus::Returned)->count()
                    + FoundItem::where('status', ItemStatus::Returned)->count(),
                'pendingClaims' => Claim::where('status', ClaimStatus::Pending)->count(),
                'pendingFlags' => Report::where('status', ReportStatus::Pending)->count(),
                'recoveryRate' => $totalFoundItems > 0
                    ? round(($recoveredFoundItems / $totalFoundItems) * 100)
                    : 0,
            ],
            'chartData' => $this->monthlyReportCounts(),
        ]);
    }

    protected function monthlyReportCounts(): array
    {
        $months = collect(range(5, 0))->map(fn ($i) => now()->subMonths($i)->format('Y-m'));
        $windowStart = now()->subMonths(5)->startOfMonth();

        $lostByMonth = LostItem::where('date_lost', '>=', $windowStart)
            ->get()
            ->groupBy(fn ($item) => $item->date_lost->format('Y-m'))
            ->map->count();

        $foundByMonth = FoundItem::where('date_found', '>=', $windowStart)
            ->get()
            ->groupBy(fn ($item) => $item->date_found->format('Y-m'))
            ->map->count();

        return [
            'labels' => $months->map(fn ($m) => Carbon::createFromFormat('Y-m', $m)->format('M Y'))->values()->all(),
            'lost' => $months->map(fn ($m) => $lostByMonth[$m] ?? 0)->values()->all(),
            'found' => $months->map(fn ($m) => $foundByMonth[$m] ?? 0)->values()->all(),
        ];
    }
}
