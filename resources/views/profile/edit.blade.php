<x-app-layout>
    <x-slot name="header">
        <h1>My profile</h1>
        <p class="text-muted mb-0 mt-1">Your photo appears next to your name across LostMate.</p>
    </x-slot>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="d-flex flex-wrap align-items-center gap-4 mb-4 pb-4 border-bottom">
                            <div id="avatarPreview">
                                <x-avatar :user="auth()->user()" size="xl" />
                            </div>
                            <div class="flex-grow-1">
                                <x-input-label for="avatar" value="Profile photo" />
                                <input id="avatar" type="file" name="avatar" class="form-control" accept="image/png,image/jpeg,image/webp">
                                <p class="form-text mb-0">JPG, PNG or WEBP, up to 5 MB. A square photo of your face works best.</p>
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

                        <x-primary-button><i class="bi bi-check2"></i> Save profile</x-primary-button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card text-center">
                <div class="card-body">
                    <div class="d-flex justify-content-center mb-3">
                        <x-avatar :user="auth()->user()" size="lg" />
                    </div>
                    <div class="fw-bold text-dark">{{ auth()->user()->name }}</div>
                    <div class="small text-muted mb-2">{{ auth()->user()->email }}</div>
                    <span class="badge text-bg-primary">{{ auth()->user()->role->label() }}</span>
                    <p class="small text-muted mt-3 mb-0"><i class="bi bi-lock"></i> Your email and contact number are never shown to other users.</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Show the chosen photo in the avatar circle right away, before saving.
        document.getElementById('avatar').addEventListener('change', function (event) {
            var file = event.target.files[0];
            if (!file || !file.type.startsWith('image/')) {
                return;
            }

            var img = document.createElement('img');
            img.className = 'lm-avatar lm-avatar-img lm-avatar-xl';
            img.alt = 'New profile photo preview';
            img.src = URL.createObjectURL(file);

            var preview = document.getElementById('avatarPreview');
            preview.innerHTML = '';
            preview.appendChild(img);
        });
    </script>
    @endpush
</x-app-layout>
