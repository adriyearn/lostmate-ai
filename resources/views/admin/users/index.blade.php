<x-admin-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Users</h1>
    </x-slot>

    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-3">
        <div class="input-group" style="max-width: 24rem;">
            <input type="text" name="q" value="{{ $q }}" class="form-control" placeholder="Search name, email, or student ID">
            <button type="submit" class="btn btn-outline-secondary">Search</button>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Student ID</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td><a href="{{ route('admin.users.show', $user) }}">{{ $user->name }}</a></td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->student_id ?? '—' }}</td>
                        <td>
                            <span class="badge {{ $user->isAdmin() ? 'text-bg-danger' : 'text-bg-secondary' }}">
                                {{ $user->role->label() }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $user->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $user->is_active ? 'Active' : 'Deactivated' }}
                            </span>
                        </td>
                        <td class="text-end">
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-secondary">Manage</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{ $users->links() }}
</x-admin-layout>
