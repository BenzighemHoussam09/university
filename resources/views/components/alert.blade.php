@props([
    'type'        => 'info',
    'dismissible' => false,
    'title'       => null,
])

@php
    $config = [
        'success' => ['bg' => 'bg-primary-fixed/30', 'border' => 'border-primary-fixed', 'text' => 'text-on-primary-fixed', 'icon' => 'check_circle', 'iconColor' => 'text-primary'],
        'error'   => ['bg' => 'bg-error-container/40', 'border' => 'border-error-container', 'text' => 'text-on-error-container', 'icon' => 'cancel', 'iconColor' => 'text-error'],
        'warning' => ['bg' => 'bg-tertiary-fixed/30', 'border' => 'border-tertiary-fixed', 'text' => 'text-on-surface', 'icon' => 'warning', 'iconColor' => 'text-tertiary'],
        'info'    => ['bg' => 'bg-secondary-container/30', 'border' => 'border-secondary-container', 'text' => 'text-on-surface', 'icon' => 'info', 'iconColor' => 'text-secondary'],
    ];
    $c = $config[$type] ?? $config['info'];
@endphp

<div {{ $attributes->class([$c['bg'], 'border', $c['border'], 'rounded-lg px-4 py-3 flex items-start gap-3']) }}
     @if($dismissible) x-data="{ show: true }" x-show="show" @endif>
    <span class="material-symbols-outlined text-[20px] mt-0.5 flex-shrink-0 {{ $c['iconColor'] }} icon-filled"
         >{{ $c['icon'] }}</span>
    <div class="flex-1 min-w-0">
        @if($title)
            <p class="text-sm font-semibold {{ $c['text'] }}">{{ $title }}</p>
        @endif
        <div class="text-sm {{ $c['text'] }} {{ $title ? 'mt-0.5' : '' }}">{{ $slot }}</div>
    </div>
    @if($dismissible)
        <button @click="show = false" class="flex-shrink-0 p-0.5 rounded hover:bg-black/10 transition-colors">
            <span class="material-symbols-outlined text-[16px] {{ $c['text'] }}">close</span>
        </button>
    @endif
</div>
