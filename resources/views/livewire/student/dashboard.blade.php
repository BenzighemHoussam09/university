<div class="p-6 lg:p-8 space-y-8" wire:poll.30s>

    {{-- Welcome --}}
    <section>
        <h1 class="text-3xl font-headline font-extrabold text-primary mb-1">
            أهلاً بك، {{ $student->name }}
        </h1>
        <p class="text-on-surface-variant font-body text-sm">
            @if($upcomingExams->isNotEmpty())
                لديك {{ $upcomingExams->count() }} امتحان قادم. حافظ على تميزك الأكاديمي.
            @else
                لا توجد امتحانات قادمة حالياً. استمر في متابعة نتائجك.
            @endif
        </p>
    </section>

    {{-- Stats Grid --}}
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        {{-- Avg Grade --}}
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow group">
            <div class="flex justify-between items-start mb-4">
                <span class="p-2 bg-primary-fixed text-on-primary-fixed rounded-lg">
                    <span class="material-symbols-outlined icon-filled">school</span>
                </span>
            </div>
            <h3 class="text-on-surface-variant text-xs font-label uppercase tracking-widest mb-1">معدل الدرجات</h3>
            <div class="flex items-baseline gap-1">
                @if($avgGrade !== null)
                    <span class="text-4xl font-headline font-extrabold text-primary">{{ $avgGrade }}</span>
                    <span class="text-on-surface-variant">/20</span>
                @else
                    <span class="text-4xl font-headline font-extrabold text-on-surface-variant">—</span>
                @endif
            </div>
        </div>

        {{-- Completed Exams --}}
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <span class="p-2 bg-secondary-container text-on-secondary-container rounded-lg">
                    <span class="material-symbols-outlined icon-filled">assignment_turned_in</span>
                </span>
            </div>
            <h3 class="text-on-surface-variant text-xs font-label uppercase tracking-widest mb-1">الامتحانات المكتملة</h3>
            <span class="text-4xl font-headline font-extrabold text-primary">{{ $completedCount }}</span>
        </div>

        {{-- Upcoming --}}
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <span class="p-2 bg-tertiary-fixed text-on-tertiary-fixed rounded-lg">
                    <span class="material-symbols-outlined">calendar_month</span>
                </span>
            </div>
            <h3 class="text-on-surface-variant text-xs font-label uppercase tracking-widest mb-1">الامتحانات القادمة</h3>
            <span class="text-4xl font-headline font-extrabold text-primary">{{ $upcomingExams->count() }}</span>
        </div>

        {{-- Absence --}}
        @if($student->absence_count > 0)
        <div class="bg-surface-container-lowest p-6 rounded-xl border-r-4 border-error shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <span class="p-2 bg-error-container text-on-error-container rounded-lg">
                    <span class="material-symbols-outlined icon-filled">person_off</span>
                </span>
                <span class="text-error text-xs font-bold">تنبيه الغياب</span>
            </div>
            <h3 class="text-on-surface-variant text-xs font-label uppercase tracking-widest mb-1">عدد الغيابات</h3>
            <span class="text-4xl font-headline font-extrabold text-error">{{ $student->absence_count }}</span>
            <p class="text-xs text-error mt-2">{{ $remaining }} غياب متبقٍ قبل الحظر</p>
        </div>
        @else
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-start mb-4">
                <span class="p-2 bg-primary-fixed text-on-primary-fixed-variant rounded-lg">
                    <span class="material-symbols-outlined icon-filled">check_circle</span>
                </span>
            </div>
            <h3 class="text-on-surface-variant text-xs font-label uppercase tracking-widest mb-1">عدد الغيابات</h3>
            <span class="text-4xl font-headline font-extrabold text-primary">0</span>
        </div>
        @endif

    </section>

    {{-- Main Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Recent Results --}}
        <div class="lg:col-span-2">
            <div class="flex justify-between items-center mb-4 px-1">
                <h2 class="text-xl font-headline font-bold text-primary">النتائج الأخيرة</h2>
                <a href="{{ route('student.exams.index') }}" wire:navigate
                   class="text-primary font-semibold text-sm hover:underline">عرض الكل</a>
            </div>

            @if($recentResults->isNotEmpty())
            <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-sm">
                <table class="w-full text-right">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant text-xs uppercase tracking-wider font-label">
                            <th scope="col" class="px-5 py-3.5 font-semibold">المادة</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold">التاريخ</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold">الدرجة</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold text-left">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        @foreach($recentResults as $session)
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-2 h-2 rounded-full flex-shrink-0 {{ ($session->exam_score_component ?? 0) >= 10 ? 'bg-emerald-500' : 'bg-error' }}"></div>
                                    <div>
                                        <p class="font-bold text-on-surface text-sm">{{ $session->exam?->title ?? '—' }}</p>
                                        <p class="text-xs text-on-surface-variant">{{ $session->exam?->group?->module?->name ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-on-surface-variant text-sm">
                                {{ $session->completed_at?->format('d M Y') ?? '—' }}
                            </td>
                            <td class="px-5 py-4 font-headline font-bold text-on-surface">
                                {{ number_format($session->exam_score_component ?? 0, 2) }}<span class="text-on-surface-variant font-normal text-xs">/20</span>
                            </td>
                            <td class="px-5 py-4 text-left">
                                @if(($session->exam_score_component ?? 0) >= 10)
                                    <span class="px-2.5 py-1 bg-primary-fixed text-on-primary-fixed-variant text-xs font-bold rounded-full">ناجح</span>
                                @else
                                    <span class="px-2.5 py-1 bg-error-container text-on-error-container text-xs font-bold rounded-full">راسب</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="bg-surface-container-lowest rounded-xl p-12 text-center shadow-sm">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant/40 mb-3 block">assignment_late</span>
                <p class="text-on-surface-variant font-semibold text-sm">لا توجد نتائج بعد</p>
                <p class="text-xs text-on-surface-variant/60 mt-1">ستظهر نتائجك هنا بعد إكمال امتحاناتك</p>
            </div>
            @endif
        </div>

        {{-- Upcoming Exams Sidebar --}}
        <div>
            <div class="px-1 mb-4">
                <h2 class="text-xl font-headline font-bold text-primary">الامتحانات القادمة</h2>
            </div>
            <div class="space-y-3">
                @forelse($upcomingExams as $exam)
                    <a href="{{ $exam->status->value === 'active' ? route('student.exams.session', $exam->id) : route('student.exams.waiting', $exam->id) }}"
                       wire:navigate
                       class="block bg-surface-container-lowest p-4 rounded-xl shadow-sm border-r-4 {{ $exam->status->value === 'active' ? 'border-primary' : 'border-outline-variant hover:border-primary' }} transition-colors group">
                        <div class="flex justify-between items-start mb-2">
                            @if($exam->status->value === 'active')
                                <span class="text-[10px] font-label font-bold text-on-primary bg-primary px-2 py-0.5 rounded">جارٍ الآن</span>
                            @else
                                <span class="text-[10px] font-label font-bold text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">
                                    {{ $exam->scheduled_at->format('d M') }}
                                </span>
                            @endif
                            <span class="material-symbols-outlined text-[18px] text-on-surface-variant group-hover:text-primary transition-colors">event</span>
                        </div>
                        <h4 class="font-bold text-on-surface text-sm mb-1 truncate">{{ $exam->title }}</h4>
                        <div class="flex items-center gap-1 text-xs text-on-surface-variant">
                            <span class="material-symbols-outlined text-[13px]">schedule</span>
                            {{ $exam->scheduled_at->format('H:i') }}
                            @if($exam->group?->module?->name)
                                · {{ $exam->group->module->name }}
                            @endif
                        </div>
                    </a>
                @empty
                    <div class="bg-surface-container-lowest rounded-xl p-6 text-center shadow-sm">
                        <span class="material-symbols-outlined text-3xl text-on-surface-variant/40 mb-2 block">event_available</span>
                        <p class="text-sm text-on-surface-variant">لا توجد امتحانات قادمة</p>
                    </div>
                @endforelse

                <a href="{{ route('student.exams.index') }}" wire:navigate
                   class="flex items-center justify-center gap-2 w-full py-3.5 btn-gradient text-on-primary rounded-xl font-bold text-sm hover:opacity-90 transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    جدول الامتحانات الكامل
                </a>
            </div>
        </div>

    </div>
</div>
