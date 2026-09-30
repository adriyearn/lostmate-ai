<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\FoundItem;
use App\Models\LostItem;
use App\Models\Message;
use Illuminate\Http\RedirectResponse;

class ReportController extends Controller
{
    public function reportMessage(StoreReportRequest $request, Message $message): RedirectResponse
    {
        $this->authorize('view', $message->conversation);

        $message->reports()->create([
            'reporter_id' => $request->user()->id,
            'reason' => $request->validated('reason'),
            'details' => $request->validated('details'),
        ]);

        return back()->with('success', 'Thanks - this message has been reported to the administrators.');
    }

    public function reportLostItem(StoreReportRequest $request, LostItem $lostItem): RedirectResponse
    {
        $lostItem->reports()->create([
            'reporter_id' => $request->user()->id,
            'reason' => $request->validated('reason'),
            'details' => $request->validated('details'),
        ]);

        return back()->with('success', 'Thanks - this report has been flagged for administrators.');
    }

    public function reportFoundItem(StoreReportRequest $request, FoundItem $foundItem): RedirectResponse
    {
        $foundItem->reports()->create([
            'reporter_id' => $request->user()->id,
            'reason' => $request->validated('reason'),
            'details' => $request->validated('details'),
        ]);

        return back()->with('success', 'Thanks - this report has been flagged for administrators.');
    }
}
