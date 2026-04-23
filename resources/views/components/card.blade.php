@props([
    'padding' => 'p-6',
    'shadow'  => true,
])

@php
    $cls = 'bg-surface-container-lowest rounded-xl '
        . $padding
        . ($shadow ? ' shadow-[0_4px_16px_rgba(25,28,29,0.05)]' : '');
@endphp

<div {{ $attributes->class([$cls]) }}>
    {{ $slot }}
</div>
