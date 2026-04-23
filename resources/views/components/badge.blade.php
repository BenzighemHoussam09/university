@props([
    'variant' => 'default',
    'dot'     => false,
])

@php
    $variants = [
        'default'   => 'bg-surface-container text-on-surface-variant',
        'primary'   => 'bg-primary-fixed/40 text-on-primary-fixed-variant',
        'success'   => 'bg-primary-fixed/40 text-on-primary-fixed-variant',
        'error'     => 'bg-error-container text-on-error-container',
        'warning'   => 'bg-tertiary-fixed/40 text-on-tertiary-fixed-variant',
        'info'      => 'bg-secondary-container/40 text-on-secondary-fixed-variant',
        'draft'     => 'bg-surface-container-high text-on-surface-variant',
        'active'    => 'bg-primary-fixed/40 text-on-primary-fixed-variant',
        'ended'     => 'bg-surface-container-high text-on-surface-variant',
        'scheduled' => 'bg-secondary-container/40 text-on-secondary-fixed-variant',
        'waiting'   => 'bg-tertiary-fixed/40 text-on-tertiary-fixed-variant',
        'completed' => 'bg-primary-fixed/40 text-on-primary-fixed-variant',
    ];
    $cls = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold ' . ($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->class([$cls]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full bg-current opacity-70"></span>
    @endif
    {{ $slot }}
</span>
