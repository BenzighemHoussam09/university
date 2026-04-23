@props([
    'label'  => null,
    'error'  => null,
    'icon'   => null,
    'type'   => 'text',
    'hint'   => null,
])

@php
    $id = $attributes->get('id', $attributes->get('name', ''));
    $hasError = $error || ($errors->has($attributes->get('name', '')));
    $errorMsg = $error ?: ($errors->first($attributes->get('name', '')) ?: null);
    $inputCls = 'w-full py-3 bg-surface-container-highest border-none rounded-lg text-on-surface placeholder:text-outline transition-all focus:ring-2 focus:ring-surface-tint '
        . ($icon ? 'pr-11' : 'pr-4')
        . ' pl-4 '
        . ($hasError ? 'ring-2 ring-error' : '');
@endphp

<div class="space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block text-sm font-semibold text-on-surface-variant">{{ $label }}</label>
    @endif
    <div class="relative">
        @if($icon)
            <span class="material-symbols-outlined absolute inset-y-0 right-3 flex items-center pointer-events-none text-outline text-[20px]"
                  style="font-size:20px;line-height:1;">{{ $icon }}</span>
        @endif
        <input type="{{ $type }}" {{ $attributes->merge(['class' => $inputCls]) }}>
    </div>
    @if($hint && !$hasError)
        <p class="text-xs text-on-surface-variant">{{ $hint }}</p>
    @endif
    @if($hasError && $errorMsg)
        <p class="text-xs text-error flex items-center gap-1">
            <span class="material-symbols-outlined text-[14px]">error</span>
            {{ $errorMsg }}
        </p>
    @endif
</div>
