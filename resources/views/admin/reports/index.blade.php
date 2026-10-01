<x-admin-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Lost &amp; Found Reports</h1>
    </x-slot>

    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'lost' ? 'active' : '' }}" href="{{ route('admin.reports.index', ['tab' => 'lost']) }}">Lost</a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $tab === 'found' ? 'active' : '' }}" href="{{ route('admin.reports.index', ['tab' => 'found']) }}">Found</a>
        </li>
    </ul>

    <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 mb-3">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="col-auto">
            <select name="category_id" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($filters['category_id'] == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button>
        </div>
    </form>

    @if ($items->isEmpty())
        <p class="text-muted">No {{ $tab }} reports match these filters.</p>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Reporter</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($items as $item)
                        <tr>
                            <td>
                                <a href="{{ route($tab === 'lost' ? 'admin.reports.show-lost' : 'admin.reports.show-found', $item) }}">
                                    {{ $item->item_name }}
                                </a>
                            </td>
                            <td>{{ $item->category->name }}</td>
                            <td>{{ $item->user->name }}</td>
                            <td><x-status-badge :status="$item->status" /></td>
                            <td>{{ ($tab === 'lost' ? $item->date_lost : $item->date_found)->format('M j, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route($tab === 'lost' ? 'admin.reports.show-lost' : 'admin.reports.show-found', $item) }}" class="btn btn-sm btn-outline-secondary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $items->links() }}
    @endif
</x-admin-layout>
