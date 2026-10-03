@props(['item'])

{{-- Tells the reporter what AI matching is doing for this report right now. --}}
@php $status = App\Services\MatchingStatus::get($item); @endphp

@if ($status === App\Services\MatchingStatus::CHECKING)
    <div {{ $attributes->merge(['class' => 'alert alert-secondary small d-flex align-items-center gap-2']) }} role="status">
        <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <span><strong>AI is checking for matches&hellip;</strong> This usually takes under a minute. Refresh the page to see results.</span>
    </div>
@elseif ($status === App\Services\MatchingStatus::FAILED)
    <div {{ $attributes->merge(['class' => 'alert alert-warning small d-flex align-items-center gap-2']) }} role="status">
        <i class="bi bi-exclamation-triangle"></i>
        <span><strong>Matching couldn't finish right now.</strong> Your report is saved. The AI service may be busy; an admin can retry it.</span>
    </div>
@endif
