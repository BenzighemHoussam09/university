@php
    $totalQ       = $exam->totalQuestions();
    $totalS       = $liveStatuses->count();
    $connectedS   = $liveStatuses->where('connected', true)->count();
    $activeS      = $liveStatuses->where('status', 'active')->count();
    $completedS   = $liveStatuses->where('status', 'completed')->count();
    $disconnectedS = $liveStatuses->where('connected', false)->where('status', 'active')->count();
    $isActive     = $exam->status?->value === 'active';
@endphp

<div
    x-data="monitor()"
    x-on:student-disconnected.window="onStudentDisconnected($event.detail)"
    x-on:student-violation.window="onStudentViolation($event.detail)"
    wire:poll.keep-alive.2s="refresh"
    class="flex h-[calc(100vh-3.5rem)] overflow-hidden"
>

    {{-- ===== MAIN CONTENT ===== --}}
    <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-extrabold text-primary font-headline leading-tight">
                    مراقبة الامتحان — {{ $exam->title }}
                </h1>
                <p class="text-on-surface-variant text-sm mt-1 flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full {{ $isActive ? 'bg-primary animate-pulse' : 'bg-outline' }}"></span>
                        {{ $isActive ? 'جارٍ' : ($exam->status?->value ?? 'غير معروف') }}
                    </span>
                    &nbsp;·&nbsp; يتجدد كل 2 ثانية
                    @if($exam->global_extra_minutes > 0)
                        &nbsp;·&nbsp;
                        <span class="text-primary font-medium">+{{ $exam->global_extra_minutes }} دقيقة إضافية للجميع</span>
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

        {{-- Disconnection alert banner --}}
        <div
            x-show="alertStudentName !== null"
            x-cloak
            x-transition
            class="bg-error-container/70 border border-error/20 text-on-error-container rounded-xl p-4 flex items-center justify-between gap-4"
        >
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-error">wifi_off</span>
                <span class="text-sm font-semibold">
                    الطالب "<span x-text="alertStudentName" class="font-bold"></span>" انقطع عن الاتصال.
                </span>
            </div>
            <button @click="alertStudentName = null" class="text-error hover:text-error/70 font-bold text-lg leading-none">✕</button>
        </div>

        {{-- Violation alert banner --}}
        <div
            x-show="alertViolationName !== null"
            x-cloak
            x-transition
            class="bg-error-container/70 border border-error/20 text-on-error-container rounded-xl p-4 flex items-center justify-between gap-4"
        >
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-error">warning</span>
                <span class="text-sm font-semibold">
                    مخالفة جديدة — الطالب "<span x-text="alertViolationName" class="font-bold"></span>"
                </span>
            </div>
            <button @click="alertViolationName = null" class="text-error hover:text-error/70 font-bold text-lg leading-none">✕</button>
        </div>

        {{-- Stat cards --}}
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="bg-surface-container-lowest p-5 rounded-xl shadow-ambient">
                <p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider mb-3">إجمالي الطلاب</p>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-extrabold text-primary font-headline">{{ $totalS }}</span>
                    <span class="material-symbols-outlined text-primary opacity-40 text-2xl">group</span>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-5 rounded-xl shadow-ambient">
                <p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider mb-3">متصل / المجموع</p>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-extrabold text-primary font-headline">
                        {{ $connectedS }}<span class="text-lg text-on-surface-variant font-normal">/{{ $totalS }}</span>
                    </span>
                    <span class="material-symbols-outlined text-primary opacity-40 text-2xl">wifi</span>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-5 rounded-xl shadow-ambient">
                <p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider mb-3">أكمل الامتحان</p>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-extrabold text-primary font-headline">{{ $completedS }}</span>
                    <span class="material-symbols-outlined text-primary opacity-40 text-2xl">task_alt</span>
                </div>
            </div>

            <div class="bg-surface-container-lowest p-5 rounded-xl shadow-ambient {{ $disconnectedS > 0 ? 'ring-2 ring-error/30' : '' }}">
                <p class="text-on-surface-variant text-xs font-bold uppercase tracking-wider mb-3">منقطع الاتصال</p>
                <div class="flex items-end justify-between">
                    <span class="text-3xl font-extrabold font-headline {{ $disconnectedS > 0 ? 'text-error' : 'text-primary' }}">
                        {{ $disconnectedS }}
                    </span>
                    <span class="material-symbols-outlined text-xl {{ $disconnectedS > 0 ? 'text-error' : 'text-primary opacity-40' }}">
                        {{ $disconnectedS > 0 ? 'wifi_off' : 'wifi' }}
                    </span>
                </div>
            </div>

        </section>

        {{-- Controls bar (exam active only) --}}
        @if($isActive)
            <div class="bg-surface-container-low p-5 rounded-xl flex flex-wrap items-end gap-4">

                {{-- Extend all --}}
                <div class="flex-1 min-w-[220px]">
                    <p class="text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-2">تمديد الوقت لجميع الطلاب</p>
                    <div class="flex gap-2">
                        <input
                            type="number"
                            min="1"
                            max="120"
                            x-model.number="globalMinutes"
                            class="bg-surface-container-highest border-none rounded-xl px-3 py-2 text-sm w-20 focus:ring-2 focus:ring-surface-tint"
                            placeholder="دقيقة"
                        >
                        <button
                            @click="$wire.extendGlobal(globalMinutes); globalMinutes = 5"
                            class="px-4 py-2 bg-primary text-on-primary text-sm font-bold rounded-xl hover:bg-primary/90 transition-colors"
                        >
                            + أضف وقتاً
                        </button>
                    </div>
                </div>

