<div class="p-6 lg:p-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant mb-2 font-cairo">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <a href="{{ route('teacher.exams.index') }}" wire:navigate class="hover:text-primary transition-colors">الامتحانات</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <span class="text-on-surface font-semibold truncate max-w-[200px]">{{ $exam->title }}</span>
            </nav>
            <div class="flex items-center gap-3 flex-wrap">
                <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">{{ $exam->title }}</h1>
                {{-- Status badge --}}
                @php
                    $statusClasses = match($exam->status->value) {
                        'active'    => 'bg-emerald-100 text-emerald-800 border border-emerald-200',
                        'scheduled' => 'bg-secondary-fixed text-on-secondary-fixed-variant border border-secondary-fixed-dim',
                        'ended'     => 'bg-surface-container text-on-surface-variant border border-outline-variant/30',
                        default     => 'bg-surface-container text-on-surface-variant',
                    };
                    $statusIcon = match($exam->status->value) {
                        'active'    => 'play_circle',
                        'scheduled' => 'calendar_clock',
                        'ended'     => 'task_alt',
                        default     => 'help',
                    };
                    $statusLabel = match($exam->status->value) {
                        'active'    => 'نشط الآن',
                        'scheduled' => 'مجدول',
                        'ended'     => 'منتهٍ',
                        default     => $exam->status->value,
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold font-cairo {{ $statusClasses }}">
                    <span class="material-symbols-outlined text-[14px] icon-filled">{{ $statusIcon }}</span>
                    {{ $statusLabel }}
                </span>
            </div>
            <p class="text-on-surface-variant mt-1 text-sm font-cairo">
                {{ $exam->group->module->name ?? '—' }} · {{ $exam->group->name }} · {{ $exam->group->level->value }}
            </p>
        </div>

        {{-- Action buttons --}}
        <div class="flex flex-wrap gap-3 shrink-0">
            <a href="{{ route('teacher.exams.index') }}" wire:navigate
               class="px-4 py-2.5 rounded-xl border border-outline-variant/40 text-on-surface-variant font-semibold hover:bg-surface-container transition-colors flex items-center gap-2 font-cairo text-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                رجوع
            </a>

            @if ($exam->status->value === 'scheduled')
                <button wire:click="start"
                        wire:confirm="بدء الامتحان الآن؟ سيتم توزيع الأسئلة على جميع طلاب الفوج. لا يمكن التراجع عن هذا الإجراء."
                        class="btn-gradient text-on-primary px-6 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity font-cairo text-sm">
                    <span class="material-symbols-outlined text-[18px] icon-filled">play_circle</span>
                    بدء الامتحان
                </button>
            @elseif ($exam->status->value === 'active')
                <a href="{{ route('teacher.exams.monitor', $exam) }}" wire:navigate
                   class="flex items-center gap-2 bg-emerald-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:bg-emerald-700 transition-colors font-cairo text-sm shadow-ambient">
                    <span class="material-symbols-outlined text-[18px] icon-filled">monitor</span>
                    مراقبة الامتحان
                </a>
            @elseif ($exam->status->value === 'ended')
                <a href="{{ route('teacher.exams.results', $exam) }}" wire:navigate
                   class="btn-gradient text-on-primary px-6 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity font-cairo text-sm">
                    <span class="material-symbols-outlined text-[18px] icon-filled">bar_chart</span>
                    عرض النتائج
                </a>
            @endif
        </div>
    </div>

    {{-- Info cards grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient">
            <div class="text-xs text-on-surface-variant font-cairo mb-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                موعد الانطلاق
            </div>
            <div class="font-bold text-on-surface font-cairo text-sm">{{ $exam->scheduled_at->format('d/m/Y') }}</div>
            <div class="text-on-surface-variant font-cairo text-xs">{{ $exam->scheduled_at->format('H:i') }}</div>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient">
            <div class="text-xs text-on-surface-variant font-cairo mb-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">timer</span>
                المدة
            </div>
            <div class="font-black text-2xl text-primary font-cairo">{{ $exam->duration_minutes }}</div>
            <div class="text-on-surface-variant font-cairo text-xs">دقيقة</div>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient">
            <div class="text-xs text-on-surface-variant font-cairo mb-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">quiz</span>
                الأسئلة
            </div>
            <div class="font-black text-2xl text-primary font-cairo">{{ $exam->totalQuestions() }}</div>
            <div class="text-on-surface-variant font-cairo text-xs">سؤال إجمالاً</div>
        </div>
        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient">
            <div class="text-xs text-on-surface-variant font-cairo mb-1 flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">group</span>
                الطلاب
            </div>
            <div class="font-black text-2xl text-primary font-cairo">{{ $exam->group->students->count() }}</div>
            <div class="text-on-surface-variant font-cairo text-xs">في الفوج</div>
        </div>
    </div>

    {{-- Question breakdown --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
        <div class="bg-surface-container-low px-6 py-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px] icon-filled">quiz</span>
            <h2 class="font-bold text-on-surface font-cairo">توزيع الأسئلة</h2>
        </div>
        <div class="px-6 py-5 grid grid-cols-3 gap-4">
            <div class="text-center p-4 bg-emerald-50 rounded-xl border border-emerald-100">
                <div class="text-3xl font-black text-emerald-700 font-cairo">{{ $exam->easy_count }}</div>
                <div class="text-xs font-bold text-emerald-600 mt-1 font-cairo flex items-center justify-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    سهل
                </div>
            </div>
            <div class="text-center p-4 bg-amber-50 rounded-xl border border-amber-100">
                <div class="text-3xl font-black text-amber-700 font-cairo">{{ $exam->medium_count }}</div>
                <div class="text-xs font-bold text-amber-600 mt-1 font-cairo flex items-center justify-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                    متوسط
                </div>
            </div>
            <div class="text-center p-4 bg-red-50 rounded-xl border border-red-100">
                <div class="text-3xl font-black text-red-700 font-cairo">{{ $exam->hard_count }}</div>
                <div class="text-xs font-bold text-red-600 mt-1 font-cairo flex items-center justify-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                    صعب
                </div>
            </div>
        </div>
    </div>

    {{-- Start exam info panel (scheduled only) --}}
    @if ($exam->status->value === 'scheduled')
        <div class="bg-primary-fixed/30 border border-primary-fixed rounded-xl px-6 py-5 flex items-start gap-4">
            <span class="material-symbols-outlined text-primary text-[28px] shrink-0 mt-0.5 icon-filled">info</span>
            <div>
                <p class="font-bold text-on-primary-fixed-variant font-cairo mb-1">جاهز للبدء</p>
                <p class="text-sm text-on-primary-fixed-variant/80 font-cairo leading-relaxed">
                    عند الضغط على "بدء الامتحان"، سيُوزَّع مجموعة أسئلة مخصصة على كل طالب في الفوج.
                    ستُحال تلقائياً إلى صفحة المراقبة المباشرة.
                </p>
            </div>
        </div>
    @endif

    {{-- Sessions / Student roster --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
        <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
            <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px] icon-filled">people</span>
                @if ($sessions->isNotEmpty())
                    جلسات الطلاب
                @else
                    طلاب الفوج
                @endif
            </h2>
            <span class="text-sm text-on-surface-variant font-cairo bg-surface-container px-3 py-1 rounded-full">
                @if ($sessions->isNotEmpty())
                    {{ $sessions->count() }} جلسة
                @else
                    {{ $exam->group->students->count() }} طالب
                @endif
            </span>
        </div>

        @if ($sessions->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20">
                            <th scope="col" class="px-6 py-4 font-cairo text-start">الطالب</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الحالة</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">النتيجة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach ($sessions as $session)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-secondary-fixed rounded-full flex items-center justify-center text-on-secondary-fixed-variant text-sm font-bold font-cairo flex-shrink-0">
                                            {{ mb_substr($session->student->name ?? 'ط', 0, 1) }}
                                        </div>
                                        <span class="font-medium text-on-surface font-cairo text-sm">
                                            {{ $session->student->name ?? '—' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $sesStatusClass = match($session->status) {
                                            'active'    => 'bg-emerald-100 text-emerald-800',
                                            'completed' => 'bg-primary-fixed text-on-primary-fixed-variant',
                                            'waiting'   => 'bg-secondary-fixed text-on-secondary-fixed-variant',
                                            default     => 'bg-surface-container text-on-surface-variant',
                                        };
                                        $sesStatusLabel = match($session->status) {
                                            'active'    => 'يؤدي الامتحان',
                                            'completed' => 'مكتمل',
                                            'waiting'   => 'ينتظر',
                                            default     => $session->status,
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold font-cairo {{ $sesStatusClass }}">
                                        {{ $sesStatusLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-cairo text-sm">
                                    @if ($session->exam_score_component !== null)
                                        <span class="font-bold text-on-surface">{{ number_format($session->exam_score_component, 2) }}</span>
                                        <span class="text-on-surface-variant text-xs">/20</span>
                                    @else
                                        <span class="text-outline">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif ($exam->group->students->isNotEmpty())
            {{-- Show group students before exam starts --}}
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20">
                            <th scope="col" class="px-6 py-4 font-cairo text-start">الطالب</th>
                            <th scope="col" class="px-6 py-4 font-cairo">البريد الإلكتروني</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach ($exam->group->students as $student)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 bg-secondary-fixed rounded-full flex items-center justify-center text-on-secondary-fixed-variant text-sm font-bold font-cairo flex-shrink-0">
                                            {{ mb_substr($student->name, 0, 1) }}
                                        </div>
                                        <span class="font-medium text-on-surface font-cairo text-sm">{{ $student->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant font-cairo text-sm">
                                    {{ $student->email }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-14 text-center px-6">
                <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">group</span>
                <p class="text-on-surface-variant font-cairo text-sm">لا يوجد طلاب في هذا الفوج بعد</p>
                <a href="{{ route('teacher.groups.show', $exam->group) }}" wire:navigate
                   class="mt-4 text-primary hover:underline font-cairo text-sm font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">group_add</span>
                    إضافة طلاب للفوج
                </a>
            </div>
        @endif
    </div>

</div>
