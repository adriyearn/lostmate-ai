<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;

class PasswordController extends Controller
{
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        // The "hashed" cast on User hashes the new password automatically.
        $request->user()->update(['password' => $request->validated('password')]);

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Your password has been changed.');
    }
}