{{-- End exam (confirm pattern using x-show to avoid morph issues) --}}
                <div class="mr-auto flex items-center gap-3">
                    <div x-show="!showEndConfirm" x-cloak>
                        <button
                            @click="showEndConfirm = true"
                            class="px-5 py-2 bg-error text-on-error text-sm font-bold rounded-xl hover:bg-error/90 transition-colors shadow-sm"
                        >
                            إنهاء الامتحان
                        </button>
                    </div>
                    <div x-show="showEndConfirm" x-cloak class="flex items-center gap-3">
                        <span class="text-sm text-error font-semibold">إنهاء الامتحان وتأكيد جميع الإجابات؟</span>
                        <button
                            wire:click="endExam"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 bg-error text-on-error text-sm font-bold rounded-xl hover:bg-error/90 disabled:opacity-60 transition-colors"
                        >
                            <span wire:loading.remove wire:target="endExam">تأكيد الإنهاء</span>
                            <span wire:loading wire:target="endExam">جارٍ...</span>
                        </button>
                        <button
                            @click="showEndConfirm = false"
                            class="px-4 py-2 bg-surface-container-highest text-on-surface-variant text-sm font-medium rounded-xl hover:bg-surface-container-high transition-colors"
                        >
                            إلغاء
                        </button>
                    </div>
                </div>

            </div>
        @endif

        {{-- Student table --}}
        <section class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">

            <div class="px-6 py-5 flex justify-between items-center border-b border-outline-variant/10">
                <h3 class="text-lg font-bold text-primary font-headline">مراقبة الجلسات المباشرة</h3>
                @if($totalS > 0)
                    <span class="text-xs text-on-surface-variant bg-surface-container px-3 py-1 rounded-full">
                        {{ $totalS }} طالب
                    </span>
                @endif
            </div>

            @if($liveStatuses->isEmpty())
                <div class="flex flex-col items-center justify-center py-16 text-on-surface-variant">
                    <span class="material-symbols-outlined text-5xl mb-3 opacity-20">group_off</span>
                    <p class="text-base font-medium">لا توجد جلسات لهذا الامتحان</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-surface-container-low text-on-surface-variant text-xs font-bold uppercase tracking-wide">
                                <th scope="col" class="px-6 py-3 text-right">الطالب</th>
                                <th scope="col" class="px-6 py-3 text-center">التقدم</th>
                                <th scope="col" class="px-6 py-3 text-center">الوقت المتبقي</th>
                                <th scope="col" class="px-6 py-3 text-center">الاتصال</th>
                                <th scope="col" class="px-6 py-3 text-center">آخر نشاط</th>
                                <th scope="col" class="px-6 py-3 text-center">المخالفات</th>
                                @if($isActive)
                                    <th scope="col" class="px-6 py-3 text-center">تمديد</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach($liveStatuses as $s)
                                @php
                                    $isCompleted   = $s['status'] === 'completed';
                                    $isDisconnected = ! $s['connected'] && ! $isCompleted;
                                    $progressPct   = ($totalQ > 0 && $s['last_answered_question_index'] !== null)
                                        ? min(100, round($s['last_answered_question_index'] / $totalQ * 100))
                                        : 0;
                                    $timeLow       = $s['remaining_seconds'] !== null && $s['remaining_seconds'] < 300;
                                @endphp
                                <tr class="transition-colors {{ $isDisconnected ? 'bg-error-container/20 hover:bg-error-container/30' : 'hover:bg-surface-container-low' }}">

                                    {{-- Student --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant flex items-center justify-center font-bold text-sm flex-shrink-0">
                                                {{ mb_substr($s['name'], 0, 1) }}
                                            </div>
                                            <span class="font-semibold text-on-surface">{{ $s['name'] }}</span>
                                        </div>
                                    </td>

                                    {{-- Progress --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($isCompleted)
                                            <span class="text-xs font-bold text-primary bg-primary/10 px-2 py-1 rounded-full">مُسلَّم</span>
                                        @else
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="text-xs font-bold text-primary">
                                                    {{ $s['last_answered_question_index'] ?? 0 }} / {{ $totalQ }}
                                                </span>
                                                <div class="w-20 h-1.5 bg-surface-container-highest rounded-full overflow-hidden">
                                                    <div class="bg-primary h-full rounded-full" style="width: {{ $progressPct }}%"></div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Time remaining --}}
                                    <td class="px-6 py-4 text-center font-headline font-bold tabular-nums
                                        {{ $isCompleted ? 'text-on-surface-variant' : ($timeLow ? 'text-error' : 'text-on-surface-variant') }}">
                                        @if($isCompleted)
                                            <span class="text-xs font-normal text-on-surface-variant">—</span>
                                        @elseif($s['remaining_seconds'] !== null)
                                            {{ gmdate('H:i:s', $s['remaining_seconds']) }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Connection --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($isCompleted)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold">
                                                <span class="w-2 h-2 rounded-full bg-primary"></span>
                                                مكتمل
                                            </span>
                                        @elseif($s['connected'])
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold">
                                                <span class="w-2 h-2 rounded-full bg-primary animate-pulse"></span>
                                                متصل
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-error/10 text-error text-xs font-bold">
                                                <span class="w-2 h-2 rounded-full bg-error"></span>
                                                منقطع
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Last heartbeat --}}
                                    <td class="px-6 py-4 text-center text-xs text-on-surface-variant">
                                        @if($s['last_heartbeat_at'])
                                            {{ \Carbon\Carbon::parse($s['last_heartbeat_at'])->diffForHumans() }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Incidents --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($s['incident_count'] > 0)
                                            <span class="px-2 py-1 rounded-full bg-error-container text-on-error-container text-xs font-bold">
                                                {{ $s['incident_count'] }}
                                            </span>
                                        @else
                                            <span class="text-on-surface-variant/40 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- Per-student time extend --}}
                                    @if($isActive)
                                        <td class="px-6 py-4 text-center">
                                            @if($s['status'] === 'active')
                                                <div x-data="{ mins: 5 }" class="flex items-center justify-center gap-1">
                                                    <input
                                                        type="number"
                                                        min="1"
                                                        max="120"
                                                        x-model.number="mins"
                                                        class="bg-surface-container-highest border-none rounded-lg px-2 py-1 text-xs w-14 focus:ring-1 focus:ring-surface-tint text-center"
                                                    >
                                                    <button
                                                        @click="$wire.extendStudent({{ $s['student_id'] }}, mins)"
                                                        class="px-2 py-1 bg-primary/10 text-primary text-xs font-bold rounded-lg hover:bg-primary/20 transition-colors"
                                                    >
                                                        +م
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-on-surface-variant/30 text-xs">—</span>
                                            @endif
                                        </td>
                                    @endif

                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Table footer --}}
                <div class="px-6 py-4 bg-surface-container-low/30 border-t border-outline-variant/10 flex flex-wrap gap-6 text-xs text-on-surface-variant">
                    <span>الكل: <span class="font-bold text-on-surface">{{ $totalS }}</span></span>
                    <span>نشط: <span class="font-bold text-primary">{{ $activeS }}</span></span>
                    <span>مكتمل: <span class="font-bold text-on-surface">{{ $completedS }}</span></span>
                    <span>متصل: <span class="font-bold text-primary">{{ $connectedS }}</span></span>
                    @if($disconnectedS > 0)
                        <span>منقطع: <span class="font-bold text-error">{{ $disconnectedS }}</span></span>
                    @endif
                </div>
            @endif

        </section>

    </div>

    {{-- ===== ACTIVITY FEED SIDEBAR ===== --}}
    <aside class="hidden xl:flex flex-col w-80 bg-surface-container-lowest border-r border-outline-variant/10 overflow-hidden flex-shrink-0">

        <div class="p-5 border-b border-outline-variant/10">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-primary flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">sensors</span>
                    سجل النشاط
                </h3>
                <span class="px-2 py-0.5 bg-primary/5 text-primary text-[10px] font-bold rounded-full uppercase tracking-wider">مباشر</span>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-4"
             style="scrollbar-width: thin; scrollbar-color: rgba(0,52,43,0.1) transparent;">

            {{-- Empty state (Alpine-only) --}}
            <div x-show="activityFeed.length === 0" x-cloak
                 class="flex flex-col items-center justify-center h-full py-10 text-on-surface-variant opacity-50">
                <span class="material-symbols-outlined text-3xl mb-2">history_toggle_off</span>
                <p class="text-xs text-center">سيظهر السجل هنا عند انقطاع اتصال طالب أو تسجيل مخالفة</p>
            </div>

            {{-- Feed items --}}
            <template x-for="(item, index) in activityFeed" :key="index">
                <div class="flex gap-3">
                    <div
                        class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm"
                        :class="{
                            'bg-error/10 text-error': item.type === 'disconnect' || item.type === 'violation',
                            'bg-primary/10 text-primary': item.type !== 'disconnect' && item.type !== 'violation'
                        }"
                    >
                        <span class="material-symbols-outlined text-[16px]"
                              x-text="item.type === 'disconnect' ? 'wifi_off' : (item.type === 'violation' ? 'warning' : 'sensors')"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p
                            class="text-xs font-bold"
                            :class="(item.type === 'disconnect' || item.type === 'violation') ? 'text-error' : 'text-on-surface'"
                            x-text="item.studentName"
                        ></p>
                        <p class="text-xs text-on-surface-variant leading-relaxed mt-0.5" x-text="item.message"></p>
                        <p class="text-[10px] text-outline mt-1" x-text="item.time"></p>
                    </div>
                </div>
            </template>

        </div>

        <div x-show="activityFeed.length > 0" x-cloak
             class="p-3 border-t border-outline-variant/10">
            <button
                @click="activityFeed = []"
                class="w-full py-2 text-xs font-bold text-on-surface-variant hover:bg-surface-container rounded-xl transition-colors"
            >
                مسح السجل
            </button>
        </div>

    </aside>

</div>
