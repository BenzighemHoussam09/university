<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>جلسة امتحان — {{ config('app.name', 'بوابة الامتحانات') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="overflow-hidden h-dvh bg-surface text-on-surface" x-data>
    {{ $slot }}
    @livewireScripts
</body>
</html>
