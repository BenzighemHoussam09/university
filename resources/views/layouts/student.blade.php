<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' — ' : '' }}{{ config('app.name', 'بوابة الامتحانات') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-surface text-on-surface" x-data="{ sidebarOpen: false }">

    {{-- Top App Bar --}}
    <header class="fixed top-0 w-full z-50 glass-nav shadow-sm px-5 py-3 flex justify-between items-center">

        <div class="flex items-center gap-3">
            <button @click="sidebarOpen = !sidebarOpen"
                    class="md:hidden p-2 rounded-full hover:bg-surface-container transition-colors text-on-surface-variant">
                <span class="material-symbols-outlined">menu</span>
            </button>
            <a href="{{ route('student.dashboard') }}" class="text-lg font-bold text-primary font-headline tracking-tight">
                منصة الامتحانات
            </a>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('student.notifications') }}"
               class="relative p-2 rounded-full hover:bg-surface-container transition-colors text-on-surface-variant">
                <span class="material-symbols-outlined">notifications</span>
            </a>

            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open"
                        class="flex items-center gap-2.5 hover:bg-surface-container rounded-full ps-3 pe-1 py-1 transition-colors">
                    <div class="text-right hidden sm:block">
                        <p class="text-[10px] text-on-surface-variant leading-none uppercase tracking-wider">طالب</p>
                        <p class="text-sm font-semibold text-on-surface leading-tight mt-0.5">{{ auth('student')->user()?->name }}</p>
                    </div>
                    <div class="w-8 h-8 rounded-full bg-secondary-fixed flex items-center justify-center text-on-secondary-fixed-variant font-bold text-sm flex-shrink-0">
                        {{ mb_substr(auth('student')->user()?->name ?? 'ط', 0, 1) }}
                    </div>
                </button>

                <div x-show="open" @click.outside="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute left-0 mt-2 w-48 bg-surface-container-lowest rounded-xl shadow-ambient py-2 z-50 origin-top-left">
                    <a href="{{ route('student.profile') }}"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm text-on-surface hover:bg-surface-container-low transition-colors">
                        <span class="material-symbols-outlined text-[18px]">person</span>
                        الملف الشخصي
                    </a>
                    <div class="my-1 mx-4 h-px bg-surface-container-high"></div>
                    <form method="POST" action="{{ route('student.logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-error hover:bg-error-container/30 transition-colors">
                            <span class="material-symbols-outlined text-[18px]">logout</span>
                            تسجيل الخروج
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-inverse-surface/30 z-30 md:hidden"></div>

    {{-- Sidebar --}}
    <aside class="fixed right-0 top-0 h-full w-64 bg-surface-container-low z-40 flex flex-col pt-14
                  transform transition-transform duration-300 ease-in-out
                  md:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : 'translate-x-full md:translate-x-0'">

        <div class="px-5 py-4 border-b border-outline-variant/20">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-secondary-container rounded-lg flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-on-secondary-container text-[20px] icon-filled"
                         >school</span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-bold text-on-surface truncate">{{ auth('student')->user()?->name }}</p>
                    <p class="text-xs text-on-surface-variant">طالب</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-3 py-3 space-y-0.5 overflow-y-auto">
            @php
                $nav = [
                    ['route' => 'student.dashboard',    'icon' => 'dashboard',     'label' => 'الرئيسية'],
                    ['route' => 'student.exams.index',  'icon' => 'assignment',    'label' => 'امتحاناتي'],
                    ['route' => 'student.grades',       'icon' => 'grade',         'label' => 'الدرجات'],
                    ['route' => 'student.notifications','icon' => 'notifications', 'label' => 'الإشعارات'],
                    ['route' => 'student.profile',      'icon' => 'person',        'label' => 'الملف الشخصي'],
                ];
            @endphp

            @foreach($nav as $item)
                @php $active = request()->routeIs($item['route'] . '*'); @endphp
                <a href="{{ route($item['route']) }}"
                   @click="sidebarOpen = false"
                   class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                          {{ $active
                              ? 'bg-primary text-on-primary'
                              : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                    <span class="material-symbols-outlined text-[20px] {{ $active ? 'icon-filled' : '' }}">{{ $item['icon'] }}</span>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="px-3 py-3 border-t border-outline-variant/20">
            <form method="POST" action="{{ route('student.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-3 px-4 py-2.5 rounded-xl text-sm text-on-surface-variant hover:bg-error-container/30 hover:text-error transition-all">
                    <span class="material-symbols-outlined text-[20px]">logout</span>
                    تسجيل الخروج
                </button>
            </form>
        </div>
    </aside>

    {{-- Page content --}}
    <main class="md:mr-64 pt-14 min-h-screen">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
