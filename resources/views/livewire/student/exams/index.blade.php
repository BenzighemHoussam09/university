<div class="p-6 lg:p-8"
     x-data="{ tab: '{{ $upcoming->isEmpty() && $past->isNotEmpty() ? 'past' : 'upcoming' }}' }">

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-headline font-bold text-primary">امتحاناتي</h1>
            <p class="text-sm text-on-surface-variant mt-0.5">جميع امتحاناتك في مكان واحد</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 p-1 bg-surface-container-high rounded-xl w-fit mb-6">
        <button @click="tab = 'upcoming'"
                :class="tab === 'upcoming' ? 'bg-surface-container-lowest text-on-surface shadow-sm' : 'text-on-surface-variant hover:text-on-surface'"
                class="px-5 py-2 rounded-lg text-sm font-semibold transition-all flex items-center gap-1.5">
            القادمة والنشطة
            @if($upcoming->isNotEmpty())
                <span class="px-1.5 py-0.5 bg-primary text-on-primary text-[10px] font-bold rounded-full leading-none">{{ $upcoming->count() }}</span>
            @endif
        </button>
        <button @click="tab = 'past'"
                :class="tab === 'past' ? 'bg-surface-container-lowest text-on-surface shadow-sm' : 'text-on-surface-variant hover:text-on-surface'"
                class="px-5 py-2 rounded-lg text-sm font-semibold transition-all flex items-center gap-1.5">
            المكتملة
            @if($past->isNotEmpty())
                <span class="px-1.5 py-0.5 bg-surface-container text-on-surface-variant text-[10px] font-bold rounded-full leading-none">{{ $past->count() }}</span>
            @endif
        </button>
    </div>

    {{-- Upcoming / Active Tab --}}
    <div x-show="tab === 'upcoming'" x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        @if($upcoming->isNotEmpty())
            <div class="space-y-3">
                @foreach($upcoming as $exam)
                <div class="bg-surface-container-lowest rounded-xl shadow-sm p-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4 min-w-0">
                        <div class="w-10 h-10 rounded-xl flex-shrink-0 flex items-center justify-center
                            {{ $exam->status->value === 'active' ? 'bg-primary text-on-primary' : 'bg-secondary-container text-on-secondary-container' }}">
                            <span class="material-symbols-outlined text-[20px] icon-filled">
                                {{ $exam->status->value === 'active' ? 'play_circle' : 'event' }}
                            </span>
                        </div>
                        <div class="min-w-0">
                            <p class="font-bold text-on-surface truncate">{{ $exam->title }}</p>
                            <p class="text-sm text-on-surface-variant">
                                {{ $exam->group?->module?->name ?? '—' }}
                                · {{ $exam->scheduled_at->format('d M Y، H:i') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        @if($exam->status->value === 'active')
                            <span class="hidden sm:block px-2.5 py-1 bg-primary text-on-primary text-xs font-bold rounded-full">نشط الآن</span>
                            <a href="{{ route('student.exams.session', $exam) }}" wire:navigate
                               class="px-4 py-2 btn-gradient text-on-primary rounded-lg text-sm font-bold hover:opacity-90 transition-all active:scale-[0.98] flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">login</span>
                                دخول الامتحان
                            </a>
                        @else
                            <span class="hidden sm:block px-2.5 py-1 bg-secondary-container text-on-secondary-container text-xs font-bold rounded-full">مجدول</span>
                            <a href="{{ route('student.exams.waiting', $exam) }}" wire:navigate
                               class="px-4 py-2 bg-surface-container text-on-surface rounded-lg text-sm font-semibold hover:bg-surface-container-high transition-all flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">hourglass_top</span>
                                غرفة الانتظار
                            </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="bg-surface-container-lowest rounded-xl p-16 text-center shadow-sm">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant/40 mb-3 block">event_available</span>
                <p class="text-on-surface-variant font-semibold">لا توجد امتحانات قادمة</p>
                <p class="text-xs text-on-surface-variant/60 mt-1">ستُضاف امتحاناتك هنا عند جدولتها من قِبَل أستاذك</p>
            </div>
        @endif
    </div>

    {{-- Past Tab --}}
    <div x-show="tab === 'past'" x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0">
        @if($past->isNotEmpty())
            <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
                <table class="w-full text-right">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant text-xs uppercase tracking-wider font-label">
                            <th scope="col" class="px-5 py-3.5 font-semibold">الامتحان</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold hidden sm:table-cell">تاريخ الانتهاء</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold text-left">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        @foreach($past as $exam)
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-5 py-4">
                                <p class="font-bold text-on-surface">{{ $exam->title }}</p>
                                <p class="text-xs text-on-surface-variant mt-0.5">{{ $exam->group?->module?->name ?? '—' }}</p>
                            </td>
                            <td class="px-5 py-4 text-on-surface-variant text-sm hidden sm:table-cell">
                                {{ $exam->ended_at?->diffForHumans() ?? '—' }}
                            </td>
                            <td class="px-5 py-4 text-left">
                                <a href="{{ route('student.exams.results', $exam) }}" wire:navigate
                                   class="inline-flex items-center gap-1 px-3.5 py-1.5 bg-surface-container text-on-surface rounded-lg text-xs font-semibold hover:bg-surface-container-high transition-all">
                                    <span class="material-symbols-outlined text-[14px]">bar_chart</span>
                                    النتائج
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-surface-container-lowest rounded-xl p-16 text-center shadow-sm">
                <span class="material-symbols-outlined text-5xl text-on-surface-variant/40 mb-3 block">history_edu</span>
                <p class="text-on-surface-variant font-semibold">لا توجد امتحانات مكتملة</p>
                <p class="text-xs text-on-surface-variant/60 mt-1">ستظهر نتائجك هنا بعد إكمال امتحاناتك</p>
            </div>
        @endif
    </div>

</div>
