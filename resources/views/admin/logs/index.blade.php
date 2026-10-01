<x-admin-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Admin Logs</h1>
    </x-slot>

    <form method="GET" action="{{ route('admin.logs.index') }}" class="row g-2 mb-3">
        <div class="col-auto">
            <select name="admin_id" class="form-select form-select-sm">
                <option value="">All Admins</option>
                @foreach ($admins as $admin)
                    <option value="{{ $admin->id }}" @selected($filters['admin_id'] == $admin->id)>{{ $admin->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="action" class="form-select form-select-sm">
                <option value="">All Actions</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control form-control-sm" placeholder="From">
        </div>
        <div class="col-auto">
            <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control form-control-sm" placeholder="To">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
        </div>
    </form>

    @if ($logs->isEmpty())
        <p class="text-muted">No admin actions match these filters.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Target</th>
                        <th>Description</th>
                        <th>IP</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                        <tr>
                            <td>{{ $log->admin->name }}</td>
                            <td><code class="small">{{ $log->action }}</code></td>
                            <td class="small text-muted">{{ $log->target_type ? class_basename($log->target_type).' #'.$log->target_id : '—' }}</td>
                            <td class="small">{{ $log->description ?? '—' }}</td>
                            <td class="small text-muted">{{ $log->ip_address ?? '—' }}</td>
                            <td class="small text-muted">{{ $log->created_at->format('M j, Y g:i A') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $logs->links() }}
    @endif
</x-admin-layout>
