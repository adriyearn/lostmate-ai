@props(['status'])

<span {{ $attributes->merge(['class' => 'badge lm-status ' . $status->badgeClass()]) }}>
    {{ $status->label() }}
</span>
