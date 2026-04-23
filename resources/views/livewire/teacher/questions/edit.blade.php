<div class="p-6 lg:p-8">

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant mb-2 font-cairo">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <a href="{{ route('teacher.questions.index') }}" wire:navigate class="hover:text-primary transition-colors">بنك الأسئلة</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <span class="text-on-surface font-semibold">تعديل السؤال</span>
            </nav>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">تعديل السؤال</h1>
            <p class="text-on-surface-variant mt-1 text-sm font-cairo">تحديث نص السؤال وخياراته وتصنيفه</p>
        </div>
        <div class="flex flex-wrap gap-3 shrink-0">
            <a href="{{ route('teacher.questions.index') }}" wire:navigate
               class="px-5 py-2.5 rounded-xl border border-outline-variant/40 text-on-surface-variant font-semibold hover:bg-surface-container transition-colors flex items-center gap-2 font-cairo text-sm">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                رجوع
            </a>
            <button wire:click="delete"
                    wire:confirm="حذف هذا السؤال نهائياً؟ لا يمكن التراجع عن هذا الإجراء."
                    class="px-5 py-2.5 rounded-xl border-2 border-error/30 text-error font-semibold hover:bg-error-container/30 transition-colors flex items-center gap-2 font-cairo text-sm">
                <span class="material-symbols-outlined text-[18px]">delete</span>
                حذف السؤال
            </button>
            <button wire:click="save"
                    class="btn-gradient text-on-primary px-6 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity font-cairo text-sm">
                <span class="material-symbols-outlined text-[18px] icon-filled">save</span>
                حفظ التعديلات
            </button>
        </div>
    </div>

    {{-- Form Body --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- Left Column: Question content --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- Question text --}}
            <section class="bg-surface-container-lowest rounded-xl shadow-ambient border-r-4 border-primary transition-all focus-within:shadow-glass overflow-hidden">
                <div class="px-6 pt-6 pb-2">
                    <label for="text" class="flex items-center gap-2 text-sm font-bold text-primary mb-3 font-cairo">
                        <span class="material-symbols-outlined text-[20px]">subject</span>
                        نص السؤال
                    </label>
                    <textarea wire:model="text"
                              id="text"
                              rows="5"
                              placeholder="اكتب نص السؤال هنا بالتفصيل..."
                              class="w-full bg-surface-container-low border-2 border-transparent focus:border-primary/20 rounded-xl p-4 text-base focus:ring-0 resize-none transition-all placeholder:text-outline font-cairo outline-none leading-relaxed"></textarea>
                </div>
                @error('text')
                    <div class="px-6 pb-4">
                        <p class="text-xs text-error font-cairo flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    </div>
                @enderror
                @if (!$errors->has('text'))
                    <div class="h-4"></div>
                @endif
            </section>

            {{-- Choices --}}
            <section class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-on-surface font-cairo flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary">checklist</span>
                        الخيارات
                    </h3>
                    @error('correctIndex')
                        <span class="text-error text-xs font-medium flex items-center gap-1 bg-error-container/30 px-2.5 py-1 rounded-lg font-cairo">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </span>
                    @else
                        @if (!$errors->has('choices'))
                            <span class="text-on-surface-variant text-xs font-cairo flex items-center gap-1 bg-surface-container px-2.5 py-1 rounded-lg">
                                <span class="material-symbols-outlined text-[14px]">info</span>
                                حدد الإجابة الصحيحة بالضغط على الدائرة
                            </span>
                        @endif
                    @enderror
                </div>

                @error('choices')
                    <p class="text-xs text-error font-cairo flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror

                @php $choiceLabels = ['أ', 'ب', 'ج', 'د']; @endphp
                @foreach (range(0, 3) as $i)
                    <div class="group flex items-center gap-3 bg-surface-container-lowest rounded-xl border-2 transition-all
                                {{ (int)$correctIndex === $i
                                    ? 'border-primary bg-emerald-50/40 shadow-sm'
                                    : 'border-outline-variant/20 hover:border-primary/30 hover:bg-surface-container-low' }}">

                        {{-- Radio button --}}
                        <label class="flex items-center cursor-pointer px-4 py-4 shrink-0" for="correct_{{ $i }}">
                            <input type="radio"
                                   wire:model.live="correctIndex"
                                   value="{{ $i }}"
                                   id="correct_{{ $i }}"
                                   class="sr-only peer"/>
                            <div class="w-6 h-6 border-2 rounded-full flex items-center justify-center transition-all
                                        {{ (int)$correctIndex === $i
                                            ? 'border-primary bg-primary'
                                            : 'border-outline-variant group-hover:border-primary/50' }}">
                                @if ((int)$correctIndex === $i)
                                    <div class="w-2.5 h-2.5 bg-on-primary rounded-full"></div>
                                @endif
                            </div>
                        </label>

                        {{-- Choice content --}}
                        <div class="flex-1 py-3">
                            <span class="block text-[10px] font-bold text-on-surface-variant mb-0.5 font-cairo">
                                الخيار {{ $choiceLabels[$i] }}
                                @if ((int)$correctIndex === $i)
                                    <span class="text-primary mr-1">✓ صحيح</span>
                                @endif
                            </span>
                            <input wire:model="choices.{{ $i }}"
                                   type="text"
                                   id="choice_{{ $i }}"
                                   placeholder="أدخل نص الخيار {{ $choiceLabels[$i] }}..."
                                   class="w-full bg-transparent border-none p-0 focus:ring-0 text-on-surface font-semibold placeholder:text-outline/60 outline-none font-cairo text-sm"/>
                        </div>

                        <div class="px-4">
                            <div class="w-1 h-8 rounded-full {{ (int)$correctIndex === $i ? 'bg-primary' : 'bg-outline-variant/20' }} transition-colors"></div>
                        </div>
                    </div>
                    @error('choices.'.$i)
                        <p class="text-xs text-error font-cairo flex items-center gap-1 -mt-2 px-2">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                @endforeach
            </section>

        </div>

        {{-- Right Column: Metadata --}}
        <div class="lg:col-span-4">
            <div class="bg-surface-container-low rounded-2xl border border-outline-variant/20 p-6 space-y-5 lg:sticky lg:top-20">

                <div class="flex items-center justify-between pb-4 border-b border-outline-variant/20">
                    <h4 class="text-sm font-black text-primary uppercase tracking-widest font-cairo">تصنيف السؤال</h4>
                    <span class="material-symbols-outlined text-on-surface-variant text-[18px]">settings_input_component</span>
                </div>

                {{-- Module --}}
                <div>
                    <label for="moduleId" class="block text-xs font-bold text-on-surface-variant mb-2 font-cairo">المقياس</label>
                    <div class="relative">
                        <select wire:model="moduleId"
                                id="moduleId"
                                class="w-full bg-surface-container-lowest border-2 border-outline-variant/20 rounded-xl px-4 py-3 appearance-none focus:ring-2 focus:ring-primary/20 focus:border-primary/30 shadow-sm cursor-pointer outline-none transition-all font-cairo text-sm">
                            <option value="">— اختر المقياس —</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module->id }}">{{ $module->name }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[18px]">keyboard_arrow_down</span>
                    </div>
                    @error('moduleId')
                        <p class="text-xs text-error mt-1.5 font-cairo flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Level --}}
                <div>
                    <label for="level" class="block text-xs font-bold text-on-surface-variant mb-2 font-cairo">المستوى</label>
                    <div class="relative">
                        <select wire:model="level"
                                id="level"
                                class="w-full bg-surface-container-lowest border-2 border-outline-variant/20 rounded-xl px-4 py-3 appearance-none focus:ring-2 focus:ring-primary/20 focus:border-primary/30 shadow-sm cursor-pointer outline-none transition-all font-cairo text-sm">
                            <option value="">— اختر المستوى —</option>
                            @foreach ($levels as $lvl)
                                <option value="{{ $lvl->value }}">{{ $lvl->value }}</option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[18px]">keyboard_arrow_down</span>
                    </div>
                    @error('level')
                        <p class="text-xs text-error mt-1.5 font-cairo flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Difficulty --}}
                <div>
                    <label for="difficulty" class="block text-xs font-bold text-on-surface-variant mb-2 font-cairo">الصعوبة</label>
                    <div class="relative">
                        <select wire:model="difficulty"
                                id="difficulty"
                                class="w-full bg-surface-container-lowest border-2 border-outline-variant/20 rounded-xl px-4 py-3 appearance-none focus:ring-2 focus:ring-primary/20 focus:border-primary/30 shadow-sm cursor-pointer outline-none transition-all font-cairo text-sm">
                            <option value="">— اختر الصعوبة —</option>
                            @foreach ($difficulties as $diff)
                                <option value="{{ $diff->value }}">
                                    @if($diff->value === 'easy') سهل
                                    @elseif($diff->value === 'medium') متوسط
                                    @else صعب
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline pointer-events-none text-[18px]">keyboard_arrow_down</span>
                    </div>
                    @error('difficulty')
                        <p class="text-xs text-error mt-1.5 font-cairo flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Meta info --}}
                <div class="pt-2 border-t border-outline-variant/20 space-y-2">
                    <div class="flex items-center gap-2 text-xs text-on-surface-variant font-cairo">
                        <span class="material-symbols-outlined text-[14px]">schedule</span>
                        أُنشئ {{ $question->created_at->diffForHumans() }}
                    </div>
                    <div class="flex items-start gap-2 bg-amber-50 text-amber-800 px-3.5 py-3 rounded-xl text-xs font-cairo leading-relaxed border border-amber-200">
                        <span class="material-symbols-outlined text-[16px] shrink-0 mt-0.5">warning</span>
                        <span>تعديل هذا السؤال لن يؤثر على الامتحانات المكتملة.</span>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>
