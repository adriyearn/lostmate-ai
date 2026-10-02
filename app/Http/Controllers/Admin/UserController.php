<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AdminLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(protected AdminLogger $adminLogger) {}

    public function index(Request $request): View
    {
        $users = User::query()->with('profile')
            ->when($request->query('q'), function ($query, $q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('student_id', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'q' => $request->query('q', ''),
        ]);
    }

    public function show(User $user): View
    {
        $user->load('profile');

        return view('admin.users.show', ['user' => $user]);
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, "You can't deactivate your own account.");

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        $this->adminLogger->log(
            $request->user(),
            $user->is_active ? 'user.activated' : 'user.deactivated',
            $user,
        );

        return back()->with('success', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        abort_if($user->id === $request->user()->id, 403, "You can't change your own role.");

        $validated = $request->validate([
            'role' => ['required', new Enum(UserRole::class)],
        ]);

        $user->forceFill(['role' => $validated['role']])->save();

        $this->adminLogger->log(
            $request->user(),
            'user.role_changed',
            $user,
            "Role changed to {$validated['role']}",
        );

        return back()->with('success', 'User role updated.');
    }
}
