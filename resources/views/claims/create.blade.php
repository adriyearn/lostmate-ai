<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Claim: {{ $foundItem->item_name }}</h1>
    </x-slot>

    <div class="alert alert-info">
        Describe details that would prove this item is yours &mdash; contents, marks, or anything not
        visible in the photos. The finder will compare this with what they privately noted when they
        found it.
    </div>

    @if ($foundItem->isAtOffice())
        <div class="alert alert-secondary small">
            <i class="bi bi-building"></i>
            This item is at the <strong>{{ config('lostmate.office.name') }}</strong>. Office staff will review your claim,
            and you'll collect it there with your pickup code.
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('claims.store', $foundItem) }}" enctype="multipart/form-data">
                @csrf

                <input type="hidden" name="ai_match_id" value="{{ old('ai_match_id', $aiMatchId) }}">

                <div class="mb-3">
                    <x-input-label for="identifying_details" value="Identifying Details" />
                    <textarea id="identifying_details" name="identifying_details" rows="4" class="form-control" required>{{ old('identifying_details') }}</textarea>
                    <x-input-error :messages="$errors->get('identifying_details')" />
                </div>

                <div class="mb-3">
                    <x-input-label for="proof_image" value="Proof Photo (optional)" />
                    <input id="proof_image" type="file" name="proof_image" class="form-control" accept="image/png,image/jpeg,image/webp" data-lm-images>
                    <p class="form-text">e.g. an old photo showing you with the item.</p>
                    <x-input-error :messages="$errors->get('proof_image')" />
                </div>

                @if ($lostItems->isNotEmpty())
                    <div class="mb-3">
                        <x-input-label for="lost_item_id" value="Link to one of your lost reports (optional)" />
                        <select id="lost_item_id" name="lost_item_id" class="form-select">
                            <option value="">None</option>
                            @foreach ($lostItems as $lostItem)
                                <option value="{{ $lostItem->id }}" @selected(old('lost_item_id', $lostItemId) == $lostItem->id)>
                                    {{ $lostItem->item_name }} &mdash; lost {{ $lostItem->date_lost->format('M j, Y') }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('lost_item_id')" />
                    </div>
                @endif

                <x-primary-button>Submit Claim</x-primary-button>
            </form>
        </div>
    </div>
</x-app-layout>
