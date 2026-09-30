<x-admin-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Flagged Content</h1>
    </x-slot>

    <form method="GET" action="{{ route('admin.flags.index') }}" class="mb-3">
        <div class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" style="max-width: 12rem;">
                <option value="">All Statuses</option>
                @foreach ($statuses as $s)
                    <option value="{{ $s->value }}" @selected($status === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Reason</th>
                    <th>Reporter</th>
                    <th>Status</th>
                    <th>Reported</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reports as $report)
                    <tr>
                        <td>{{ class_basename($report->reportable_type) }}</td>
                        <td>{{ $report->reason->label() }}</td>
                        <td>{{ $report->reporter->name }}</td>
                        <td><span class="badge {{ $report->status->badgeClass() }}">{{ $report->status->label() }}</span></td>
                        <td>{{ $report->created_at->format('M j, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('admin.flags.show', $report) }}" class="btn btn-sm btn-outline-secondary">Review</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $reports->links() }}
</x-admin-layout>
