@props([
    'role'  => 'teacher',
    'items' => [],
])

<aside {{ $attributes->class([
    'fixed right-0 top-0 h-full w-64 bg-surface-container-low z-40 flex flex-col pt-14',
    'transform transition-transform duration-300 ease-in-out',
    'md:translate-x-0',
]) }}
       :class="$el.closest('[x-data]').__x?.$data?.sidebarOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'">

    <div class="px-5 py-4 border-b border-outline-variant/20">
        {{ $header ?? '' }}
    </div>

    <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">
        @foreach($items as $item)
            @php $active = request()->routeIs(($item['route'] ?? '') . '*'); @endphp
            <a href="{{ isset($item['route']) ? route($item['route']) : ($item['url'] ?? '#') }}"
               class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ $active
                          ? 'bg-primary text-on-primary'
                          : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                @if(isset($item['icon']))
                    <span class="material-symbols-outlined text-[20px] {{ $active ? 'icon-filled' : '' }}">{{ $item['icon'] }}</span>
                @endif
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    <div class="px-3 py-3 border-t border-outline-variant/20">
        {{ $footer ?? '' }}
    </div>
</aside>
