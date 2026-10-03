<x-admin-layout>
    @php $tagNumber = 'F-'.str_pad($foundItem->id, 4, '0', STR_PAD_LEFT); @endphp

    <x-slot name="header">
        <div class="lm-no-print">
            <h1 class="h3 mb-1">Claim tag</h1>
            <p class="text-muted mb-0">Print this and attach it to the item at the office. Scanning the QR code opens the item's page.</p>
        </div>
    </x-slot>

    <div class="d-flex gap-2 mb-4 lm-no-print">
        <button type="button" class="btn btn-dark" onclick="window.print()"><i class="bi bi-printer"></i> Print tag</button>
        <a href="{{ route('admin.reports.show-found', $foundItem) }}" class="btn btn-outline-secondary">Back to item</a>
    </div>

    {{-- Only public details: never hidden details or the finder's info. --}}
    <div class="lm-print-tag">
        <div class="lm-print-tag-hole"></div>
        <div class="lm-mono small text-muted">LOSTMATE AI &middot; {{ config('lostmate.office.name') }}</div>
        <div class="lm-print-tag-number lm-mono">{{ $tagNumber }}</div>
        <div id="qr" class="my-3" aria-label="QR code linking to this item"></div>
        <div class="fw-bold text-dark">{{ $foundItem->item_name }}</div>
        <div class="small text-muted">{{ $foundItem->category->name }} &middot; found {{ $foundItem->date_found->format('M j, Y') }}</div>
        <div class="small mt-2">Is this yours? Scan to claim it on LostMate AI.</div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        new QRCode(document.getElementById('qr'), {
            text: @json(route('found-items.show', $foundItem)),
            width: 170,
            height: 170,
            correctLevel: QRCode.CorrectLevel.M,
        });
    </script>
    @endpush
</x-admin-layout>
