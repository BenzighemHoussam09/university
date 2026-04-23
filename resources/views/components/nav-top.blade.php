@props([
    'homeRoute' => 'home',
    'title'     => null,
])

<header {{ $attributes->class(['fixed top-0 w-full z-50 glass-nav shadow-sm px-5 py-3 flex justify-between items-center']) }}>

    <div class="flex items-center gap-3">
        {{ $start ?? '' }}
        <a href="{{ route($homeRoute) }}" class="text-lg font-bold text-primary font-headline tracking-tight">
            {{ $title ?? config('app.name', 'منصة الامتحانات') }}
        </a>
    </div>

    <div class="flex items-center gap-2">
        {{ $end ?? '' }}
    </div>
</header>
