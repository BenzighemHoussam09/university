<div class="p-6 lg:p-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant mb-2 font-cairo">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <span class="text-on-surface font-semibold">بنك الأسئلة</span>
            </nav>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">بنك الأسئلة</h1>
            <p class="text-on-surface-variant mt-1 text-sm font-cairo">إدارة أسئلة الاختبار وتصنيفها</p>
        </div>
        <a href="{{ route('teacher.questions.create') }}" wire:navigate
           class="btn-gradient text-on-primary px-5 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity self-start sm:self-auto">
            <span class="material-symbols-outlined text-[20px] icon-filled">add_circle</span>
            <span class="font-cairo">سؤال جديد</span>
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
        <div class="bg-surface-container-low px-6 py-4 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px]">filter_list</span>
            <h2 class="font-bold text-on-surface font-cairo">تصفية الأسئلة</h2>
        </div>
        <div class="px-6 py-5">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5 font-cairo">المقياس</label>
                    <select wire:model.live="moduleId"
                            class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 transition-all font-cairo outline-none appearance-none">
                        <option value="">كل المقاييس</option>
                        @foreach ($modules as $module)
                            <option value="{{ $module->id }}">{{ $module->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5 font-cairo">المستوى</label>
                    <select wire:model.live="level"
                            class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 transition-all font-cairo outline-none appearance-none">
                        <option value="">كل المستويات</option>
                        @foreach ($levels as $lvl)
                            <option value="{{ $lvl->value }}">{{ $lvl->value }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-on-surface-variant mb-1.5 font-cairo">الصعوبة</label>
                    <select wire:model.live="difficulty"
                            class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 transition-all font-cairo outline-none appearance-none">
                        <option value="">كل المستويات</option>
                        @foreach ($difficulties as $diff)
                            <option value="{{ $diff->value }}">
                                @if($diff->value === 'easy') سهل
                                @elseif($diff->value === 'medium') متوسط
                                @else صعب
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Questions Table --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
        <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
            <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px] icon-filled">quiz</span>
                قائمة الأسئلة
            </h2>
            <span class="text-sm text-on-surface-variant font-cairo bg-surface-container px-3 py-1 rounded-full">
                {{ $questions->total() }} سؤال
            </span>
        </div>

        @if ($questions->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <div class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-5xl text-outline-variant">quiz</span>
                </div>
                <h3 class="text-xl font-bold text-on-surface font-cairo mb-2">لا توجد أسئلة</h3>
                <p class="text-on-surface-variant text-sm max-w-md font-cairo leading-relaxed mb-6">
                    @if ($moduleId || $level || $difficulty)
                        لا توجد أسئلة تطابق الفلتر المحدد. جرّب تغيير معايير البحث.
                    @else
                        ابدأ ببناء بنك الأسئلة الخاص بك بإضافة أسئلتك الأولى.
                    @endif
                </p>
                @if (!$moduleId && !$level && !$difficulty)
                    <a href="{{ route('teacher.questions.create') }}" wire:navigate
                       class="btn-gradient text-on-primary px-5 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-[18px] icon-filled">add_circle</span>
                        <span class="font-cairo">إضافة سؤال</span>
                    </a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20 bg-surface-container-lowest">
                            <th scope="col" class="px-6 py-4 font-cairo text-start">نص السؤال</th>
                            <th scope="col" class="px-6 py-4 font-cairo">المقياس</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">المستوى</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الصعوبة</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الخيارات</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach ($questions as $question)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 bg-primary-fixed flex items-center justify-center rounded-lg text-on-primary-fixed-variant flex-shrink-0">
                                            <span class="material-symbols-outlined text-[18px]">help_outline</span>
                                        </div>
                                        <span class="font-medium text-on-surface font-cairo text-sm max-w-xs truncate block">
                                            {{ $question->text }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant font-cairo text-sm">
                                    {{ $question->module->name }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-primary-fixed text-on-primary-fixed-variant px-2.5 py-1 rounded-full text-xs font-bold font-cairo">
                                        {{ $question->level->value }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $diffClasses = match($question->difficulty->value) {
                                            'easy'   => 'bg-emerald-100 text-emerald-800',
                                            'medium' => 'bg-amber-100 text-amber-800',
                                            'hard'   => 'bg-red-100 text-red-800',
                                            default  => 'bg-surface-container text-on-surface-variant',
                                        };
                                        $diffLabel = match($question->difficulty->value) {
                                            'easy'   => 'سهل',
                                            'medium' => 'متوسط',
                                            'hard'   => 'صعب',
                                            default  => $question->difficulty->value,
                                        };
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold font-cairo {{ $diffClasses }}">
                                        {{ $diffLabel }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-1 bg-surface-container text-on-surface-variant px-2.5 py-1 rounded-full text-xs font-bold">
                                        <span class="material-symbols-outlined text-[14px]">checklist</span>
                                        <span class="font-cairo">{{ $question->choices->count() }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('teacher.questions.edit', $question) }}" wire:navigate
                                           class="p-2 text-on-primary-fixed-variant hover:bg-primary-fixed rounded-lg transition-colors flex items-center gap-1 text-sm font-bold font-cairo">
                                            <span class="material-symbols-outlined text-[18px]">edit</span>
                                            تعديل
                                        </a>
                                        <button wire:click="delete({{ $question->id }})"
                                                wire:confirm="حذف هذا السؤال؟ لا يمكن التراجع عن هذا الإجراء."
                                                class="p-2 text-error hover:bg-error-container rounded-lg transition-colors flex items-center gap-1 text-sm font-bold font-cairo">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($questions->hasPages())
                <div class="px-6 py-4 border-t border-outline-variant/10">
                    {{ $questions->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
