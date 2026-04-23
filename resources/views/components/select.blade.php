@props([
    'label'   => null,
    'error'   => null,
    'options' => [],
    'placeholder' => null,
])

@php
    $id = $attributes->get('id', $attributes->get('name', ''));
    $hasError = $error || ($errors->has($attributes->get('name', '')));
    $errorMsg = $error ?: ($errors->first($attributes->get('name', '')) ?: null);
    $cls = 'w-full py-3 px-4 bg-surface-container-highest border-none rounded-lg text-on-surface appearance-none focus:ring-2 focus:ring-surface-tint transition-all '
        . ($hasError ? 'ring-2 ring-error' : '');
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-on-surface-variant">{{ $label }}</label>
    @endif
    <div class="relative">
        <select {{ $attributes->merge(['class' => $cls]) }}>
            @if($placeholder)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach($options as $value => $display)
                <option value="{{ $value }}">{{ $display }}</option>
            @endforeach
            {{ $slot }}
        </select>
        <span class="material-symbols-outlined absolute left-3 inset-y-0 flex items-center pointer-events-none text-outline text-[18px]">expand_more</span>
    </div>
    @if($hasError && $errorMsg)
        <p class="text-xs text-error flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px]">error</span>
            {{ $errorMsg }}
        </p>
    @endif
</div>
