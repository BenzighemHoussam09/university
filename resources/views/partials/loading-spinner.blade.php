@props([
    'size'  => 'md',
    'color' => 'primary',
    'label' => null,
])

@php
    $sizes = ['sm' => 'w-4 h-4', 'md' => 'w-6 h-6', 'lg' => 'w-10 h-10'];
    $colors = ['primary' => 'border-primary', 'white' => 'border-on-primary', 'muted' => 'border-outline'];
    $sCls = $sizes[$size] ?? $sizes['md'];
    $cCls = $colors[$color] ?? $colors['primary'];
@endphp

<div class="inline-flex items-center gap-2">
    <div class="rounded-full border-2 border-surface-container-high {{ $cCls }} border-t-transparent animate-spin {{ $sCls }}"></div>
    @if($label)
        <span class="text-sm text-on-surface-variant">{{ $label }}</span>
    @endif
</div>
