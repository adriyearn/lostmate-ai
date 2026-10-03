<?php

namespace App\Http\Controllers;

use App\Services\PhotoStorage;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
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
}
