<div class="p-6 lg:p-8 space-y-8">

    {{-- Greeting --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">
                أهلاً، {{ auth('teacher')->user()?->name }}
            </h1>
            <p class="text-on-surface-variant mt-1">
                مرحباً بك في لوحة التحكم. لديك {{ $examCount }} امتحان مسجّل.
            </p>
        </div>
        <a href="{{ route('teacher.exams.create') }}"
           class="btn-gradient text-on-primary px-5 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity self-start sm:self-auto">
            <span class="material-symbols-outlined text-[20px] icon-filled">add</span>
            إنشاء امتحان
        </a>
    </div>

    {{-- Stats grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient hover:bg-surface-container transition-colors">
            <div class="flex justify-between items-start mb-3">
                <div class="p-2.5 bg-primary-fixed rounded-lg text-on-primary-fixed-variant">
                    <span class="material-symbols-outlined text-[20px]">groups</span>
                </div>
                <span class="text-[10px] font-bold text-on-surface-variant tracking-wider uppercase">الطلاب</span>
            </div>
            <div class="text-3xl font-bold font-headline text-primary">{{ $studentCount }}</div>
            <div class="text-xs text-on-surface-variant mt-1">مسجّل</div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient hover:bg-surface-container transition-colors">
            <div class="flex justify-between items-start mb-3">
                <div class="p-2.5 bg-secondary-fixed rounded-lg text-on-secondary-fixed-variant">
                    <span class="material-symbols-outlined text-[20px]">class</span>
                </div>
                <span class="text-[10px] font-bold text-on-surface-variant tracking-wider uppercase">المجموعات</span>
            </div>
            <div class="text-3xl font-bold font-headline text-primary">{{ $groupCount }}</div>
            <div class="text-xs text-on-surface-variant mt-1">فوج</div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient hover:bg-surface-container transition-colors">
            <div class="flex justify-between items-start mb-3">
                <div class="p-2.5 bg-tertiary-fixed rounded-lg text-on-tertiary-fixed-variant">
                    <span class="material-symbols-outlined text-[20px]">quiz</span>
                </div>
                <span class="text-[10px] font-bold text-on-surface-variant tracking-wider uppercase">الأسئلة</span>
            </div>
            <div class="text-3xl font-bold font-headline text-primary">{{ $questionCount }}</div>
            <div class="text-xs text-on-surface-variant mt-1">سؤال في البنك</div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient hover:bg-surface-container transition-colors border-r-4 border-surface-tint">
            <div class="flex justify-between items-start mb-3">
                <div class="p-2.5 bg-primary-fixed rounded-lg text-on-primary-fixed-variant">
                    <span class="material-symbols-outlined text-[20px]">description</span>
                </div>
                <span class="text-[10px] font-bold text-on-surface-variant tracking-wider uppercase">الامتحانات</span>
            </div>
            <div class="text-3xl font-bold font-headline text-primary">{{ $examCount }}</div>
            <div class="text-xs text-on-surface-variant mt-1">إجمالي</div>
        </div>

    </div>

    {{-- Main 2/3 + 1/3 layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- col-span-2: Upcoming Exams + Recent Results --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Upcoming Exams --}}
            <div class="bg-surface-container-low rounded-xl p-6 shadow-ambient">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-primary font-headline">الامتحانات القادمة</h2>
                    <a href="{{ route('teacher.exams.index') }}"
                       class="text-sm font-semibold text-on-primary-fixed-variant hover:underline">
                        عرض الكل
                    </a>
                </div>

                @if ($upcomingExams->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">event_busy</span>
                        <p class="text-on-surface-variant text-sm">لا توجد امتحانات قادمة.</p>
                        <a href="{{ route('teacher.exams.create') }}"
                           class="mt-3 text-sm font-semibold text-primary hover:underline">
                            إنشاء امتحان جديد
                        </a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20">
                                    <th scope="col" class="pb-3 text-start font-semibold">الامتحان</th>
                                    <th scope="col" class="pb-3 text-start font-semibold">الفوج</th>
                                    <th scope="col" class="pb-3 text-start font-semibold">الموعد</th>
                                    <th scope="col" class="pb-3 text-start font-semibold">الحالة</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-outline-variant/10">
                                @foreach ($upcomingExams as $exam)
                                    <tr class="hover:bg-surface-container-lowest transition-colors">
                                        <td class="py-3 font-semibold text-primary">
                                            <a href="{{ route('teacher.exams.show', $exam) }}" class="hover:underline">
                                                {{ $exam->title }}
                                            </a>
                                        </td>
                                        <td class="py-3 text-on-surface-variant text-xs">
                                            {{ $exam->group?->name ?? '—' }}
                                            @if ($exam->group?->module)
                                                <br><span class="text-outline">{{ $exam->group->module->name }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 text-on-surface-variant">
                                            {{ $exam->scheduled_at?->format('d/m/Y H:i') ?? '—' }}
                                        </td>
                                        <td class="py-3">
                                            @if ($exam->status->value === 'active')
                                                <span class="bg-primary/10 text-primary px-2.5 py-1 rounded-full text-xs font-bold">نشط</span>
                                            @else
                                                <span class="bg-secondary-fixed text-on-secondary-fixed-variant px-2.5 py-1 rounded-full text-xs font-bold">مجدول</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Recent Ended Exams --}}
            <div class="bg-surface-container-low rounded-xl p-6 shadow-ambient">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-primary font-headline">آخر النتائج</h2>
                    <a href="{{ route('teacher.exams.index') }}"
                       class="text-sm font-semibold text-on-primary-fixed-variant hover:underline">
                        جميع النتائج
                    </a>
                </div>

                @if ($recentEndedExams->isEmpty())
                    <div class="flex flex-col items-center justify-center py-10 text-center">
                        <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">analytics</span>
                        <p class="text-on-surface-variant text-sm">لا توجد نتائج منشورة بعد.</p>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach ($recentEndedExams as $exam)
                            <div class="bg-surface-container-lowest p-4 rounded-lg border-r-4 border-surface-tint">
                                <h3 class="font-bold text-primary text-sm mb-1">{{ $exam->title }}</h3>
                                <div class="text-xs text-on-surface-variant">
                                    {{ $exam->group?->name ?? '—' }}
                                    @if ($exam->group?->module)
                                        &mdash; {{ $exam->group->module->name }}
                                    @endif
                                </div>
                                <div class="text-xs text-outline mt-2">
                                    انتهى: {{ $exam->ended_at?->format('d/m/Y') ?? '—' }}
                                </div>
                                <a href="{{ route('teacher.exams.results', $exam) }}"
                                   class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-primary hover:underline">
                                    <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                    عرض النتائج
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        {{-- col-span-1: Recent Notifications --}}
        <div class="lg:col-span-1">
            <div class="bg-surface-container-low rounded-xl p-6 shadow-ambient h-full flex flex-col">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-primary font-headline">التنبيهات الأخيرة</h2>
                    <a href="{{ route('teacher.notifications') }}"
                       class="text-on-surface-variant hover:text-primary transition-colors">
                        <span class="material-symbols-outlined text-[20px]">more_horiz</span>
                    </a>
                </div>

                @if ($recentNotifications->isEmpty())
                    <div class="flex flex-col items-center justify-center flex-1 py-10 text-center">
                        <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">notifications_none</span>
                        <p class="text-on-surface-variant text-sm">لا توجد تنبيهات.</p>
                    </div>
                @else
                    <div class="space-y-2 flex-1">
                        @foreach ($recentNotifications as $notification)
                            @php
                                $iconMap = [
                                    'student_account_created' => ['icon' => 'person_add',            'bg' => 'bg-secondary-fixed',  'fg' => 'text-on-secondary-fixed-variant'],
                                    'exam_reminder'           => ['icon' => 'schedule',               'bg' => 'bg-primary-fixed',    'fg' => 'text-on-primary-fixed-variant'],
                                    'results_available'       => ['icon' => 'assignment_turned_in',   'bg' => 'bg-tertiary-fixed',   'fg' => 'text-on-tertiary-fixed-variant'],
                                ];
                                $style = $iconMap[$notification->kind] ?? ['icon' => 'notifications', 'bg' => 'bg-surface-container', 'fg' => 'text-on-surface-variant'];

                                $titleMap = [
                                    'student_account_created' => 'طالب جديد مضاف',
                                    'exam_reminder'           => 'تذكير امتحان',
                                    'results_available'       => 'نتائج متاحة',
                                ];
                                $title = $titleMap[$notification->kind] ?? $notification->kind;
                            @endphp
                            <div class="flex gap-3 p-3 hover:bg-surface-container-lowest rounded-lg transition-colors
                                        {{ $notification->isUnread() ? 'border-r-2 border-primary' : '' }}">
                                <div class="h-9 w-9 rounded-full {{ $style['bg'] }} flex items-center justify-center flex-shrink-0 {{ $style['fg'] }}">
                                    <span class="material-symbols-outlined text-[17px] icon-filled"
                                         >{{ $style['icon'] }}</span>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-semibold text-sm text-on-surface leading-tight">{{ $title }}</p>
                                    @if (!empty($notification->payload['exam_title']))
                                        <p class="text-xs text-on-surface-variant truncate">{{ $notification->payload['exam_title'] }}</p>
                                    @endif
                                    <span class="text-[10px] font-bold text-outline uppercase">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <a href="{{ route('teacher.notifications') }}"
                   class="mt-6 py-2.5 block text-center border border-outline-variant/30 rounded-lg text-primary text-sm font-semibold hover:bg-primary-fixed transition-colors">
                    عرض كل التنبيهات
                </a>
            </div>
        </div>

    </div>

</div>
