<x-admin-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Flagged {{ class_basename($report->reportable_type) }}</h1>
    </x-slot>

    <div class="row">
        <div class="col-md-7">
            <div class="card mb-3">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-4">Reported by</dt>
                        <dd class="col-8">{{ $report->reporter->name }} ({{ $report->reporter->email }})</dd>
                        <dt class="col-4">Reason</dt>
                        <dd class="col-8">{{ $report->reason->label() }}</dd>
                        <dt class="col-4">Details</dt>
                        <dd class="col-8">{{ $report->details ?: '—' }}</dd>
                        <dt class="col-4">Reported</dt>
                        <dd class="col-8">{{ $report->created_at->format('M j, Y g:i A') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h2 class="h6">Flagged Content</h2>

                    @if (! $report->reportable)
                        <p class="text-muted mb-0">The original content has since been deleted.</p>
                    @elseif ($report->reportable instanceof \App\Models\Message)
                        <p class="small text-muted mb-1">
                            Message from {{ $report->reportable->sender->name }} in a conversation
                            (<a href="{{ route('conversations.show', $report->reportable->conversation_id) }}">view thread</a>)
                        </p>
                        <p class="mb-0">{{ $report->reportable->body }}</p>
                    @elseif ($report->reportable instanceof \App\Models\LostItem)
                        <p class="mb-1"><a href="{{ route('admin.reports.show-lost', $report->reportable) }}">{{ $report->reportable->item_name }}</a></p>
                        <p class="mb-0 small text-muted">{{ $report->reportable->description }}</p>
                    @elseif ($report->reportable instanceof \App\Models\FoundItem)
                        <p class="mb-1"><a href="{{ route('admin.reports.show-found', $report->reportable) }}">{{ $report->reportable->item_name }}</a></p>
                        <p class="mb-0 small text-muted">{{ $report->reportable->description }}</p>
                    @elseif ($report->reportable instanceof \App\Models\User)
                        <p class="mb-0"><a href="{{ route('admin.users.show', $report->reportable) }}">{{ $report->reportable->name }}</a> ({{ $report->reportable->email }})</p>
                    @endif
                </div>
            </div>

            @if ($report->admin_notes)
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="h6">Previous Admin Notes</h2>
                        <p class="mb-0">{{ $report->admin_notes }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6">Update Status</h2>
                    <form method="POST" action="{{ route('admin.flags.update-status', $report) }}">
                        @csrf
                        <div class="mb-2">
                            <select name="status" class="form-select form-select-sm">
                                @foreach (\App\Enums\ReportStatus::cases() as $s)
                                    <option value="{{ $s->value }}" @selected($report->status === $s)>{{ $s->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-2">
                            <textarea name="admin_notes" class="form-control form-control-sm" rows="2" placeholder="Notes (sent to the reporter)">{{ $report->admin_notes }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Update &amp; Notify Reporter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
