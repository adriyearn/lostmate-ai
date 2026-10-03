<x-admin-layout>
    <x-slot name="header">
        <div>
            <h1 class="h3 mb-1">Office &amp; unclaimed items</h1>
            <p class="text-muted mb-0">
                Items held at the {{ config('lostmate.office.name') }}, and items nobody claimed within
                {{ config('lostmate.unclaimed_after_days') }} days.
            </p>
        </div>
    </x-slot>

    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'office' ? 'active' : '' }}" href="{{ route('admin.office.index') }}">
                <i class="bi bi-building"></i> At the office <span class="badge text-bg-light ms-1">{{ $officeCount }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'unclaimed' ? 'active' : '' }}" href="{{ route('admin.office.index', ['tab' => 'unclaimed']) }}">
                <i class="bi bi-hourglass-bottom"></i> Unclaimed {{ config('lostmate.unclaimed_after_days') }}+ days <span class="badge text-bg-light ms-1">{{ $unclaimedCount }}</span>
            </a>
        </li>
    </ul>

    @if ($tab === 'unclaimed')
        <div class="alert alert-secondary small">
            <i class="bi bi-info-circle"></i>
            These found items are still <strong>open</strong> with no claim after {{ config('lostmate.unclaimed_after_days') }} days.
            Following school policy, staff may donate or dispose of them. Closing requires a note, which is saved in the admin log.
        </div>
    @endif

    @if ($items->isEmpty())
        <div class="card">
            <div class="lm-empty">
                <i class="bi {{ $tab === 'office' ? 'bi-building' : 'bi-hourglass' }}"></i>
                <div class="fw-semibold text-dark mb-1">
                    {{ $tab === 'office' ? 'No items are being held at the office.' : 'No items are past the unclaimed limit.' }}
                </div>
                <div class="small">
                    {{ $tab === 'office'
                        ? 'Use "Mark as received at office" on a found item when a finder turns it in.'
                        : 'Found items appear here once they stay unclaimed for '.config('lostmate.unclaimed_after_days').' days.' }}
                </div>
            </div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Found</th>
                        <th>Finder</th>
                        <th>{{ $tab === 'office' ? 'Received' : 'Custody' }}</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>
                                <a href="{{ route('admin.reports.show-found', $item) }}" class="fw-semibold">{{ $item->item_name }}</a>
                                <div class="small text-muted">{{ $item->category->name }}</div>
                            </td>
                            <td>
                                {{ $item->date_found->format('M j, Y') }}
                                <div class="small text-muted">{{ $item->date_found->diffForHumans() }}</div>
                            </td>
                            <td>{{ $item->user->name }}</td>
                            <td>
                                @if ($item->isAtOffice())
                                    <span class="small">{{ $item->surrendered_at->format('M j, Y') }}</span>
                                    <div class="small text-muted">by {{ $item->surrenderedTo?->name ?? 'former staff' }}</div>
                                @else
                                    <span class="small text-muted">With the finder</span>
                                @endif
                            </td>
                            <td><x-status-badge :status="$item->status" /></td>
                            <td class="text-end">
                                @if ($tab === 'unclaimed')
                                    <form method="POST" action="{{ route('admin.office.close-unclaimed', $item) }}" class="d-flex gap-2 justify-content-end"
                                          onsubmit="return confirm('Close this item as unclaimed?');">
                                        @csrf
                                        <input type="text" name="note" class="form-control form-control-sm" style="max-width: 15rem;"
                                               placeholder="e.g. Donated to the clinic" required maxlength="500">
                                        <button type="submit" class="btn btn-sm btn-outline-danger text-nowrap">Close</button>
                                    </form>
                                @else
                                    <a href="{{ route('admin.office.tag', $item) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-qr-code"></i> Tag
                                    </a>
                                    <a href="{{ route('found-items.claims', $item) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-patch-check"></i> Claims
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $items->links() }}</div>
    @endif
</x-admin-layout>
