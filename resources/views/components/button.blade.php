@props([
    'variant' => 'primary',
    'size'    => 'md',
    'type'    => 'button',
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-lg transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-surface-tint active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed select-none';

    $variants = [
        'primary'   => 'btn-gradient text-on-primary shadow-md hover:shadow-lg',
        'secondary' => 'bg-transparent border border-outline-variant/40 text-primary hover:bg-primary/5',
        'danger'    => 'bg-error text-on-error hover:opacity-90',
        'ghost'     => 'bg-transparent text-on-surface-variant hover:bg-surface-container',
        'outline'   => 'border border-outline text-on-surface hover:bg-surface-container',
    ];

    $sizes = [
        'sm' => 'py-1.5 px-3.5 text-sm',
        'md' => 'py-2.5 px-5 text-sm',
        'lg' => 'py-4 px-7 text-base',
    ];

    $cls = $base . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['md']);
@endphp

<button type="{{ $type }}" {{ $attributes->class([$cls]) }}>
    {{ $slot }}
</button>
