<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ClaimStatus;
use App\Http\Controllers\Controller;
use App\Models\Claim;
use App\Services\AdminLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class ClaimController extends Controller
{
    public function __construct(protected AdminLogger $adminLogger) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', '');

        $claims = Claim::with(['claimant', 'foundItem'])
            ->when($status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.claims.index', [
            'claims' => $claims,
            'statuses' => ClaimStatus::cases(),
            'status' => $status,
        ]);
    }

    public function show(Claim $claim): View
    {
        $claim->load(['claimant', 'foundItem.user', 'lostItem']);

        return view('admin.claims.show', ['claim' => $claim]);
    }

    public function overrideStatus(Request $request, Claim $claim): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', new Enum(ClaimStatus::class)],
            'note' => ['required', 'string', 'max:1000'],
        ]);

        $claim->update([
            'status' => $validated['status'],
            'finder_response' => $claim->finder_response,
            'reviewed_at' => now(),
        ]);

        $this->adminLogger->log(
            $request->user(),
            'claim.status_overridden',
            $claim,
            "Status set to {$validated['status']}. Note: {$validated['note']}",
        );

        return back()->with('success', 'Claim status overridden.');
    }
}
