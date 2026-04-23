@props([
    'icon'        => 'inbox',
    'title'       => 'لا توجد بيانات',
    'description' => null,
    'action'      => null,
    'actionLabel' => null,
    'actionRoute' => null,
])

<div class="flex flex-col items-center justify-center py-16 px-6 text-center">
    <div class="w-16 h-16 rounded-2xl bg-surface-container flex items-center justify-center mb-5">
        <span class="material-symbols-outlined text-[32px] text-on-surface-variant">{{ $icon }}</span>
    </div>
    <h3 class="text-base font-bold text-on-surface font-headline mb-2">{{ $title }}</h3>
    @if($description)
        <p class="text-sm text-on-surface-variant max-w-xs leading-relaxed">{{ $description }}</p>
    @endif
    @if($slot->isNotEmpty())
        <div class="mt-5">{{ $slot }}</div>
    @elseif($actionRoute && $actionLabel)
        <div class="mt-5">
            <a href="{{ $actionRoute }}"
               class="inline-flex items-center gap-2 py-2.5 px-5 btn-gradient text-on-primary rounded-lg font-semibold text-sm shadow-md hover:shadow-lg transition-all">
                {{ $actionLabel }}
            </a>
        </div>
    @endif
</div>
