<?php

namespace App\Http\Controllers;

use App\Enums\ItemStatus;
use App\Exceptions\InvalidStatusTransitionException;
use App\Http\Requests\ApproveClaimRequest;
use App\Http\Requests\ConfirmReturnedRequest;
use App\Http\Requests\RejectClaimRequest;
use App\Http\Requests\StoreClaimRequest;
use App\Models\Claim;
use App\Models\FoundItem;
use App\Services\ClaimService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClaimController extends Controller
{
    public function __construct(protected ClaimService $claims) {}

    public function create(Request $request, FoundItem $foundItem): View
    {
        $this->authorize('create', [Claim::class, $foundItem]);

        abort_unless(
            in_array($foundItem->status, [ItemStatus::Open, ItemStatus::Matched], true),
            403,
            'This item is no longer available to claim.'
        );

        $lostItems = $request->user()->lostItems()
            ->whereIn('status', [ItemStatus::Open, ItemStatus::Matched])
            ->orderByDesc('created_at')
            ->get();

        return view('claims.create', [
            'foundItem' => $foundItem,
            'lostItems' => $lostItems,
            'aiMatchId' => $request->query('ai_match_id'),
            'lostItemId' => $request->query('lost_item_id'),
        ]);
    }

    public function store(StoreClaimRequest $request, FoundItem $foundItem): RedirectResponse
    {
        $proofImagePath = $request->hasFile('proof_image')
            ? $request->file('proof_image')->store('claim-proofs', 'public')
            : null;

        try {
            $this->claims->submit($request->user(), $foundItem, [
                ...$request->safe()->except(['proof_image']),
                'proof_image_path' => $proofImagePath,
            ]);
        } catch (InvalidStatusTransitionException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('my-claims.index')
            ->with('success', 'Your claim has been submitted. The finder will review it.');
    }

    public function approve(ApproveClaimRequest $request, Claim $claim): RedirectResponse
    {
        $this->claims->approve($claim, $request->validated('response'));

        return back()->with('success', 'Claim approved. Other pending claims on this item were automatically rejected.');
    }

    public function reject(RejectClaimRequest $request, Claim $claim): RedirectResponse
    {
        $this->claims->reject($claim, $request->validated('response'));

        return back()->with('success', 'Claim rejected.');
    }

    public function cancel(Claim $claim): RedirectResponse
    {
        $this->authorize('cancel', $claim);

        try {
            $this->claims->cancel($claim);
        } catch (InvalidStatusTransitionException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Your claim was cancelled.');
    }

    /**
     * Authorization and the pickup-code check happen in ConfirmReturnedRequest.
     */
    public function confirmReturned(ConfirmReturnedRequest $request, Claim $claim): RedirectResponse
    {
        try {
            $this->claims->confirmReturned($claim);
        } catch (InvalidStatusTransitionException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Marked as returned. Thanks for helping reunite it with its owner!');
    }
}
