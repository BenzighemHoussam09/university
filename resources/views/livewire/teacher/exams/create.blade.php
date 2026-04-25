<div class="p-6 lg:p-8"
     x-data="{
         get total() { return (parseInt($wire.easyCount) || 0) + (parseInt($wire.mediumCount) || 0) + (parseInt($wire.hardCount) || 0) }
     }">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant mb-2 font-cairo">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <a href="{{ route('teacher.exams.index') }}" wire:navigate class="hover:text-primary transition-colors">الامتحانات</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <span class="text-on-surface font-semibold">إنشاء امتحان</span>
            </nav>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">إنشاء امتحان جديد</h1>
            <p class="text-on-surface-variant mt-1 text-sm font-cairo">حدد الفوج والأسئلة وموعد الامتحان</p>
        </div>
        <div class="flex flex-wrap gap-3 shrink-0">
            <a href="{{ route('teacher.exams.index') }}" wire:navigate
               class="px-5 py-2.5 rounded-xl border border-outline-variant/40 text-on-surface-variant font-semibold hover:bg-surface-container transition-colors flex items-center gap-2 font-cairo text-sm">
                <span class="material-symbols-outlined text-[18px]">close</span>
                إلغاء
            </a>
            <button wire:click="save"
                    class="btn-gradient text-on-primary px-6 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity font-cairo text-sm">
                <span class="material-symbols-outlined text-[18px] icon-filled">save</span>
                إنشاء الامتحان
            </button>
        </div>
    </div>

    {{-- Bank errors (top-level) --}}
    @if ($errors->has('bank.easy') || $errors->has('bank.medium') || $errors->has('bank.hard'))
        <div class="mb-6 bg-error-container/30 border border-error/20 rounded-xl px-5 py-4 space-y-1.5">
            <div class="flex items-center gap-2 text-error font-bold font-cairo text-sm mb-2">
                <span class="material-symbols-outlined text-[18px]">warning</span>
                بنك الأسئلة غير كافٍ
            </div>
            @error('bank.easy')
                <p class="text-error text-xs font-cairo flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ $message }}
                </p>
            @enderror
            @error('bank.medium')
                <p class="text-error text-xs font-cairo flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ $message }}
                </p>
            @enderror
            @error('bank.hard')
                <p class="text-error text-xs font-cairo flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>
    @endif

    {{-- Form Body: 2-col layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- Left column: main fields --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Exam title --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-ambient border-r-4 border-primary overflow-hidden">
                <div class="px-6 pt-6 pb-2">
                    <label for="title" class="flex items-center gap-2 text-sm font-bold text-primary mb-3 font-cairo">
                        <span class="material-symbols-outlined text-[20px]">title</span>
                        عنوان الامتحان
                    </label>
                    <input wire:model="title"
                           id="title"
                           type="text"
                           placeholder="مثال: امتحان الفصل الأول — الرياضيات"
                           class="w-full bg-surface-container-low border-2 border-transparent focus:border-primary/20 rounded-xl px-4 py-3 text-base focus:ring-0 transition-all placeholder:text-outline font-cairo outline-none"/>
                </div>
                @error('title')
                    <div class="px-6 pb-4">
                        <p class="text-xs text-error font-cairo flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    </div>
                @enderror
                @if (!$errors->has('title')) <div class="h-4"></div> @endif
            </section>

            {{-- Group selection --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
                <div class="bg-surface-container-low px-6 py-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px] icon-filled">groups</span>
                    <h3 class="font-bold text-on-surface font-cairo">الفوج المستهدف</h3>
                </div>
                <div class="px-6 py-5 space-y-4">
                    {{-- Module filter --}}
                    <div>
                        <label class="block text-xs font-bold text-on-surface-variant mb-2 font-cairo">تصفية حسب المقياس <span class="font-normal text-outline">(اختياري)</span></label>
                        <div class="relative">
                            <select wire:model.live="moduleId"
                                    class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 appearance-none focus:ring-2 focus:ring-surface-tint/30 transition-all font-cairo outline-none text-sm">
                                <option value="">كل المقاييس</option>
                                @foreach ($modules as $module)
                                    <option value="{{ $module->id }}">{{ $module->name }}</option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[18px]">keyboard_arrow_down</span>
                        </div>
                    </div>
                    {{-- Group select --}}
                    <div>
                        <label for="groupId" class="block text-xs font-bold text-on-surface-variant mb-2 font-cairo">الفوج</label>
                        <div class="relative">
                            <select wire:model.live="groupId"
                                    id="groupId"
                                    class="w-full bg-surface-container-lowest border-2 border-outline-variant/20 rounded-xl px-4 py-3 appearance-none focus:ring-2 focus:ring-primary/20 focus:border-primary/30 shadow-sm cursor-pointer outline-none transition-all font-cairo text-sm">
                                <option value="">— اختر الفوج —</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">
                                        {{ $group->module->name }} · {{ $group->name }} ({{ $group->level->value }})
                                    </option>
                                @endforeach
                            </select>
                            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[18px]">keyboard_arrow_down</span>
                        </div>
                        @error('groupId')
                            <p class="text-xs text-error mt-1.5 font-cairo flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                        @if ($groups->isEmpty())
                            <p class="text-xs text-on-surface-variant mt-1.5 font-cairo flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">info</span>
                                @if ($moduleId)
                                    لا توجد أفواج لهذا المقياس. <a href="{{ route('teacher.groups.index') }}" wire:navigate class="text-primary underline">إنشاء فوج</a>
                                @else
                                    لا توجد أفواج. <a href="{{ route('teacher.groups.index') }}" wire:navigate class="text-primary underline">إنشاء فوج أولاً</a>
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Question distribution --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
                <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px] icon-filled">quiz</span>
                        <h3 class="font-bold text-on-surface font-cairo">توزيع الأسئلة</h3>
                    </div>
                    <div class="flex items-center gap-2 font-cairo text-sm">
                        <span class="text-on-surface-variant">الإجمالي:</span>
                        <span class="font-black text-lg text-primary" x-text="total"></span>
                        <span class="text-on-surface-variant">سؤال</span>
                    </div>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-3 gap-4 mb-4">
                        {{-- Easy --}}
                        <div class="bg-emerald-50 border-2 border-emerald-100 rounded-xl p-4">
                            <label for="easyCount" class="flex items-center gap-2 text-xs font-bold text-emerald-800 mb-1 font-cairo">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                                سهل
                            </label>
                            @if ($groupId)
                                <p class="text-[10px] text-emerald-600 font-cairo mb-2">{{ $bankCounts['easy'] }} متاح في البنك</p>
                            @else
                                <div class="mb-2 h-4"></div>
                            @endif
                            <input wire:model.live="easyCount"
                                   id="easyCount"
                                   type="number"
                                   min="0"
                                   class="w-full bg-white border-2 border-emerald-200 focus:border-emerald-400 rounded-xl px-3 py-2.5 text-center text-xl font-black text-emerald-800 focus:ring-0 outline-none transition-all font-cairo"/>
                            @error('easyCount')
                                <p class="text-xs text-error mt-1.5 font-cairo">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Medium --}}
                        <div class="bg-amber-50 border-2 border-amber-100 rounded-xl p-4">
                            <label for="mediumCount" class="flex items-center gap-2 text-xs font-bold text-amber-800 mb-1 font-cairo">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span>
                                متوسط
                            </label>
                            @if ($groupId)
                                <p class="text-[10px] text-amber-600 font-cairo mb-2">{{ $bankCounts['medium'] }} متاح في البنك</p>
                            @else
                                <div class="mb-2 h-4"></div>
                            @endif
                            <input wire:model.live="mediumCount"
                                   id="mediumCount"
                                   type="number"
                                   min="0"
                                   class="w-full bg-white border-2 border-amber-200 focus:border-amber-400 rounded-xl px-3 py-2.5 text-center text-xl font-black text-amber-800 focus:ring-0 outline-none transition-all font-cairo"/>
                            @error('mediumCount')
                                <p class="text-xs text-error mt-1.5 font-cairo">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Hard --}}
                        <div class="bg-red-50 border-2 border-red-100 rounded-xl p-4">
                            <label for="hardCount" class="flex items-center gap-2 text-xs font-bold text-red-800 mb-1 font-cairo">
                                <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span>
                                صعب
                            </label>
                            @if ($groupId)
                                <p class="text-[10px] text-red-500 font-cairo mb-2">{{ $bankCounts['hard'] }} متاح في البنك</p>
                            @else
                                <div class="mb-2 h-4"></div>
                            @endif
                            <input wire:model.live="hardCount"
                                   id="hardCount"
                                   type="number"
                                   min="0"
                                   class="w-full bg-white border-2 border-red-200 focus:border-red-400 rounded-xl px-3 py-2.5 text-center text-xl font-black text-red-800 focus:ring-0 outline-none transition-all font-cairo"/>
                            @error('hardCount')
                                <p class="text-xs text-error mt-1.5 font-cairo">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Live validation bar --}}
                    <div class="bg-surface-container rounded-xl p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-bold text-on-surface-variant font-cairo">التوزيع البصري</span>
                            <span class="text-xs font-bold font-cairo"
                                  :class="total === 0 ? 'text-outline' : 'text-primary'"
                                  x-text="total + ' سؤال إجمالاً'"></span>
                        </div>
                        <div class="flex h-3 rounded-full overflow-hidden gap-0.5" x-show="total > 0">
                            <div class="bg-emerald-400 transition-all duration-300 rounded-r-full"
                                 :style="`width: ${(parseInt($wire.easyCount)||0) / total * 100}%`"></div>
                            <div class="bg-amber-400 transition-all duration-300"
                                 :style="`width: ${(parseInt($wire.mediumCount)||0) / total * 100}%`"></div>
                            <div class="bg-red-400 transition-all duration-300 rounded-l-full"
                                 :style="`width: ${(parseInt($wire.hardCount)||0) / total * 100}%`"></div>
                        </div>
                        <div class="h-3 rounded-full bg-outline-variant/20" x-show="total === 0"></div>
                        <div class="flex justify-between mt-2 text-[10px] text-on-surface-variant font-cairo" x-show="total > 0">
                            <span x-text="(parseInt($wire.easyCount)||0) + ' سهل'"></span>
                            <span x-text="(parseInt($wire.mediumCount)||0) + ' متوسط'"></span>
                            <span x-text="(parseInt($wire.hardCount)||0) + ' صعب'"></span>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center gap-2 text-xs text-on-surface-variant font-cairo" x-show="total === 0">
                        <span class="material-symbols-outlined text-[14px]">info</span>
                        أدخل عدد الأسئلة لكل مستوى صعوبة (الإجمالي يجب أن يكون 1 على الأقل)
                    </div>
                </div>
            </section>

        </div>

        {{-- Right column: schedule + settings --}}
        <div class="lg:col-span-4">
            <div class="bg-surface-container-low rounded-2xl border border-outline-variant/20 p-6 space-y-5 lg:sticky lg:top-20">

                <div class="flex items-center justify-between pb-4 border-b border-outline-variant/20">
                    <h4 class="text-sm font-black text-primary uppercase tracking-widest font-cairo">إعدادات الامتحان</h4>
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px]">tune</span>
                </div>

                {{-- Duration --}}
                <div>
                    <label for="durationMinutes" class="block text-xs font-bold text-on-surface-variant mb-2 font-cairo">
                        المدة الزمنية
                    </label>
                    <div class="relative">
                        <input wire:model="durationMinutes"
                               id="durationMinutes"
                               type="number"
                               min="1"
                               max="600"
                               class="w-full bg-surface-container-lowest border-2 border-outline-variant/20 rounded-xl px-4 py-3 appearance-none focus:ring-2 focus:ring-primary/20 focus:border-primary/30 shadow-sm outline-none transition-all font-cairo text-sm pr-16"/>
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-on-surface-variant font-cairo font-bold">دقيقة</span>
                    </div>
                    @error('durationMinutes')
                        <p class="text-xs text-error mt-1.5 font-cairo flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Scheduled at --}}
                <div>
                    <label for="scheduledAt" class="block text-xs font-bold text-on-surface-variant mb-2 font-cairo">
                        موعد الانطلاق
                    </label>
                    <input wire:model="scheduledAt"
                           id="scheduledAt"
                           type="datetime-local"
                           class="w-full bg-surface-container-lowest border-2 border-outline-variant/20 rounded-xl px-4 py-3 appearance-none focus:ring-2 focus:ring-primary/20 focus:border-primary/30 shadow-sm outline-none transition-all font-cairo text-sm"/>
                    @error('scheduledAt')
                        <p class="text-xs text-error mt-1.5 font-cairo flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Summary card --}}
                <div class="pt-2 border-t border-outline-variant/20 space-y-3">
                    <h5 class="text-xs font-bold text-on-surface-variant font-cairo uppercase tracking-widest">ملخص</h5>
                    <div class="space-y-2 text-sm font-cairo">
                        <div class="flex justify-between text-on-surface-variant">
                            <span>المدة</span>
                            <span class="font-bold text-on-surface">{{ $durationMinutes }} دقيقة</span>
                        </div>
                        <div class="flex justify-between text-on-surface-variant">
                            <span>إجمالي الأسئلة</span>
                            <span class="font-bold text-on-surface" x-text="total + ' سؤال'">{{ $easyCount + $mediumCount + $hardCount }} سؤال</span>
                        </div>
                    </div>
                    <div class="flex items-start gap-2 bg-primary-fixed/40 text-on-primary-fixed-variant px-3.5 py-3 rounded-xl text-xs font-cairo leading-relaxed">
                        <span class="material-symbols-outlined text-[16px] shrink-0 mt-0.5">info</span>
                        <span>بعد الإنشاء، يمكنك مراجعة الامتحان والضغط على "ابدأ" لفتحه أمام الطلاب.</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>
