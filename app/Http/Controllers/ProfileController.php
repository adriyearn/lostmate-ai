<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Services\AccountDeletionService;
use App\Services\PhotoStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', ['profile' => request()->user()->profile]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $profile = $request->user()->profile;

        $profile->fill($request->safe()->except('avatar'));

        if ($request->hasFile('avatar')) {
            $photos = app(PhotoStorage::class);
            $photos->delete($profile->avatar_path);
            $profile->avatar_path = $photos->store($request->file('avatar'), 'avatars');
        }

        $profile->save();

        return redirect()
            ->route('profile.edit')
            ->with('success', 'Your profile has been updated.');
    }

    /**
     * Permanently delete the signed-in user's account and data.
     */
    public function destroy(DeleteAccountRequest $request, AccountDeletionService $deletion): RedirectResponse
    {
        $user = $request->user();

        if ($reason = $deletion->blockedReason($user)) {
            return back()->with('error', $reason);
        }

        Auth::logout();
        $deletion->delete($user);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Your account and data have been deleted.');
    }
}
