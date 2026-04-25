<div class="p-6 lg:p-8 max-w-3xl mx-auto space-y-6">

    {{-- Score Hero Card --}}
    <div class="bg-primary-container text-on-primary-container rounded-xl p-8 text-center relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-xs uppercase tracking-widest opacity-70 mb-2 font-label">نتيجة امتحان</p>
            <h1 class="text-2xl font-headline font-bold mb-1">{{ $session->exam?->title ?? 'الامتحان' }}</h1>
            <p class="text-sm opacity-70 mb-7">{{ $session->exam?->group?->module?->name ?? '' }}</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white/10 rounded-xl p-4">
                    <p class="text-[10px] uppercase tracking-wider opacity-70 mb-1 font-label">الإجابات الصحيحة</p>
                    <p class="text-3xl font-headline font-extrabold">
                        {{ $rawCorrect }}<span class="text-lg opacity-60">/{{ $total }}</span>
                    </p>
                </div>
                <div class="bg-white/10 rounded-xl p-4 ring-2 ring-primary-fixed/40">
                    <p class="text-[10px] uppercase tracking-wider opacity-70 mb-1 font-label">الدرجة النهائية</p>
                    <p class="text-3xl font-headline font-extrabold">
                        {{ number_format($finalGrade, 2) }}<span class="text-lg opacity-60">/20</span>
                    </p>
                </div>
                <div class="bg-white/10 rounded-xl p-4">
                    <p class="text-[10px] uppercase tracking-wider opacity-70 mb-1 font-label">وقت الإكمال</p>
                    <p class="text-2xl font-headline font-bold">
                        {{ $session->completed_at?->format('H:i') ?? '—' }}
                    </p>
                </div>
            </div>

            <div class="mt-5">
                @if($finalGrade >= 10)
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-primary-fixed text-on-primary-fixed-variant rounded-full text-sm font-bold">
                        <span class="material-symbols-outlined text-[16px] icon-filled">workspace_premium</span>
                        ناجح
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-4 py-1.5 bg-error-container text-on-error-container rounded-full text-sm font-bold">
                        <span class="material-symbols-outlined text-[16px] icon-filled">cancel</span>
                        راسب
                    </span>
                @endif
            </div>
        </div>
        <div class="absolute -bottom-16 -left-16 w-48 h-48 bg-primary-fixed/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    {{-- Per-question Review --}}
    <div>
        <h2 class="text-xl font-headline font-bold text-primary mb-4">مراجعة الإجابات</h2>
        <div class="space-y-3">
            @foreach($review as $i => $item)
            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-5 border-r-4 {{ $item['is_correct'] ? 'border-primary-fixed' : 'border-error' }}">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <p class="text-xs text-on-surface-variant font-label">سؤال {{ $i + 1 }}</p>
                    @if($item['is_correct'])
                        <span class="flex items-center gap-1 text-xs font-bold text-on-primary-fixed-variant">
                            <span class="material-symbols-outlined text-[14px] icon-filled">check_circle</span>
                            صحيح
                        </span>
                    @else
                        <span class="flex items-center gap-1 text-xs font-bold text-error">
                            <span class="material-symbols-outlined text-[14px] icon-filled">cancel</span>
                            خطأ
                        </span>
                    @endif
                </div>
                <p class="font-bold text-on-surface text-sm mb-3 leading-relaxed">{{ $item['question_text'] }}</p>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2 px-3 py-2.5 rounded-lg
                        {{ $item['is_correct'] ? 'bg-primary-fixed/30 text-on-primary-fixed-variant' : 'bg-error-container/50 text-on-error-container' }}">
                        <span class="material-symbols-outlined text-[14px]">{{ $item['is_correct'] ? 'check' : 'close' }}</span>
                        إجابتك: <strong>{{ $item['selected_choice_text'] }}</strong>
                    </div>
                    @if(! $item['is_correct'])
                        <div class="flex items-center gap-2 px-3 py-2.5 rounded-lg bg-primary-fixed/30 text-on-primary-fixed-variant">
                            <span class="material-symbols-outlined text-[14px]">check</span>
                            الإجابة الصحيحة: <strong>{{ $item['correct_choice_text'] }}</strong>
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <div class="text-center pt-2 pb-4">
        <a href="{{ route('student.exams.index') }}" wire:navigate
           class="inline-flex items-center gap-2 px-6 py-3 bg-surface-container-high text-on-surface rounded-xl font-semibold text-sm hover:bg-surface-container-highest transition-all">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            العودة إلى امتحاناتي
        </a>
    </div>

</div>
