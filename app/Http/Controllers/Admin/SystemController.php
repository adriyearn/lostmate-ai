<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SystemController extends Controller
{
    /**
     * Health of the background queue, shown on the admin dashboard:
     *  - waiting:  jobs not processed yet (AI matching, emails)
     *  - failed:   jobs that gave up after all retries
     *  - oldestWaitingMinutes: if this keeps growing, the worker is off
     */
    public static function health(): array
    {
        $oldest = DB::table('jobs')->min('created_at');

        return [
            'waiting' => DB::table('jobs')->count(),
            'failed' => DB::table('failed_jobs')->count(),
            'oldestWaitingMinutes' => $oldest ? (int) floor((time() - $oldest) / 60) : null,
        ];
    }

    /**
     * Put every failed job back in the queue (e.g. after the AI service
     * was down). Logged to admin_logs like every admin action.
     */
    public function retryFailed(Request $request, AdminLogger $logger): RedirectResponse
    {
        $count = DB::table('failed_jobs')->count();

        if ($count === 0) {
            return back()->with('success', 'There are no failed jobs to retry.');
        }

        Artisan::call('queue:retry', ['id' => ['all']]);

        $logger->log($request->user(), 'system.retried_failed_jobs', null, "Retried {$count} failed background job(s).");

        return back()->with('success', "{$count} failed job(s) were sent back to the queue.");
    }
}
