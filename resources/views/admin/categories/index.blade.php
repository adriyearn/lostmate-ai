<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h1 class="h4 mb-0">Categories</h1>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">New Category</a>
        </div>
    </x-slot>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Items</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td class="small text-muted">{{ $category->description ?? '—' }}</td>
                        <td>{{ $category->lost_items_count + $category->found_items_count }}</td>
                        <td>
                            <span class="badge {{ $category->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="d-flex gap-2 justify-content-end">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.toggle-active', $category) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                                        {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                      onsubmit="return confirm('Delete this category?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</x-admin-layout>
