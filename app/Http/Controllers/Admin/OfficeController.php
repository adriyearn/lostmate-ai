<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ItemStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\CloseUnclaimedRequest;
use App\Models\FoundItem;
use App\Services\OfficeCustodyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeController extends Controller
{
    public function __construct(protected OfficeCustodyService $office) {}

    /**
     * Two lists: items currently held at the office, and items past the
     * unclaimed policy that staff should donate or dispose of.
     */
    public function index(Request $request): View
    {
        $tab = $request->query('tab') === 'unclaimed' ? 'unclaimed' : 'office';

        $items = $tab === 'office'
            ? FoundItem::query()
                ->whereNotNull('surrendered_at')
                ->whereNotIn('status', [ItemStatus::Returned, ItemStatus::Closed])
                ->latest('surrendered_at')
            : $this->office->unclaimedQuery();

        return view('admin.office.index', [
            'tab' => $tab,
            'items' => $items->with(['category', 'user', 'surrenderedTo'])->paginate(20)->withQueryString(),
            'officeCount' => FoundItem::whereNotNull('surrendered_at')
                ->whereNotIn('status', [ItemStatus::Returned, ItemStatus::Closed])->count(),
            'unclaimedCount' => $this->office->unclaimedQuery()->count(),
        ]);
    }

    /**
     * Printable claim tag with a QR code that opens the item's page.
     */
    public function tag(FoundItem $foundItem): View
    {
        return view('admin.office.tag', ['foundItem' => $foundItem->load('category')]);
    }

    public function receive(Request $request, FoundItem $foundItem): RedirectResponse
    {
        try {
            $this->office->receive($foundItem, $request->user());
        } catch (InvalidStatusTransitionException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Marked as received at '.config('lostmate.office.name').'. Office staff now handle its claims.');
    }

    public function closeUnclaimed(CloseUnclaimedRequest $request, FoundItem $foundItem): RedirectResponse
    {
        try {
            $this->office->closeUnclaimed($foundItem, $request->user(), $request->validated('note'));
        } catch (InvalidStatusTransitionException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', "\"{$foundItem->item_name}\" was closed as unclaimed.");
    }
}
