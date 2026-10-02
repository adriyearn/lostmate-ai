@php
    $foundItem ??= null;
@endphp

<div class="row">
    <div class="col-sm-6 mb-3">
        <x-input-label for="category_id" value="Category" />
        <select id="category_id" name="category_id" class="form-select" required>
            <option value="">Select a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $foundItem?->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" />
    </div>

    <div class="col-sm-6 mb-3">
        <x-input-label for="item_name" value="Item Name" />
        <x-text-input id="item_name" type="text" name="item_name" :value="old('item_name', $foundItem?->item_name)" required />
        <x-input-error :messages="$errors->get('item_name')" />
    </div>
</div>

<div class="row">
    <div class="col-sm-6 mb-3">
        <x-input-label for="color" value="Color" />
        <x-text-input id="color" type="text" name="color" :value="old('color', $foundItem?->color)" />
        <x-input-error :messages="$errors->get('color')" />
    </div>

    <div class="col-sm-6 mb-3">
        <x-input-label for="brand" value="Brand" />
        <x-text-input id="brand" type="text" name="brand" :value="old('brand', $foundItem?->brand)" />
        <x-input-error :messages="$errors->get('brand')" />
    </div>
</div>

<div class="mb-3">
    <x-input-label for="description" value="Public Description" />
    <textarea id="description" name="description" rows="3" class="form-control" required>{{ old('description', $foundItem?->description) }}</textarea>
    <p class="form-text">Visible to everyone browsing found items.</p>
    <x-input-error :messages="$errors->get('description')" />
</div>

<div class="mb-3">
    <x-input-label for="hidden_details" value="Hidden Details (private)" />
    <textarea id="hidden_details" name="hidden_details" rows="3" class="form-control">{{ old('hidden_details', $foundItem?->hidden_details) }}</textarea>
    <p class="form-text">
        Only you and administrators can see this. Use it for details a claimant would have to
        prove they know &mdash; contents, a scratch, a name inside &mdash; so you can verify a claim.
    </p>
    <x-input-error :messages="$errors->get('hidden_details')" />
</div>

<div class="row">
    <div class="col-sm-4 mb-3">
        <x-input-label for="location_found" value="Location Found" />
        <x-text-input id="location_found" type="text" name="location_found" :value="old('location_found', $foundItem?->location_found)" required />
        <x-input-error :messages="$errors->get('location_found')" />
    </div>

    <div class="col-sm-3 mb-3">
        <x-input-label for="date_found" value="Date Found" />
        <x-text-input id="date_found" type="date" name="date_found" :value="old('date_found', $foundItem?->date_found?->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('date_found')" />
    </div>

    <div class="col-sm-2 mb-3">
        <x-input-label for="time_found" value="Time (optional)" />
        <x-text-input id="time_found" type="time" name="time_found" :value="old('time_found', $foundItem?->time_found)" />
        <x-input-error :messages="$errors->get('time_found')" />
    </div>

    <div class="col-sm-3 mb-3">
        <x-input-label for="current_location" value="Item Currently At" />
        <x-text-input id="current_location" type="text" name="current_location" :value="old('current_location', $foundItem?->current_location)" placeholder="e.g. With finder" />
        <x-input-error :messages="$errors->get('current_location')" />
    </div>
</div>

@if ($foundItem && $foundItem->images->isNotEmpty())
    <div class="mb-3">
        <x-input-label value="Current Photos" />
        <div class="d-flex flex-wrap gap-3 mt-1">
            @foreach ($foundItem->images as $image)
                <div class="text-center">
                    <img src="{{ asset('storage/'.$image->path) }}" alt="Photo" class="rounded mb-1" style="width: 100px; height: 100px; object-fit: cover;">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remove_images[]" value="{{ $image->id }}" id="remove_image_{{ $image->id }}">
                        <label class="form-check-label small" for="remove_image_{{ $image->id }}">Remove</label>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="mb-3">
    <x-input-label for="images" value="Add Photos (up to 3 total, jpg/png/webp; large phone photos are shrunk automatically)" />
    <input id="images" type="file" name="images[]" class="form-control" accept="image/png,image/jpeg,image/webp" multiple data-lm-images data-max-files="3">
    <x-input-error :messages="$errors->get('images')" />
    <x-input-error :messages="$errors->get('images.0')" />
</div>
