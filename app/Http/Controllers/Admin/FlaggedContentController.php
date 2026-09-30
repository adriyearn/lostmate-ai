<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Notifications\ReportActionTaken;
use App\Services\AdminLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class FlaggedContentController extends Controller
{
    public function __construct(protected AdminLogger $adminLogger) {}

    public function index(Request $request): View
    {
        $status = $request->query('status', '');

        $reports = Report::with(['reporter', 'reportable'])
            ->when($status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.flags.index', [
            'reports' => $reports,
            'statuses' => ReportStatus::cases(),
            'status' => $status,
        ]);
    }

    public function show(Report $report): View
    {
        $report->load(['reporter', 'reportable', 'reviewedBy']);

        return view('admin.flags.show', ['report' => $report]);
    }

    public function updateStatus(Request $request, Report $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', new Enum(ReportStatus::class)],
            'admin_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->update([
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'] ?? null,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $report->reporter->notify(new ReportActionTaken($report));

        $this->adminLogger->log($request->user(), 'report.'.$validated['status'], $report);

        return redirect()->route('admin.flags.index')->with('success', 'Report updated.');
    }
}
