<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ItemStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Services\AdminLogger;
use App\Services\ItemStatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemReportController extends Controller
{
    public function __construct(
        protected AdminLogger $adminLogger,
        protected ItemStatusService $statusService,
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'lost') === 'found' ? 'found' : 'lost';

        $filters = [
            'category_id' => $request->query('category_id', ''),
            'status' => $request->query('status', ''),
        ];

        if ($tab === 'found') {
            $items = FoundItem::with(['user', 'category'])
                ->when($filters['category_id'], fn ($q, $v) => $q->where('category_id', $v))
                ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
                ->latest()
                ->paginate(20)
                ->withQueryString();
        } else {
            $items = LostItem::with(['user', 'category'])
                ->when($filters['category_id'], fn ($q, $v) => $q->where('category_id', $v))
                ->when($filters['status'], fn ($q, $v) => $q->where('status', $v))
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        return view('admin.reports.index', [
            'tab' => $tab,
            'items' => $items,
            'categories' => Category::orderBy('name')->get(),
            'statuses' => ItemStatus::cases(),
            'filters' => $filters,
        ]);
    }

    public function showLost(LostItem $lostItem): View
    {
        $lostItem->load(['user', 'category', 'images']);

        return view('admin.reports.show-lost', ['lostItem' => $lostItem]);
    }

    public function showFound(FoundItem $foundItem): View
    {
        $foundItem->load(['user', 'category', 'images']);

        return view('admin.reports.show-found', ['foundItem' => $foundItem]);
    }

    public function closeLost(Request $request, LostItem $lostItem): RedirectResponse
    {
        $this->statusService->transitionIfPossible($lostItem, ItemStatus::Closed);

        $this->adminLogger->log($request->user(), 'lost_item.closed', $lostItem);

        return back()->with('success', 'Lost item report closed.');
    }

    public function closeFound(Request $request, FoundItem $foundItem): RedirectResponse
    {
        $this->statusService->transitionIfPossible($foundItem, ItemStatus::Closed);

        $this->adminLogger->log($request->user(), 'found_item.closed', $foundItem);

        return back()->with('success', 'Found item report closed.');
    }

    public function destroyLost(Request $request, LostItem $lostItem): RedirectResponse
    {
        $this->adminLogger->log($request->user(), 'lost_item.deleted', $lostItem, $lostItem->item_name);

        $lostItem->delete();

        return redirect()->route('admin.reports.index', ['tab' => 'lost'])->with('success', 'Lost item report deleted.');
    }

    public function destroyFound(Request $request, FoundItem $foundItem): RedirectResponse
    {
        $this->adminLogger->log($request->user(), 'found_item.deleted', $foundItem, $foundItem->item_name);

        $foundItem->delete();

        return redirect()->route('admin.reports.index', ['tab' => 'found'])->with('success', 'Found item report deleted.');
    }
}
