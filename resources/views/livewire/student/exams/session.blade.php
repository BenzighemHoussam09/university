@php
    $totalQuestions  = $assignedQuestions->count();
    $answeredCount   = count($draftSelections);
    $unansweredCount = $totalQuestions - $answeredCount;
    $progressPct     = $totalQuestions > 0 ? round($answeredCount / $totalQuestions * 100) : 0;
    $examTitle       = $session?->exam?->title ?? 'امتحان';
    $moduleName      = $session?->exam?->group?->module?->name ?? '';
    $studentName     = auth('student')->user()?->name ?? '';
    $studentInitial  = mb_substr($studentName, 0, 1);
@endphp

<div
    x-data="examSession({
        sessionId: @js($sessionId ?? 0),
        deadlineIso: @js($deadlineIso),
        wireId: '{{ $_instance->getId() }}'
    })"
    wire:poll.10s="heartbeat"
    class="flex flex-col h-screen h-dvh overflow-hidden"
>
    {{-- ========== HEADER ========== --}}
    <header class="bg-primary/95 backdrop-blur-xl flex-shrink-0 z-50 shadow-lg">
        <div class="flex items-center justify-between px-4 py-3 md:px-6 md:py-4">

            {{-- Exam info (RTL: right side) --}}
            <div class="flex items-center gap-4">
                <div class="h-8 w-px bg-primary-container/60"></div>
                <div>
                    <p class="text-on-primary font-headline font-bold text-base leading-tight">{{ $examTitle }}</p>
                    @if($moduleName)
                        <p class="text-primary-fixed-dim text-xs mt-0.5 opacity-80">{{ $moduleName }}</p>
                    @endif
                </div>
            </div>

            {{-- Timer (center) --}}
            <div class="bg-primary-container/40 px-3 py-2 md:px-5 md:py-2.5 rounded-xl border border-primary-fixed/20 flex items-center gap-2 md:gap-3">
                <span class="material-symbols-outlined text-primary-fixed text-lg md:text-xl">timer</span>
                <span
                    class="font-headline font-bold text-lg md:text-xl tracking-tight transition-colors duration-300"
                    :class="isTimeLow() ? 'text-red-300 animate-pulse' : 'text-primary-fixed'"
                    x-text="formatCountdown()"
                ></span>
            </div>

            {{-- Student info (RTL: left side) --}}
            <div class="flex items-center gap-3">
                <div class="text-right hidden sm:block">
                    <p class="text-on-primary font-bold text-sm leading-tight">{{ $studentName }}</p>
                    <p class="text-primary-fixed-dim text-xs opacity-70">طالب</p>
                </div>
                <div class="w-9 h-9 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container font-bold text-sm flex-shrink-0">
                    {{ $studentInitial }}
                </div>
            </div>
        </div>
    </header>

    {{-- Mobile-only: compact answered progress strip --}}
    <div class="md:hidden flex-shrink-0 bg-surface-container-low border-b border-outline-variant/20 px-4 py-2 flex items-center gap-3">
        <span class="text-xs font-bold text-on-surface-variant whitespace-nowrap">{{ $answeredCount }}/{{ $totalQuestions }}</span>
        <div class="flex-1 h-1.5 bg-surface-container-highest rounded-full overflow-hidden">
            <div class="h-full bg-primary rounded-full transition-all duration-500" style="width: {{ $progressPct }}%"></div>
        </div>
        <span class="text-xs text-on-surface-variant">{{ $progressPct }}%</span>
    </div>

    {{-- ========== BODY: Sidebar + Main ========== --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- SIDEBAR: Question Navigator (right side in RTL — first in DOM) --}}
        <aside class="hidden md:flex md:w-64 lg:w-72 bg-surface-container-low flex-shrink-0 flex-col py-6 px-4 overflow-y-auto">

            {{-- Progress header --}}
            <div class="mb-5">
                <h3 class="font-headline font-bold text-on-surface text-sm mb-1">متصفح الأسئلة</h3>
                <p class="text-on-surface-variant text-xs mb-3">
                    {{ $answeredCount }} من {{ $totalQuestions }} تمت الإجابة
                </p>
                <div class="h-1.5 bg-surface-container-highest rounded-full overflow-hidden">
                    <div
                        class="h-full bg-primary rounded-full transition-all duration-500"
                        style="width: {{ $progressPct }}%"
                    ></div>
                </div>
            </div>

            {{-- Question grid --}}
            <div class="grid grid-cols-5 gap-1.5 mb-6">
                @foreach($assignedQuestions as $item)
                    @php
                        $q          = $item['question'];
                        $order      = $item['display_order'];
                        $isAnswered = isset($draftSelections[$q->id]);
                    @endphp
                    <button
                        type="button"
                        onclick="document.getElementById('q-{{ $q->id }}').scrollIntoView({ behavior: 'smooth', block: 'center' })"
                        title="سؤال {{ $order }}"
                        class="aspect-square flex items-center justify-center rounded-lg text-xs font-bold transition-all duration-200
                            {{ $isAnswered
                                ? 'bg-primary text-on-primary shadow-sm'
                                : 'bg-surface-container-lowest border border-outline-variant/50 text-on-surface-variant hover:bg-surface-container hover:border-primary/40' }}"
                    >{{ $order }}</button>
                @endforeach
            </div>

            {{-- Legend --}}
            <div class="space-y-2 mb-6">
                <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                    <div class="w-3.5 h-3.5 rounded bg-primary flex-shrink-0"></div>
                    <span>تمت الإجابة</span>
                </div>
                <div class="flex items-center gap-2 text-xs text-on-surface-variant">
                    <div class="w-3.5 h-3.5 rounded border border-outline-variant/60 bg-surface-container-lowest flex-shrink-0"></div>
                    <span>لم يُجب بعد</span>
                </div>
            </div>

            {{-- Stats --}}
            <div class="mt-auto p-3 bg-surface-container rounded-xl">
                <div class="flex justify-between text-xs mb-1">
                    <span class="text-on-surface-variant">أُجيب</span>
                    <span class="font-bold text-primary">{{ $answeredCount }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-on-surface-variant">لم يُجب</span>
                    <span class="font-bold text-on-surface-variant">{{ $unansweredCount }}</span>
                </div>
            </div>
        </aside>

        {{-- MAIN CONTENT (scrollable) --}}
        <main data-scroll-preserve class="flex-1 overflow-y-auto bg-surface-container px-6 md:px-10 lg:px-14 py-6">

            {{-- Security notice (always visible) --}}
            <div class="mb-6 bg-surface-container-lowest border border-outline-variant/30 rounded-xl p-4 flex items-start gap-3 max-w-3xl mx-auto shadow-ambient">
                <div class="bg-primary-fixed/40 p-2 rounded-lg flex-shrink-0 mt-0.5">
                    <span class="material-symbols-outlined text-primary text-[18px] icon-filled">security</span>
                </div>
                <div>
                    <p class="text-on-surface font-bold text-sm">وضع الامتحان الآمن نشط</p>
                    <p class="text-on-surface-variant text-xs mt-0.5 leading-relaxed">جلستك تحت المراقبة. أي تنقل خارج هذه النافذة سيُسجَّل ويُبلَّغ عنه.</p>
                </div>
            </div>

            {{-- Incident warning (server-rendered, updates on morph) --}}
            @if($incidentCount > 0)
                <div class="mb-6 bg-error-container border border-error/20 rounded-xl p-4 flex items-center gap-3 max-w-3xl mx-auto">
                    <span class="material-symbols-outlined text-error flex-shrink-0 icon-filled">warning</span>
                    <p class="text-on-error-container font-bold text-sm">
                        تم تسجيل {{ $incidentCount }} انتهاك(ات) من قواعد الامتحان. يُرجى البقاء في هذه الصفحة.
                    </p>
                </div>
            @endif

            {{-- Question cards --}}
            @foreach($assignedQuestions as $item)
                @php
                    $question        = $item['question'];
                    $choices         = $item['choices'];
                    $displayOrder    = $item['display_order'];
                    $selectedChoiceId = $draftSelections[$question->id] ?? null;
                @endphp

                <div
                    wire:key="question-{{ $question->id }}"
                    class="mb-5 bg-surface-container-lowest rounded-xl shadow-ambient p-6 md:p-8 max-w-3xl mx-auto transition-all duration-200
                        {{ $selectedChoiceId ? 'border-t-4 border-primary' : 'border-t-4 border-outline-variant/30' }}"
                    id="q-{{ $question->id }}"
                >
                    {{-- Question header --}}
                    <div class="mb-6">
                        <span class="text-primary font-headline font-bold text-xs uppercase tracking-widest opacity-60">
                            سؤال {{ $displayOrder }} / {{ $totalQuestions }}
                        </span>
                        <h2 class="text-on-surface font-bold text-lg leading-relaxed mt-2">
                            {{ $question->text }}
                        </h2>
                    </div>

                    {{-- Choices --}}
                    <div class="space-y-3">
                        @foreach($choices as $choice)
                            @php $isSelected = $selectedChoiceId === $choice->id; @endphp
                            <label
                                class="group flex items-center gap-4 p-4 rounded-xl cursor-pointer transition-all duration-150
                                    has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-primary has-[:focus-visible]:ring-offset-1
                                    {{ $isSelected
                                        ? 'border-2 border-primary bg-primary-fixed/20 shadow-sm'
                                        : 'border border-outline-variant/30 bg-surface-container-low hover:bg-surface-container hover:border-primary/30' }}"
                            >
                                {{-- Custom radio circle --}}
                                <div class="w-5 h-5 rounded-full flex-shrink-0 border-2 flex items-center justify-center transition-all duration-150
                                    {{ $isSelected
                                        ? 'border-primary bg-primary'
                                        : 'border-outline-variant group-hover:border-primary' }}">
                                    @if($isSelected)
                                        <div class="w-2 h-2 rounded-full bg-on-primary"></div>
                                    @endif
                                </div>

                                {{-- Hidden native radio --}}
                                <input
                                    type="radio"
                                    name="q{{ $question->id }}"
                                    value="{{ $choice->id }}"
                                    {{ $isSelected ? 'checked' : '' }}
                                    x-on:change="saveDraft({{ $question->id }}, {{ $choice->id }})"
                                    class="sr-only"
                                    aria-label="{{ $choice->text }}"
                                >

                                <span class="leading-relaxed text-base transition-colors
                                    {{ $isSelected ? 'text-on-primary-fixed-variant font-bold' : 'text-on-surface-variant font-medium' }}">
                                    {{ $choice->text }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="h-4"></div>
        </main>
    </div>

    {{-- ========== FOOTER ========== --}}
    <footer class="bg-surface-container-lowest/90 backdrop-blur-md border-t border-outline-variant/20 flex-shrink-0 px-6 py-4 flex justify-between items-center shadow-[0_-8px_24px_rgba(25,28,29,0.06)]">

        {{-- Auto-save & offline indicators --}}
        <div class="flex items-center gap-4">

            {{-- Save status pill --}}
            <div class="flex items-center gap-2.5 px-4 py-2 rounded-full bg-surface-container border border-outline-variant/30 transition-all duration-300">
                {{-- Pulsing dot (saved) --}}
                <div class="relative flex items-center justify-center" x-show="!isSaving" x-cloak>
                    <span class="absolute inline-flex h-2.5 w-2.5 rounded-full bg-green-400 opacity-75 animate-ping"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                </div>
                {{-- Spinner (saving) --}}
                <div x-show="isSaving" x-cloak class="flex-shrink-0">
                    <svg class="animate-spin h-4 w-4 text-primary" viewBox="0 0 24 24" fill="none">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                <div class="flex items-center gap-1.5 text-on-surface-variant font-bold text-sm">
                    <span class="material-symbols-outlined text-[16px] icon-filled">cloud_done</span>
                    <span class="hidden sm:inline" x-show="isSaving" x-cloak>جارٍ الحفظ...</span>
                    <span class="hidden sm:inline" x-show="!isSaving" x-cloak>
                        <span x-show="lastSavedAt !== null" x-cloak x-text="'تم الحفظ ' + lastSavedAt"></span>
                        <span x-show="lastSavedAt === null" x-cloak>الحفظ التلقائي نشط</span>
                    </span>
                </div>
            </div>

            {{-- Offline pending indicator --}}
            <div x-show="pendingCount > 0" x-cloak class="flex items-center gap-1.5 text-xs text-on-surface-variant">
                <span class="material-symbols-outlined text-[16px] text-tertiary">wifi_off</span>
                <span x-text="pendingCount + ' إجابة في انتظار الإرسال'"></span>
            </div>
        </div>

        {{-- Submit button --}}
        <button
            type="button"
            @click="showSubmitModal = true"
            class="bg-primary text-on-primary rounded-xl px-7 py-3 font-bold shadow-lg flex items-center gap-2.5 hover:opacity-90 active:scale-[0.97] transition-all"
        >
            <span class="material-symbols-outlined text-xl">send</span>
            تسليم الامتحان
        </button>
    </footer>

    {{-- ========== FULLSCREEN GATE ========== --}}
    {{-- Covers everything. Only dismissible by clicking the button (= user gesture = requestFullscreen succeeds). --}}
    <div
        x-show="!isFullscreen"
        x-cloak
        style="pointer-events: all;"
        class="fixed inset-0 z-[500] bg-primary flex flex-col items-center justify-center text-center px-6 select-none"
        @keydown.window.prevent
    >
        <span class="material-symbols-outlined text-on-primary text-7xl mb-6">fullscreen</span>
        <h2 class="text-on-primary font-headline font-extrabold text-3xl mb-3">
            جلسة الامتحان متوقفة
        </h2>
        <p class="text-primary-fixed-dim text-base mb-2 max-w-sm leading-relaxed">
            خرجت من وضع الشاشة الكاملة. لا يمكنك متابعة الامتحان خارج هذا الوضع.
        </p>
        <p class="text-primary-fixed-dim/60 text-sm mb-10 max-w-sm">
            اضغط على الزر للعودة. لا يوجد خيار آخر.
        </p>
        <button
            @click="requestFullscreen()"
            class="bg-on-primary text-primary font-bold px-10 py-4 rounded-2xl text-xl shadow-2xl hover:opacity-90 active:scale-95 transition-all cursor-pointer"
        >
            العودة إلى وضع الامتحان
        </button>
    </div>

    {{-- ========== SUBMIT CONFIRMATION MODAL ========== --}}
    <div
        x-show="showSubmitModal"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[200] flex items-center justify-center bg-inverse-surface/30 backdrop-blur-sm px-4"
        @keydown.escape.window="showSubmitModal = false"
    >
        <div
            x-show="showSubmitModal"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.stop
            class="bg-surface-container-lowest rounded-2xl shadow-ambient p-8 max-w-sm w-full"
        >
            <div class="text-center mb-6">
                <div class="w-14 h-14 rounded-full bg-primary-fixed mx-auto flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-primary text-2xl icon-filled">assignment_turned_in</span>
                </div>
                <h3 class="font-headline font-bold text-xl text-on-surface mb-2">تسليم الامتحان</h3>

                <p class="text-on-surface-variant text-sm leading-relaxed mb-2">
                    أجبت على
                    <strong class="text-primary">{{ $answeredCount }}</strong>
                    من أصل
                    <strong class="text-on-surface">{{ $totalQuestions }}</strong>
                    سؤال.
                </p>

                @if($unansweredCount > 0)
                    <div class="flex items-center justify-center gap-2 text-sm text-error mb-3">
                        <span class="material-symbols-outlined text-[16px]">warning</span>
                        <span>{{ $unansweredCount }} سؤال(ات) لم تُجب عليها.</span>
                    </div>
                @else
                    <div class="flex items-center justify-center gap-2 text-sm text-green-600 mb-3">
                        <span class="material-symbols-outlined text-[16px]">check_circle</span>
                        <span>أجبت على جميع الأسئلة.</span>
                    </div>
                @endif

                <p class="text-on-surface-variant text-xs">هذا الإجراء نهائي ولا يمكن التراجع عنه.</p>
            </div>

            <div class="flex gap-3">
                <button
                    type="button"
                    @click="showSubmitModal = false"
                    class="flex-1 py-3 rounded-xl border border-outline-variant text-on-surface-variant font-bold text-sm hover:bg-surface-container transition-colors"
                >
                    مراجعة
                </button>
                <button
                    type="button"
                    wire:click="submitFinal"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-not-allowed"
                    @click="showSubmitModal = false"
                    class="flex-1 py-3 rounded-xl bg-primary text-on-primary font-bold text-sm hover:opacity-90 transition-opacity flex items-center justify-center gap-2"
                >
                    <span wire:loading.remove wire:target="submitFinal">تأكيد التسليم</span>
                    <span wire:loading wire:target="submitFinal" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        جارٍ...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>
