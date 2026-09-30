<x-admin-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Edit Category</h1>
    </x-slot>

    <div class="card" style="max-width: 32rem;">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" type="text" name="name" :value="old('name', $category->name)" required autofocus />
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="description" value="Description (optional)" />
                    <textarea id="description" name="description" class="form-control" rows="2">{{ old('description', $category->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" />
                </div>

                <x-primary-button>Save Changes</x-primary-button>
            </form>
        </div>
    </div>
</x-admin-layout>
