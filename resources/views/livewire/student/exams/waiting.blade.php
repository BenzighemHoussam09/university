<div class="p-6 lg:p-8 flex items-start justify-center min-h-[70vh]">
    <div class="w-full max-w-lg space-y-5">

        {{-- Exam Info Card --}}
        <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
            <div class="flex items-start gap-4 mb-5">
                <div class="w-11 h-11 bg-secondary-container text-on-secondary-container rounded-xl flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[22px] icon-filled">quiz</span>
                </div>
                <div class="min-w-0">
                    <h1 class="text-xl font-headline font-bold text-primary leading-tight">{{ $exam->title }}</h1>
                    <p class="text-sm text-on-surface-variant mt-0.5">{{ $exam->group?->module?->name ?? '—' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-3">
                <div class="bg-surface-container-low rounded-lg p-3 text-center">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px] block mb-1">calendar_today</span>
                    <p class="text-[10px] text-on-surface-variant uppercase tracking-wider mb-0.5">التاريخ</p>
                    <p class="text-xs font-semibold text-on-surface">{{ $exam->scheduled_at->format('d M Y') }}</p>
                </div>
                <div class="bg-surface-container-low rounded-lg p-3 text-center">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px] block mb-1">schedule</span>
                    <p class="text-[10px] text-on-surface-variant uppercase tracking-wider mb-0.5">الموعد</p>
                    <p class="text-xs font-semibold text-on-surface">{{ $exam->scheduled_at->format('H:i') }}</p>
                </div>
                <div class="bg-surface-container-low rounded-lg p-3 text-center">
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px] block mb-1">timer</span>
                    <p class="text-[10px] text-on-surface-variant uppercase tracking-wider mb-0.5">المدة</p>
                    <p class="text-xs font-semibold text-on-surface">{{ $exam->duration_minutes }} د</p>
                </div>
            </div>
        </div>

        @if($started)
            {{-- Exam Started --}}
            <div class="bg-primary-container text-on-primary-container rounded-xl p-8 text-center">
                <span class="material-symbols-outlined text-5xl mb-3 block text-on-primary-container icon-filled"
                     >play_circle</span>
                <h2 class="text-xl font-headline font-bold mb-2">بدأ الامتحان!</h2>
                <p class="text-sm opacity-80 mb-6">أنت يُعاد توجيهك إلى جلسة الامتحان...</p>
                <a href="{{ route('student.exams.session', $exam) }}" wire:navigate
                   class="inline-flex items-center gap-2 px-6 py-3 bg-primary-fixed text-on-primary-fixed-variant rounded-xl font-bold hover:opacity-90 transition-all active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[18px]">login</span>
                    دخول الامتحان الآن
                </a>
            </div>
        @else
            {{-- Waiting State --}}
            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-8 text-center">
                @if($secondsUntilScheduled > 0)
                    <p class="text-on-surface-variant text-sm mb-3 uppercase tracking-wider text-xs font-label">يبدأ الامتحان خلال</p>
                    <div class="text-5xl font-mono font-extrabold text-primary mb-5 tabular-nums"
                         x-data="{ seconds: {{ $secondsUntilScheduled }} }"
                         x-init="setInterval(() => { if (seconds > 0) seconds-- }, 1000)"
                         x-text="new Date(seconds * 1000).toISOString().substr(11, 8)"
                         aria-live="polite" aria-atomic="true" role="timer">
                        {{ gmdate('H:i:s', $secondsUntilScheduled) }}
                    </div>
                    <p class="text-xs text-on-surface-variant">
                        {{ $exam->scheduled_at->format('d M Y، H:i') }}
                    </p>
                @else
                    <span class="material-symbols-outlined text-5xl text-on-surface-variant/40 mb-3 block animate-pulse">hourglass_top</span>
                    <h2 class="text-lg font-headline font-bold text-on-surface mb-2">في انتظار الأستاذ...</h2>
                    <p class="text-sm text-on-surface-variant">يتحقق النظام من بدء الامتحان تلقائياً</p>
                @endif
            </div>

            <div class="flex items-center justify-center gap-2 text-xs text-on-surface-variant py-1">
                <span class="w-1.5 h-1.5 bg-primary rounded-full animate-pulse"></span>
                تحقق تلقائي كل 5 ثوانٍ
            </div>
        @endif

    </div>
</div>
