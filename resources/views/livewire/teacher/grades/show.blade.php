<div class="p-6 lg:p-8 space-y-6">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant">
        <a href="{{ route('teacher.groups.index') }}" class="hover:text-primary transition-colors">المجموعات</a>
        <span class="material-symbols-outlined text-[14px]">chevron_left</span>
        <a href="{{ route('teacher.groups.show', $group) }}" class="hover:text-primary transition-colors">{{ $group->name }}</a>
        <span class="material-symbols-outlined text-[14px]">chevron_left</span>
        <span class="text-on-surface font-medium">الدرجات</span>
    </nav>

    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">
                {{ $group->module?->name }} — رصد العلامات
            </h1>
            <p class="text-on-surface-variant mt-1">
                {{ $group->name }}
                @if($group->level)
                    · <span class="font-medium">{{ $group->level }}</span>
                @endif
            </p>
        </div>
        <a href="{{ route('teacher.settings') }}"
           class="flex items-center gap-2 text-sm text-on-surface-variant hover:text-primary transition-colors self-start sm:self-auto">
            <span class="material-symbols-outlined text-[18px]">tune</span>
            تعديل الأوزان
        </a>
    </div>

    {{-- Flash messages --}}
    @if ($successMessage)
        <div class="flex items-center gap-3 p-4 bg-primary-fixed/40 text-on-primary-fixed-variant rounded-xl text-sm font-medium"
             x-data x-init="setTimeout(() => $el.remove(), 4000)">
            <span class="material-symbols-outlined text-[18px] shrink-0 icon-filled">check_circle</span>
            تم حفظ الدرجات بنجاح.
        </div>
    @endif
    @if ($errorMessage)
        <div class="flex items-center gap-3 p-4 bg-error-container text-on-error-container rounded-xl text-sm font-medium">
            <span class="material-symbols-outlined text-[18px] shrink-0 icon-filled">error</span>
            @if(str_contains($errorMessage, 'template not found'))
                لا يوجد نموذج تقييم. يرجى الذهاب إلى
                <a href="{{ route('teacher.settings') }}" class="underline ms-1">الإعدادات</a>.
            @else
                {{ $errorMessage }}
            @endif
        </div>
    @endif

    {{-- Template info box --}}
    @if ($template)
        <div class="p-6 bg-primary-container text-on-primary-container rounded-2xl shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6 overflow-hidden relative group">
            <div class="absolute -right-10 -top-10 w-40 h-40 bg-white/5 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700 pointer-events-none"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="bg-primary p-3 rounded-xl shadow-inner shrink-0">
                    <span class="material-symbols-outlined text-white icon-filled">info</span>
                </div>
                <div>
                    <h3 class="font-bold text-lg leading-none mb-1">حدود العلامات المعتمدة</h3>
                    <p class="text-sm opacity-80">يجب الالتزام بالمعايير التالية لكل مكوّن تقييمي</p>
                </div>
            </div>
            <div class="relative z-10 grid grid-cols-2 md:grid-cols-4 gap-4 flex-grow">
                <div class="flex flex-col border-r border-white/10 pr-4">
                    <span class="text-[0.65rem] uppercase tracking-widest opacity-70 mb-1">الامتحان</span>
                    <span class="text-xl font-bold">{{ $template->exam_max }} / {{ $template->exam_max }}</span>
                </div>
                <div class="flex flex-col border-r border-white/10 pr-4">
                    <span class="text-[0.65rem] uppercase tracking-widest opacity-70 mb-1">العمل الشخصي</span>
                    <span class="text-xl font-bold">{{ $template->personal_work_max }} / {{ $template->personal_work_max }}</span>
                </div>
                <div class="flex flex-col border-r border-white/10 pr-4">
                    <span class="text-[0.65rem] uppercase tracking-widest opacity-70 mb-1">الحضور</span>
                    <span class="text-xl font-bold">{{ $template->attendance_max }} / {{ $template->attendance_max }}</span>
                </div>
                <div class="flex flex-col pr-4">
                    <span class="text-[0.65rem] uppercase tracking-widest opacity-70 mb-1">المشاركة</span>
                    <span class="text-xl font-bold">{{ $template->participation_max }} / {{ $template->participation_max }}</span>
                </div>
            </div>
        </div>
    @else
        <div class="flex items-start gap-3 p-4 bg-error-container/30 border border-error/20 rounded-xl text-sm text-on-error-container">
            <span class="material-symbols-outlined text-[18px] shrink-0 mt-0.5">warning</span>
            <span>
                لا يوجد نموذج تقييم.
                <a href="{{ route('teacher.settings') }}" class="underline font-semibold ms-1">اذهب إلى الإعدادات</a>
                لتحديد حدود المكونات.
            </span>
        </div>
    @endif

    {{-- Grade Grid --}}
    <div class="bg-surface-container-lowest rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse min-w-[700px]">
                <thead>
                    <tr class="bg-surface-container-low border-b border-outline-variant/10">
                        <th scope="col" class="sticky right-0 bg-surface-container-low px-6 py-5 font-bold text-primary z-20 w-56 text-right">
                            اسم الطالب
                        </th>
                        <th scope="col" class="px-6 py-5 font-bold text-primary text-right whitespace-nowrap">
                            الامتحان
                            @if($template)
                                <span class="text-xs font-normal text-on-surface-variant">({{ $template->exam_max }})</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-5 font-bold text-primary text-right whitespace-nowrap">
                            العمل الشخصي
                            @if($template)
                                <span class="text-xs font-normal text-on-surface-variant">({{ $template->personal_work_max }})</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-5 font-bold text-primary text-right whitespace-nowrap">
                            الحضور
                            @if($template)
                                <span class="text-xs font-normal text-on-surface-variant">({{ $template->attendance_max }})</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-5 font-bold text-primary text-right whitespace-nowrap">
                            المشاركة
                            @if($template)
                                <span class="text-xs font-normal text-on-surface-variant">({{ $template->participation_max }})</span>
                            @endif
                        </th>
                        <th scope="col" class="px-6 py-5 font-bold text-primary text-right whitespace-nowrap">
                            العلامة النهائية (20)
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-outline-variant/5">

                    @forelse ($rows as $studentId => $row)
                        <tr class="hover:bg-surface-container-low/30 transition-colors"
                            x-data="{
                                pw:   {{ (float) $row['personal_work'] }},
                                att:  {{ (float) $row['attendance'] }},
                                part: {{ (float) $row['participation'] }},
                                exam: {{ (float) $row['exam_component'] }},
                                pwMax:   {{ $template?->personal_work_max ?? 20 }},
                                attMax:  {{ $template?->attendance_max ?? 20 }},
                                partMax: {{ $template?->participation_max ?? 20 }},
                                get pwOk()   { return !isNaN(this.pw)   && this.pw   >= 0 && this.pw   <= this.pwMax;   },
                                get attOk()  { return !isNaN(this.att)  && this.att  >= 0 && this.att  <= this.attMax;  },
                                get partOk() { return !isNaN(this.part) && this.part >= 0 && this.part <= this.partMax; },
                                get allOk()  { return this.pwOk && this.attOk && this.partOk; },
                                get final()  { return this.allOk ? (this.exam + this.pw + this.att + this.part).toFixed(2) : '--'; }
                            }">

                            {{-- Student name sticky --}}
                            <td class="sticky right-0 bg-white px-6 py-4 font-semibold text-on-surface z-10 border-l border-outline-variant/5">
                                {{ $row['student_name'] }}
                            </td>

                            {{-- Exam (read-only) --}}
                            <td class="px-6 py-4">
                                <div class="bg-surface-container-low text-on-surface-variant px-4 py-2.5 rounded-lg w-24 text-center font-medium select-none">
                                    {{ number_format($row['exam_component'], 2) }}
                                </div>
                            </td>

                            {{-- Personal Work --}}
                            <td class="px-6 py-4">
                                <div class="relative pb-5">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $template?->personal_work_max ?? 20 }}"
                                        wire:model="rows.{{ $studentId }}.personal_work"
                                        wire:blur="saveRow({{ $studentId }})"
                                        @input="pw = parseFloat($event.target.value)"
                                        :class="pwOk
                                            ? 'border-transparent focus:border-primary focus:ring-2 focus:ring-surface-tint/20'
                                            : 'border-error border-2 text-error focus:ring-2 focus:ring-error/10'"
                                        class="bg-surface-container-highest rounded-lg w-24 px-3 py-2.5 text-center transition-all border outline-none"
                                    >
                                    <span x-show="!pwOk"
                                          x-transition:enter="transition ease-out duration-100"
                                          x-transition:enter-start="opacity-0"
                                          x-transition:enter-end="opacity-100"
                                          class="absolute bottom-0 right-0 text-[10px] text-error font-bold whitespace-nowrap">
                                        قيمة غير صالحة
                                    </span>
                                </div>
                            </td>

                            {{-- Attendance --}}
                            <td class="px-6 py-4">
                                <div class="relative pb-5">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $template?->attendance_max ?? 20 }}"
                                        wire:model="rows.{{ $studentId }}.attendance"
                                        wire:blur="saveRow({{ $studentId }})"
                                        @input="att = parseFloat($event.target.value)"
                                        :class="attOk
                                            ? 'border-transparent focus:border-primary focus:ring-2 focus:ring-surface-tint/20'
                                            : 'border-error border-2 text-error focus:ring-2 focus:ring-error/10'"
                                        class="bg-surface-container-highest rounded-lg w-24 px-3 py-2.5 text-center transition-all border outline-none"
                                    >
                                    <span x-show="!attOk"
                                          x-transition:enter="transition ease-out duration-100"
                                          x-transition:enter-start="opacity-0"
                                          x-transition:enter-end="opacity-100"
                                          class="absolute bottom-0 right-0 text-[10px] text-error font-bold whitespace-nowrap">
                                        قيمة غير صالحة
                                    </span>
                                </div>
                            </td>

                            {{-- Participation --}}
                            <td class="px-6 py-4">
                                <div class="relative pb-5">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        max="{{ $template?->participation_max ?? 20 }}"
                                        wire:model="rows.{{ $studentId }}.participation"
                                        wire:blur="saveRow({{ $studentId }})"
                                        @input="part = parseFloat($event.target.value)"
                                        :class="partOk
                                            ? 'border-transparent focus:border-primary focus:ring-2 focus:ring-surface-tint/20'
                                            : 'border-error border-2 text-error focus:ring-2 focus:ring-error/10'"
                                        class="bg-surface-container-highest rounded-lg w-24 px-3 py-2.5 text-center transition-all border outline-none"
                                    >
                                    <span x-show="!partOk"
                                          x-transition:enter="transition ease-out duration-100"
                                          x-transition:enter-start="opacity-0"
                                          x-transition:enter-end="opacity-100"
                                          class="absolute bottom-0 right-0 text-[10px] text-error font-bold whitespace-nowrap">
                                        قيمة غير صالحة
                                    </span>
                                </div>
                            </td>

                            {{-- Final Grade --}}
                            <td class="px-6 py-4">
                                <span x-text="final"
                                      :class="allOk ? 'text-primary' : 'text-on-surface-variant opacity-50'"
                                      class="font-bold text-lg font-headline"></span>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-20 text-center">
                                <span class="material-symbols-outlined text-5xl text-on-surface-variant/30 block mb-3">group</span>
                                <p class="text-on-surface-variant font-semibold">لا يوجد طلاب في هذه المجموعة</p>
                                <p class="text-xs text-on-surface-variant/60 mt-1">
                                    <a href="{{ route('teacher.groups.show', $group) }}" class="text-primary hover:underline">
                                        أضف طلاباً إلى المجموعة
                                    </a>
                                </p>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    {{-- Footer actions --}}
    @if (!empty($rows))
        <div class="flex flex-col md:flex-row items-center justify-between gap-6 bg-surface-container-low/50 p-6 rounded-2xl">
            <div class="flex items-center gap-3 text-on-surface-variant">
                <span class="material-symbols-outlined text-primary">history</span>
                <span class="text-sm">
                    @if ($successMessage)
                        تم حفظ جميع الدرجات
                    @else
                        انقر على حقل ثم غادره للحفظ التلقائي
                    @endif
                </span>
                <div wire:loading wire:target="saveRow,saveAll"
                     class="flex items-center gap-1.5 text-xs text-on-surface-variant">
                    <span class="material-symbols-outlined text-[14px] animate-spin">refresh</span>
                    جارٍ الحفظ...
                </div>
            </div>
            <div class="flex gap-3">
                <button
                    wire:click="cancelChanges"
                    wire:confirm="سيتم التراجع عن جميع التغييرات غير المحفوظة. هل أنت متأكد؟"
                    class="px-7 py-3 border-2 border-outline-variant/40 text-primary font-bold rounded-xl hover:bg-white transition-all active:scale-95 text-sm">
                    إلغاء التغييرات
                </button>
                <button
                    wire:click="saveAll"
                    class="btn-gradient text-on-primary px-8 py-3 font-bold rounded-xl shadow-ambient hover:opacity-90 transition-all active:scale-95 flex items-center gap-2 text-sm">
                    <span class="material-symbols-outlined text-[18px] icon-filled">save</span>
                    حفظ العلامات
                </button>
            </div>
        </div>
    @endif

</div>
