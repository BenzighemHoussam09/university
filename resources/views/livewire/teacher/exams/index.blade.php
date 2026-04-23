<div class="p-6 lg:p-8 space-y-6" x-data="{ tab: '{{ $active->isNotEmpty() ? 'active' : ($scheduled->isNotEmpty() ? 'scheduled' : 'ended') }}' }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant mb-2 font-cairo">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <span class="text-on-surface font-semibold">الامتحانات</span>
            </nav>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">الامتحانات</h1>
            <p class="text-on-surface-variant mt-1 text-sm font-cairo">إدارة وجدولة امتحاناتك</p>
        </div>
        <a href="{{ route('teacher.exams.create') }}" wire:navigate
           class="btn-gradient text-on-primary px-5 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity self-start sm:self-auto">
            <span class="material-symbols-outlined text-[20px] icon-filled">add_circle</span>
            <span class="font-cairo">امتحان جديد</span>
        </a>
    </div>

    {{-- Summary badges --}}
    <div class="grid grid-cols-3 gap-4">
        <button @click="tab = 'active'"
                class="text-center p-4 rounded-xl border-2 transition-all font-cairo"
                :class="tab === 'active'
                    ? 'bg-emerald-50 border-emerald-400 text-emerald-800'
                    : 'bg-surface-container-lowest border-outline-variant/20 text-on-surface-variant hover:border-emerald-200'">
            <div class="text-2xl font-black">{{ $active->count() }}</div>
            <div class="text-xs font-bold mt-1 flex items-center justify-center gap-1">
                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                نشط الآن
            </div>
        </button>
        <button @click="tab = 'scheduled'"
                class="text-center p-4 rounded-xl border-2 transition-all font-cairo"
                :class="tab === 'scheduled'
                    ? 'bg-blue-50 border-blue-400 text-blue-800'
                    : 'bg-surface-container-lowest border-outline-variant/20 text-on-surface-variant hover:border-blue-200'">
            <div class="text-2xl font-black">{{ $scheduled->count() }}</div>
            <div class="text-xs font-bold mt-1 flex items-center justify-center gap-1">
                <span class="w-2 h-2 rounded-full bg-blue-500 inline-block"></span>
                مجدول
            </div>
        </button>
        <button @click="tab = 'ended'"
                class="text-center p-4 rounded-xl border-2 transition-all font-cairo"
                :class="tab === 'ended'
                    ? 'bg-surface-container border-outline text-on-surface'
                    : 'bg-surface-container-lowest border-outline-variant/20 text-on-surface-variant hover:border-outline-variant'">
            <div class="text-2xl font-black">{{ $ended->count() }}</div>
            <div class="text-xs font-bold mt-1 flex items-center justify-center gap-1">
                <span class="w-2 h-2 rounded-full bg-outline inline-block"></span>
                منتهٍ
            </div>
        </button>
    </div>

    {{-- Active Tab --}}
    <div x-show="tab === 'active'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
            <div class="bg-emerald-50 border-b border-emerald-100 px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-emerald-900 font-cairo flex items-center gap-2">
                    <span class="material-symbols-outlined text-emerald-600 text-[20px] icon-filled">play_circle</span>
                    الامتحانات النشطة
                </h2>
                <span class="text-xs font-bold text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full font-cairo">{{ $active->count() }} نشط</span>
            </div>
            @if ($active->isEmpty())
                <div class="flex flex-col items-center justify-center py-14 text-center px-6">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">play_circle</span>
                    <p class="text-on-surface-variant font-cairo text-sm">لا توجد امتحانات نشطة حالياً</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20">
                                <th scope="col" class="px-6 py-4 font-cairo text-start">الامتحان</th>
                                <th scope="col" class="px-6 py-4 font-cairo">المقياس / الفوج</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">بدأ منذ</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach ($active as $exam)
                                <tr class="hover:bg-emerald-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 bg-emerald-100 flex items-center justify-center rounded-lg text-emerald-700 flex-shrink-0">
                                                <span class="material-symbols-outlined text-[18px]">assignment</span>
                                            </div>
                                            <span class="font-bold text-on-surface font-cairo text-sm">{{ $exam->title }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant font-cairo text-sm">
                                        {{ $exam->group->module->name ?? '—' }}
                                        <span class="text-outline mx-1">·</span>
                                        {{ $exam->group->name }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-on-surface-variant font-cairo text-sm">
                                        {{ $exam->started_at?->diffForHumans() ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('teacher.exams.monitor', $exam) }}" wire:navigate
                                               class="flex items-center gap-1 bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-sm font-bold font-cairo hover:bg-emerald-700 transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">monitor</span>
                                                مراقبة
                                            </a>
                                            <a href="{{ route('teacher.exams.show', $exam) }}" wire:navigate
                                               class="flex items-center gap-1 text-on-primary-fixed-variant hover:bg-primary-fixed px-3 py-1.5 rounded-lg text-sm font-bold font-cairo transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                                عرض
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Scheduled Tab --}}
    <div x-show="tab === 'scheduled'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
            <div class="bg-blue-50 border-b border-blue-100 px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-blue-900 font-cairo flex items-center gap-2">
                    <span class="material-symbols-outlined text-blue-600 text-[20px] icon-filled">calendar_clock</span>
                    الامتحانات المجدولة
                </h2>
                <span class="text-xs font-bold text-blue-700 bg-blue-100 px-3 py-1 rounded-full font-cairo">{{ $scheduled->count() }} مجدول</span>
            </div>
            @if ($scheduled->isEmpty())
                <div class="flex flex-col items-center justify-center py-14 text-center px-6">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">calendar_clock</span>
                    <p class="text-on-surface-variant font-cairo text-sm mb-5">لا توجد امتحانات مجدولة</p>
                    <a href="{{ route('teacher.exams.create') }}" wire:navigate
                       class="btn-gradient text-on-primary px-5 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity font-cairo text-sm">
                        <span class="material-symbols-outlined text-[18px] icon-filled">add_circle</span>
                        جدولة امتحان
                    </a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20">
                                <th scope="col" class="px-6 py-4 font-cairo text-start">الامتحان</th>
                                <th scope="col" class="px-6 py-4 font-cairo">المقياس / الفوج</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">موعد الانطلاق</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">المدة</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach ($scheduled as $exam)
                                <tr class="hover:bg-blue-50/30 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 bg-secondary-fixed flex items-center justify-center rounded-lg text-on-secondary-fixed-variant flex-shrink-0">
                                                <span class="material-symbols-outlined text-[18px]">assignment</span>
                                            </div>
                                            <span class="font-bold text-on-surface font-cairo text-sm">{{ $exam->title }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant font-cairo text-sm">
                                        {{ $exam->group->module->name ?? '—' }}
                                        <span class="text-outline mx-1">·</span>
                                        {{ $exam->group->name }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="font-cairo text-sm text-on-surface">{{ $exam->scheduled_at->format('d/m/Y') }}</div>
                                        <div class="font-cairo text-xs text-on-surface-variant">{{ $exam->scheduled_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-on-surface-variant font-cairo text-sm">
                                        {{ $exam->duration_minutes }} د
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <a href="{{ route('teacher.exams.show', $exam) }}" wire:navigate
                                           class="flex items-center justify-center gap-1 text-on-primary-fixed-variant hover:bg-primary-fixed px-3 py-1.5 rounded-lg text-sm font-bold font-cairo transition-colors">
                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                            عرض
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Ended Tab --}}
    <div x-show="tab === 'ended'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
            <div class="bg-surface-container-low border-b border-outline-variant/20 px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                    <span class="material-symbols-outlined text-outline text-[20px] icon-filled">task_alt</span>
                    الامتحانات المنتهية
                </h2>
                <span class="text-xs font-bold text-on-surface-variant bg-surface-container px-3 py-1 rounded-full font-cairo">{{ $ended->count() }} منتهٍ</span>
            </div>
            @if ($ended->isEmpty())
                <div class="flex flex-col items-center justify-center py-14 text-center px-6">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">task_alt</span>
                    <p class="text-on-surface-variant font-cairo text-sm">لا توجد امتحانات منتهية بعد</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20">
                                <th scope="col" class="px-6 py-4 font-cairo text-start">الامتحان</th>
                                <th scope="col" class="px-6 py-4 font-cairo">المقياس / الفوج</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">انتهى</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach ($ended as $exam)
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 bg-surface-container flex items-center justify-center rounded-lg text-outline flex-shrink-0">
                                                <span class="material-symbols-outlined text-[18px]">assignment_turned_in</span>
                                            </div>
                                            <span class="font-bold text-on-surface font-cairo text-sm">{{ $exam->title }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant font-cairo text-sm">
                                        {{ $exam->group->module->name ?? '—' }}
                                        <span class="text-outline mx-1">·</span>
                                        {{ $exam->group->name }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-on-surface-variant font-cairo text-sm">
                                        {{ $exam->ended_at?->diffForHumans() ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('teacher.exams.results', $exam) }}" wire:navigate
                                               class="flex items-center gap-1 text-on-primary-fixed-variant hover:bg-primary-fixed px-3 py-1.5 rounded-lg text-sm font-bold font-cairo transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">bar_chart</span>
                                                النتائج
                                            </a>
                                            <a href="{{ route('teacher.exams.show', $exam) }}" wire:navigate
                                               class="flex items-center gap-1 text-on-surface-variant hover:bg-surface-container px-3 py-1.5 rounded-lg text-sm font-bold font-cairo transition-colors">
                                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                                عرض
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Totally empty state --}}
    @if ($scheduled->isEmpty() && $active->isEmpty() && $ended->isEmpty())
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient flex flex-col items-center justify-center py-20 text-center px-6">
            <div class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mb-5">
                <span class="material-symbols-outlined text-5xl text-outline-variant">assignment</span>
            </div>
            <h3 class="text-xl font-bold text-on-surface font-cairo mb-2">لا توجد امتحانات بعد</h3>
            <p class="text-on-surface-variant text-sm max-w-md font-cairo leading-relaxed mb-6">
                ابدأ بإنشاء امتحانك الأول وجدولته للطلاب.
            </p>
            <a href="{{ route('teacher.exams.create') }}" wire:navigate
               class="btn-gradient text-on-primary px-6 py-3 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity font-cairo">
                <span class="material-symbols-outlined text-[20px] icon-filled">add_circle</span>
                إنشاء أول امتحان
            </a>
        </div>
    @endif

</div>
