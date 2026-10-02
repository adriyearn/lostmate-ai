@props(['user', 'size' => 'md'])

@php
    // Uploaded profile photo if there is one, otherwise the initials circle.
    $path = $user->profile?->avatar_path;
    $sizeClass = [
        'sm' => 'lm-avatar-sm',
        'md' => '',
        'lg' => 'lm-avatar-lg',
        'xl' => 'lm-avatar-xl',
    ][$size] ?? '';
@endphp

@if ($path)
    <img src="{{ asset('storage/'.$path) }}" alt="{{ $user->name }}"
         {{ $attributes->merge(['class' => trim("lm-avatar lm-avatar-img {$sizeClass}")]) }}>
@else
    <span {{ $attributes->merge(['class' => trim("lm-avatar {$sizeClass}")]) }} title="{{ $user->name }}">{{ $user->initials() }}</span>
@endif
