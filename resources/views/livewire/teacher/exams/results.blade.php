@php
    $totalStudents = $rows->count();
    $passRate      = $totalStudents > 0 ? round($passCount / $totalStudents * 100) : 0;
    $totalQ        = $exam->totalQuestions();
@endphp

<div class="p-6 md:p-8 max-w-7xl mx-auto">

    {{-- ===== HEADER ===== --}}
    <div class="flex items-start justify-between mb-8 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-primary font-headline leading-tight">
                نتائج الامتحان — {{ $exam->title }}
            </h1>
            <p class="text-on-surface-variant text-sm mt-1">
                {{ $exam->group?->module?->name ?? '' }}
                @if($exam->scheduled_at)
                    &nbsp;·&nbsp; {{ $exam->scheduled_at->format('d/m/Y') }}
                @endif
            </p>
        </div>
        <a
            href="{{ route('teacher.exams.show', $exam) }}"
            wire:navigate
            class="flex items-center gap-2 px-4 py-2 rounded-xl border border-outline-variant/50 text-on-surface-variant text-sm font-medium hover:bg-surface-container transition-colors flex-shrink-0"
        >
            <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
            العودة
        </a>
    </div>

    {{-- ===== STAT CARDS (4) ===== --}}
    <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">

        {{-- Total students --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-ambient">
            <p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider mb-3">إجمالي الطلاب</p>
            <div class="flex items-end justify-between">
                <span class="text-3xl font-extrabold text-primary font-headline">{{ $totalStudents }}</span>
                <span class="material-symbols-outlined text-primary opacity-40 text-2xl">group</span>
            </div>
        </div>

        {{-- Group average --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-ambient">
            <p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider mb-3">متوسط الدرجة</p>
            <div class="flex items-end justify-between">
                <span class="text-3xl font-extrabold text-primary font-headline">
                    {{ $groupAverage }}<span class="text-lg text-on-surface-variant font-normal">/20</span>
                </span>
                <span class="material-symbols-outlined text-primary opacity-40 text-2xl">trending_up</span>
            </div>
        </div>

        {{-- Highest --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-ambient">
            <p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider mb-3">أعلى درجة</p>
            <div class="flex items-end justify-between">
                <span class="text-3xl font-extrabold text-primary font-headline">
                    {{ $highestScore }}<span class="text-lg text-on-surface-variant font-normal">/20</span>
                </span>
                <span class="material-symbols-outlined text-primary opacity-40 text-2xl">workspace_premium</span>
            </div>
        </div>

        {{-- Lowest / pass rate --}}
        <div class="bg-surface-container-lowest p-5 rounded-xl shadow-ambient">
            <p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider mb-3">أدنى درجة</p>
            <div class="flex items-end justify-between">
                <span class="text-3xl font-extrabold font-headline {{ $lowestScore < 10 ? 'text-error' : 'text-primary' }}">
                    {{ $lowestScore }}<span class="text-lg text-on-surface-variant font-normal">/20</span>
                </span>
                <span class="material-symbols-outlined text-xl {{ $lowestScore < 10 ? 'text-error' : 'text-primary opacity-40' }}">
                    {{ $lowestScore < 10 ? 'warning' : 'check_circle' }}
                </span>
            </div>
        </div>
    </section>

    {{-- ===== ANALYTICS BENTO ===== --}}
    <section class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- Most Missed Questions (2-col wide) --}}
        <div class="lg:col-span-2 bg-surface-container-low p-6 rounded-xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-lg font-bold text-primary">الأسئلة الأكثر خطأً</h3>
                <span class="text-xs text-on-surface-variant font-medium">أعلى {{ $mostMissed->count() }} أسئلة</span>
            </div>

            @if($mostMissed->isNotEmpty())
                <div class="space-y-3">
                    @foreach($mostMissed as $missed)
                        @php $wrongPct = round($missed['wrong_rate'] * 100); @endphp
                        <div class="flex items-center gap-4 bg-surface-container-lowest p-4 rounded-xl hover:scale-[1.005] transition-transform">
                            <div class="w-9 h-9 flex-shrink-0 flex items-center justify-center rounded-full bg-error-container text-on-error-container font-bold text-sm">
                                {{ $missed['question_id'] }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-on-surface truncate mb-1.5">{{ $missed['question_text'] }}</p>
                                <div class="w-full bg-surface-container-highest h-1.5 rounded-full overflow-hidden">
                                    <div class="bg-error h-full rounded-full transition-all" style="width: {{ $wrongPct }}%"></div>
                                </div>
                            </div>
                            <span class="text-error font-bold text-sm flex-shrink-0">{{ $wrongPct }}% فشل</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl mb-2 opacity-30">quiz</span>
                    <p class="text-sm">لا توجد بيانات بعد</p>
                </div>
            @endif
        </div>

        {{-- Pass rate card (1-col) --}}
        <div class="bg-primary-container p-6 rounded-xl flex flex-col justify-between">
            <div>
                <h3 class="text-lg font-bold text-on-primary-container mb-6">نسبة النجاح</h3>
                <div class="flex items-center justify-center my-6">
                    <div class="relative w-32 h-32">
                        <svg class="w-full h-full -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="15.9" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="3"></circle>
                            <circle cx="18" cy="18" r="15.9" fill="none"
                                stroke="#afefdd"
                                stroke-width="3"
                                stroke-linecap="round"
                                stroke-dasharray="{{ $passRate }} {{ 100 - $passRate }}"
                                stroke-dashoffset="25"
                            ></circle>
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center rotate-0">
                            <span class="text-2xl font-extrabold text-on-primary-container font-headline">{{ $passRate }}%</span>
                            <span class="text-xs text-on-primary-container/70">نجاح</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="space-y-2 border-t border-on-primary-container/10 pt-4">
                <div class="flex justify-between text-sm text-on-primary-container">
                    <span>ناجح (&ge;10)</span>
                    <span class="font-bold">{{ $passCount }}</span>
                </div>
                <div class="flex justify-between text-sm text-on-primary-container">
                    <span>راسب</span>
                    <span class="font-bold">{{ $totalStudents - $passCount }}</span>
                </div>
                <div class="flex justify-between text-sm text-on-primary-container/70">
                    <span>متوسط المجموعة</span>
                    <span class="font-bold">{{ $groupAverage }}/20</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ===== DETAILED RESULTS TABLE ===== --}}
    <section class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden mb-8">
        <div class="p-6 flex flex-col sm:flex-row justify-between sm:items-center gap-4 border-b border-outline-variant/10">
            <h3 class="text-lg font-bold text-primary">قائمة نتائج الطلاب</h3>
            @if($totalStudents > 0)
                <span class="text-xs text-on-surface-variant bg-surface-container px-3 py-1 rounded-full">
                    {{ $totalStudents }} طالب
                </span>
            @endif
        </div>

        @if($rows->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant text-xs font-bold uppercase tracking-wide">
                            <th scope="col" class="px-6 py-3 text-right">الطالب</th>
                            <th scope="col" class="px-6 py-3 text-center">إجابات صحيحة</th>
                            <th scope="col" class="px-6 py-3 text-center">النسبة</th>
                            <th scope="col" class="px-6 py-3 text-center">الدرجة /20</th>
                            <th scope="col" class="px-6 py-3 text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach($rows as $row)
                            @php
                                $pct = $totalQ > 0 ? round($row['raw'] / $totalQ * 100) : 0;
                                $score = $row['component'];
                                if ($score >= 16)      { $badge = 'ممتاز';  $badgeClass = 'bg-emerald-100 text-emerald-900'; }
                                elseif ($score >= 12)  { $badge = 'جيد';    $badgeClass = 'bg-secondary-container text-on-secondary-container'; }
                                elseif ($score >= 10)  { $badge = 'مقبول';  $badgeClass = 'bg-primary-fixed text-on-primary-fixed-variant'; }
                                else                   { $badge = 'راسب';   $badgeClass = 'bg-error-container text-on-error-container'; }
                            @endphp
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant flex items-center justify-center font-bold text-xs flex-shrink-0">
                                            {{ mb_substr($row['student_name'], 0, 1) }}
                                        </div>
                                        <span class="font-semibold text-on-surface">{{ $row['student_name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center text-on-surface-variant">
                                    {{ $row['raw'] }} / {{ $totalQ }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <div class="w-20 bg-surface-container-highest h-1.5 rounded-full">
                                            <div class="{{ $score >= 10 ? 'bg-primary' : 'bg-error' }} h-full rounded-full" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-on-surface-variant">{{ $pct }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-headline font-extrabold {{ $score >= 10 ? 'text-primary' : 'text-error' }}">
                                    {{ $score }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $badgeClass }}">{{ $badge }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-16 text-on-surface-variant">
                <span class="material-symbols-outlined text-5xl mb-3 opacity-20">assignment</span>
                <p class="text-base font-medium">لا توجد جلسات مكتملة بعد</p>
                <p class="text-sm mt-1 opacity-70">ستظهر النتائج هنا بعد انتهاء الطلاب من الامتحان</p>
            </div>
        @endif
    </section>

    {{-- ===== QUESTION PASS RATES ===== --}}
    @if($mostMissed->isNotEmpty())
        <section class="bg-surface-container-low p-6 rounded-xl">
            <h3 class="text-lg font-bold text-primary mb-5">معدلات النجاح لكل سؤال (الأكثر تحدياً)</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                @foreach($mostMissed as $missed)
                    @php
                        $passPct = round((1 - $missed['wrong_rate']) * 100);
                        $isHard  = $passPct < 40;
                    @endphp
                    <div class="bg-surface-container-lowest p-4 rounded-xl flex flex-col items-center
                        {{ $isHard ? 'ring-2 ring-error/30' : '' }}">
                        <span class="text-xs text-on-surface-variant font-bold mb-2">
                            س.{{ $missed['question_id'] }}
                        </span>
                        <span class="text-lg font-extrabold {{ $isHard ? 'text-error' : 'text-primary' }}">
                            {{ $passPct }}%
                        </span>
                        <span class="text-[10px] text-on-surface-variant mt-1">نجاح</span>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

</div>
