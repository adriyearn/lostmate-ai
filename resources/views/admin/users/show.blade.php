<x-admin-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-3"><x-avatar :user="$user" size="lg" /><h1 class="h4 mb-0">{{ $user->name }}</h1></div>
    </x-slot>

    <div class="row">
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-4">Email</dt>
                        <dd class="col-8">{{ $user->email }}</dd>

                        <dt class="col-4">Student ID</dt>
                        <dd class="col-8">{{ $user->student_id ?? '—' }}</dd>

                        <dt class="col-4">Role</dt>
                        <dd class="col-8">{{ $user->role->label() }}</dd>

                        <dt class="col-4">Status</dt>
                        <dd class="col-8">{{ $user->is_active ? 'Active' : 'Deactivated' }}</dd>

                        <dt class="col-4">Department</dt>
                        <dd class="col-8">{{ $user->profile->department ?? '—' }}</dd>

                        <dt class="col-4">Course / Position</dt>
                        <dd class="col-8">{{ $user->profile->course_or_position ?? '—' }}</dd>

                        <dt class="col-4">Joined</dt>
                        <dd class="col-8">{{ $user->created_at->format('M j, Y') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            @if ($user->id !== auth()->id())
                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="h6">Account Status</h2>
                        <form method="POST" action="{{ route('admin.users.toggle-active', $user) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $user->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }}
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h2 class="h6">Role</h2>
                        <form method="POST" action="{{ route('admin.users.update-role', $user) }}" class="d-flex gap-2">
                            @csrf
                            <select name="role" class="form-select form-select-sm">
                                <option value="student_staff" @selected($user->role->value === 'student_staff')>Student / Staff</option>
                                <option value="admin" @selected($user->role->value === 'admin')>Admin</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary text-nowrap">Update Role</button>
                        </form>
                    </div>
                </div>
            @else
                <div class="alert alert-secondary">This is your own account - deactivation and role changes are disabled here.</div>
            @endif
        </div>
    </div>
</x-admin-layout>
