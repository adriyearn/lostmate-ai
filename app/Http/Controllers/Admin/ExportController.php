<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportReportRequest;
use App\Services\AdminLogger;
use App\Services\ReportSummaryService;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function __construct(protected ReportSummaryService $reports) {}

    /**
     * Printable summary for a date range: totals, recovery rate, and where
     * and what gets lost most.
     */
    public function index(ExportReportRequest $request): View
    {
        $from = $request->from();
        $to = $request->to();

        return view('admin.exports.index', [
            'from' => $from,
            'to' => $to,
            'summary' => $this->reports->summary($from, $to),
            'topLocations' => $this->reports->topLocations($from, $to),
            'topCategories' => $this->reports->topCategories($from, $to),
        ]);
    }

    /**
     * Download every lost and found report in the range as a CSV file
     * (opens in Excel or Google Sheets). Logged to admin_logs.
     */
    public function csv(ExportReportRequest $request, AdminLogger $logger): StreamedResponse
    {
        $from = $request->from();
        $to = $request->to();

        $logger->log($request->user(), 'report.exported', null,
            "Exported reports from {$from->toDateString()} to {$to->toDateString()}.");

        $filename = "lostmate-reports-{$from->toDateString()}-to-{$to->toDateString()}.csv";

        return response()->streamDownload(function () use ($from, $to) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 marker so Excel shows accents (e.g. ñ) correctly

            fputcsv($out, ReportSummaryService::CSV_HEADINGS);

            foreach ($this->reports->csvRows($from, $to) as $row) {
                fputcsv($out, $row);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
