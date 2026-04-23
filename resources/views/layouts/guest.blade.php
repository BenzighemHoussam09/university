<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'بوابة الامتحانات') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col">

    <header class="fixed top-0 w-full z-50 glass-nav shadow-sm px-6 py-4 flex justify-between items-center">
        <a href="{{ route('home') }}" class="text-xl font-bold text-primary font-headline tracking-tight">
            منصة الامتحانات
        </a>
        <div class="flex items-center gap-2">
            <button class="p-2 rounded-full hover:bg-surface-container transition-colors text-on-surface-variant" title="تغيير اللغة">
                <span class="material-symbols-outlined text-[20px]">language</span>
            </button>
        </div>
    </header>

    <div class="fixed top-0 left-0 w-full h-0.5 bg-gradient-to-l from-primary via-primary-container to-transparent opacity-30 z-[60]"></div>

    <main class="flex-grow flex items-center justify-center pt-20 pb-12 px-4">
        {{ $slot }}
    </main>

    <footer class="w-full py-6 bg-surface-container-low border-t border-outline-variant/20">
        <div class="flex flex-col md:flex-row justify-between items-center px-8 gap-4">
            <div class="text-base font-black font-headline text-primary uppercase tracking-tight">منصة الامتحانات</div>
            <div class="flex gap-6 text-xs uppercase tracking-widest">
                <span class="text-on-surface-variant/60">© {{ date('Y') }} منصة الامتحانات. جميع الحقوق محفوظة.</span>
            </div>
        </div>
    </footer>

    @livewireScripts
</body>
</html>
