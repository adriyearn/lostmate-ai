@php
    $lostItem ??= null;
@endphp

{{-- On phones the field errors can be far below, so say it at the top too. --}}
@if ($errors->any())
    <div class="alert alert-danger small" role="alert">
        <i class="bi bi-exclamation-triangle"></i> Your report wasn't saved yet. Please fix the field(s) marked in red below.
    </div>
@endif

<div class="row">
    <div class="col-sm-6 mb-3">
        <x-input-label for="category_id" value="Category" />
        <select id="category_id" name="category_id" class="form-select" required>
            <option value="">Select a category</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $lostItem?->category_id) == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" />
    </div>

    <div class="col-sm-6 mb-3">
        <x-input-label for="item_name" value="Item Name" />
        <x-text-input id="item_name" type="text" name="item_name" :value="old('item_name', $lostItem?->item_name)" required />
        <x-input-error :messages="$errors->get('item_name')" />
    </div>
</div>

<div class="row">
    <div class="col-sm-6 mb-3">
        <x-input-label for="color" value="Color" />
        <x-text-input id="color" type="text" name="color" :value="old('color', $lostItem?->color)" />
        <x-input-error :messages="$errors->get('color')" />
    </div>

    <div class="col-sm-6 mb-3">
        <x-input-label for="brand" value="Brand" />
        <x-text-input id="brand" type="text" name="brand" :value="old('brand', $lostItem?->brand)" />
        <x-input-error :messages="$errors->get('brand')" />
    </div>
</div>

<div class="mb-3">
    <x-input-label for="description" value="Description" />
    <textarea id="description" name="description" rows="4" class="form-control" required>{{ old('description', $lostItem?->description) }}</textarea>
    <x-input-error :messages="$errors->get('description')" />
</div>

<div class="row">
    <div class="col-sm-6 mb-3">
        <x-input-label for="location_lost" value="Location Lost" />
        <x-text-input id="location_lost" type="text" name="location_lost" :value="old('location_lost', $lostItem?->location_lost)" placeholder="e.g. Library 2nd floor" required />
        <x-input-error :messages="$errors->get('location_lost')" />
    </div>

    <div class="col-sm-3 mb-3">
        <x-input-label for="date_lost" value="Date Lost" />
        <x-text-input id="date_lost" type="date" name="date_lost" :value="old('date_lost', $lostItem?->date_lost?->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('date_lost')" />
    </div>

    <div class="col-sm-3 mb-3">
        <x-input-label for="time_lost" value="Time Lost (optional)" />
        <x-text-input id="time_lost" type="time" name="time_lost" :value="old('time_lost', $lostItem?->time_lost ? substr($lostItem->time_lost, 0, 5) : null)" />
        <x-input-error :messages="$errors->get('time_lost')" />
    </div>
</div>

@if ($lostItem && $lostItem->images->isNotEmpty())
    <div class="mb-3">
        <x-input-label value="Current Photos" />
        <div class="d-flex flex-wrap gap-3 mt-1">
            @foreach ($lostItem->images as $image)
                <div class="text-center">
                    <img src="{{ $image->url }}" alt="Photo" class="rounded mb-1" style="width: 100px; height: 100px; object-fit: cover;">
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
