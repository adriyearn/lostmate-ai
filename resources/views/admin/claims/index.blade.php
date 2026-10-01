<x-admin-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Claims</h1>
    </x-slot>

    <form method="GET" action="{{ route('admin.claims.index') }}" class="mb-3">
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

    @if ($claims->isEmpty())
        <p class="text-muted">No claims match these filters.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Found Item</th>
                        <th>Claimant</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($claims as $claim)
                        <tr>
                            <td>{{ $claim->foundItem->item_name }}</td>
                            <td>{{ $claim->claimant->name }}</td>
                            <td><span class="badge {{ $claim->status->badgeClass() }}">{{ $claim->status->label() }}</span></td>
                            <td>{{ $claim->created_at->format('M j, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.claims.show', $claim) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $claims->links() }}
    @endif
</x-admin-layout>
