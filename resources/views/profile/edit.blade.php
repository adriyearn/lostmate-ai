<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">My Profile</h1>
    </x-slot>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="row mb-3">
                            <div class="col-sm-4">
                                @if ($profile->avatar_path)
                                    <img src="{{ asset('storage/'.$profile->avatar_path) }}" alt="Avatar"
                                         class="rounded-circle mb-2" style="width: 96px; height: 96px; object-fit: cover;">
                                @else
                                    <div class="rounded-circle bg-body-secondary d-flex align-items-center justify-content-center mb-2 text-muted"
                                         style="width: 96px; height: 96px;">
                                        No avatar
                                    </div>
                                @endif
                            </div>
                            <div class="col-sm-8">
                                <x-input-label for="avatar" value="Avatar" />
                                <input id="avatar" type="file" name="avatar" class="form-control" accept="image/png,image/jpeg,image/webp">
                                <x-input-error :messages="$errors->get('avatar')" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <x-input-label for="department" value="Department" />
                                <x-text-input id="department" type="text" name="department" :value="old('department', $profile->department)" />
                                <x-input-error :messages="$errors->get('department')" />
                            </div>

                            <div class="col-sm-6 mb-3">
                                <x-input-label for="course_or_position" value="Course / Position" />
                                <x-text-input id="course_or_position" type="text" name="course_or_position" :value="old('course_or_position', $profile->course_or_position)" placeholder="e.g. BSIT, Faculty" />
                                <x-input-error :messages="$errors->get('course_or_position')" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <x-input-label for="year_level" value="Year Level" />
                                <x-text-input id="year_level" type="text" name="year_level" :value="old('year_level', $profile->year_level)" />
                                <x-input-error :messages="$errors->get('year_level')" />
                            </div>

                            <div class="col-sm-6 mb-3">
                                <x-input-label for="contact_number" value="Contact Number" />
                                <x-text-input id="contact_number" type="text" name="contact_number" :value="old('contact_number', $profile->contact_number)" />
                                <p class="form-text">Private &mdash; never shown to other users.</p>
                                <x-input-error :messages="$errors->get('contact_number')" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <x-input-label for="bio" value="Bio" />
                            <textarea id="bio" name="bio" rows="3" class="form-control">{{ old('bio', $profile->bio) }}</textarea>
                            <x-input-error :messages="$errors->get('bio')" />
                        </div>

                        <x-primary-button>Save Profile</x-primary-button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h2 class="h6">Account</h2>
                    <p class="small text-muted mb-1">{{ auth()->user()->name }}</p>
                    <p class="small text-muted mb-0">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
