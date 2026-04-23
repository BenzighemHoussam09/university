@props([
    'label' => null,
    'error' => null,
    'rows'  => 4,
])

@php
    $id = $attributes->get('id', $attributes->get('name', ''));
    $hasError = $error || ($errors->has($attributes->get('name', '')));
    $errorMsg = $error ?: ($errors->first($attributes->get('name', '')) ?: null);
    $cls = 'w-full px-4 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface placeholder:text-outline focus:ring-2 focus:ring-surface-tint transition-all resize-y '
        . ($hasError ? 'ring-2 ring-error' : '');
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-on-surface-variant">{{ $label }}</label>
    @endif
    <textarea rows="{{ $rows }}" {{ $attributes->merge(['class' => $cls]) }}>{{ $slot }}</textarea>
    @if($hasError && $errorMsg)
        <p class="text-xs text-error flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px]">error</span>
            {{ $errorMsg }}
        </p>
    @endif
</div>
