<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminLogController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'admin_id' => $request->query('admin_id', ''),
            'action' => $request->query('action', ''),
            'date_from' => $request->query('date_from', ''),
            'date_to' => $request->query('date_to', ''),
        ];

        $logs = AdminLog::with('admin')
            ->when($filters['admin_id'], fn ($q, $v) => $q->where('admin_id', $v))
            ->when($filters['action'], fn ($q, $v) => $q->where('action', $v))
            ->when($filters['date_from'], fn ($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['date_to'], fn ($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.logs.index', [
            'logs' => $logs,
            'admins' => User::where('role', 'admin')->orderBy('name')->get(),
            'actions' => AdminLog::query()->distinct()->orderBy('action')->pluck('action'),
            'filters' => $filters,
        ]);
    }
}
