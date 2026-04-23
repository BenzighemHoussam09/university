<div class="p-6 lg:p-8 space-y-6" x-data="{ activeTab: 'students' }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant mb-2 font-cairo">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <a href="{{ route('teacher.groups.index') }}" class="hover:text-primary transition-colors">المجموعات</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <span class="text-on-surface font-semibold">{{ $group->name }}</span>
            </nav>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">{{ $group->name }}</h1>
            <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                <span class="bg-primary-fixed text-on-primary-fixed-variant px-2.5 py-1 rounded-full text-xs font-bold font-cairo">
                    {{ $group->level->value }}
                </span>
                <span class="text-on-surface-variant text-sm font-cairo">{{ $group->module->name }}</span>
                <span class="text-outline-variant text-sm">•</span>
                <span class="text-on-surface-variant text-sm font-cairo">{{ $students->count() }} طالب</span>
            </div>
        </div>
        <a href="{{ route('teacher.grades.show', $group) }}"
           class="flex items-center gap-2 px-4 py-2 border border-outline-variant/30 rounded-xl text-sm font-semibold text-on-surface-variant hover:bg-surface-container transition-colors self-start font-cairo">
            <span class="material-symbols-outlined text-[18px]">grade</span>
            جدول الدرجات
        </a>
    </div>

    {{-- Tab bar --}}
    <div class="flex gap-1 bg-surface-container rounded-xl p-1 w-fit flex-wrap"
         role="tablist" aria-label="أقسام إدارة الفوج">
        <button @click="activeTab = 'students'"
                :class="activeTab === 'students' ? 'bg-surface-container-lowest text-primary shadow-sm font-bold' : 'text-on-surface-variant hover:text-on-surface'"
                class="px-4 py-2 rounded-lg text-sm transition-all font-cairo flex items-center gap-1.5"
                role="tab" :aria-selected="activeTab === 'students'" aria-controls="tab-students">
            <span class="material-symbols-outlined text-[18px]"
                  :class="activeTab === 'students' ? 'icon-filled' : ''">groups</span>
            الطلاب
            <span class="bg-primary/10 text-primary px-1.5 py-0.5 rounded-full text-[10px] font-bold">{{ $students->count() }}</span>
        </button>
        <button @click="activeTab = 'add'"
                :class="activeTab === 'add' ? 'bg-surface-container-lowest text-primary shadow-sm font-bold' : 'text-on-surface-variant hover:text-on-surface'"
                class="px-4 py-2 rounded-lg text-sm transition-all font-cairo flex items-center gap-1.5"
                role="tab" :aria-selected="activeTab === 'add'" aria-controls="tab-add">
            <span class="material-symbols-outlined text-[18px]"
                  :class="activeTab === 'add' ? 'icon-filled' : ''">person_add</span>
            إضافة طالب
        </button>
        @if ($unassigned->isNotEmpty())
            <button @click="activeTab = 'assign'"
                    :class="activeTab === 'assign' ? 'bg-surface-container-lowest text-primary shadow-sm font-bold' : 'text-on-surface-variant hover:text-on-surface'"
                    class="px-4 py-2 rounded-lg text-sm transition-all font-cairo flex items-center gap-1.5"
                    role="tab" :aria-selected="activeTab === 'assign'" aria-controls="tab-assign">
                <span class="material-symbols-outlined text-[18px]"
                      :class="activeTab === 'assign' ? 'icon-filled' : ''">transfer_within_a_station</span>
                إسناد موجود
            </button>
        @endif
    </div>

    {{-- Students tab --}}
    <div x-show="activeTab === 'students'" x-transition id="tab-students" role="tabpanel">
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
            <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px] icon-filled">groups</span>
                    الطلاب في هذا الفوج
                </h2>
                <button @click="activeTab = 'add'"
                        class="flex items-center gap-1.5 text-on-primary-fixed-variant hover:bg-primary-fixed px-3 py-1.5 rounded-lg transition-colors text-sm font-bold font-cairo">
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    إضافة طالب
                </button>
            </div>

            @if ($students->isEmpty())
                <div class="flex flex-col items-center justify-center py-14 text-center px-6">
                    <div class="w-16 h-16 bg-surface-container rounded-full flex items-center justify-center mb-4">
                        <span class="material-symbols-outlined text-4xl text-outline-variant">person_off</span>
                    </div>
                    <h3 class="text-lg font-bold text-on-surface font-cairo mb-2">لا يوجد طلاب في هذا الفوج</h3>
                    <p class="text-on-surface-variant text-sm font-cairo">
                        انقر على "إضافة طالب" لبدء تسجيل الطلاب.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20 bg-surface-container-lowest">
                                <th scope="col" class="px-6 py-4 font-cairo text-start">الطالب</th>
                                <th scope="col" class="px-6 py-4 font-cairo">البريد الإلكتروني</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">تاريخ الانضمام</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach ($students as $student)
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-primary-fixed flex items-center justify-center text-on-primary-fixed-variant font-bold text-sm flex-shrink-0">
                                                {{ mb_substr($student->name, 0, 1) }}
                                            </div>
                                            <a href="{{ route('teacher.students.show', $student) }}"
                                               class="font-semibold text-on-surface hover:text-primary transition-colors font-cairo">
                                                {{ $student->name }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-on-surface-variant text-sm font-cairo">
                                        {{ $student->email }}
                                    </td>
                                    <td class="px-6 py-4 text-center text-on-surface-variant text-sm font-cairo">
                                        {{ $student->pivot->created_at?->format('d/m/Y') ?? '—' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('teacher.students.show', $student) }}"
                                               class="p-2 text-on-primary-fixed-variant hover:bg-primary-fixed rounded-lg transition-colors flex items-center gap-1 text-sm font-bold font-cairo">
                                                <span class="material-symbols-outlined text-[18px]">visibility</span>
                                                عرض
                                            </a>
                                            <button wire:click="removeFromGroup({{ $student->id }})"
                                                    wire:confirm="إزالة {{ $student->name }} من هذا الفوج؟"
                                                    class="p-2 text-error hover:bg-error-container rounded-lg transition-colors flex items-center gap-1 text-sm font-bold font-cairo">
                                                <span class="material-symbols-outlined text-[18px]">remove_circle</span>
                                                إزالة
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Add new student tab --}}
    <div x-show="activeTab === 'add'" x-transition id="tab-add" role="tabpanel">
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
            <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px] icon-filled">person_add</span>
                    إنشاء طالب جديد وإضافته
                </h2>
                <button @click="activeTab = 'students'"
                        class="p-1.5 rounded-full hover:bg-surface-container text-on-surface-variant transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form wire:submit="addStudent" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-1.5 font-cairo">الاسم الكامل</label>
                        <input wire:model="newStudentName"
                               type="text"
                               placeholder="اسم الطالب الكامل"
                               class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 focus:border-surface-tint/50 transition-all font-cairo outline-none"/>
                        @error('newStudentName')
                            <p class="text-xs text-error mt-1 font-cairo flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-1.5 font-cairo">البريد الإلكتروني</label>
                        <input wire:model="newStudentEmail"
                               type="email"
                               placeholder="student@example.com"
                               class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 focus:border-surface-tint/50 transition-all font-cairo outline-none"
                               dir="ltr"/>
                        @error('newStudentEmail')
                            <p class="text-xs text-error mt-1 font-cairo flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
                <div class="bg-secondary-fixed/50 rounded-xl px-4 py-3 flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-[18px] text-on-secondary-fixed-variant mt-0.5 flex-shrink-0">info</span>
                    <p class="text-xs text-on-secondary-fixed-variant font-cairo leading-relaxed">
                        سيتم إنشاء كلمة مرور عشوائية مكونة من 10 أحرف وإرسالها إلى بريد الطالب الإلكتروني.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="btn-gradient text-on-primary px-6 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-[18px] icon-filled">person_add</span>
                        <span class="font-cairo">إنشاء وإضافة الطالب</span>
                    </button>
                    <button type="button" @click="activeTab = 'students'"
                            class="px-6 py-2.5 rounded-xl font-semibold border border-outline-variant/30 text-on-surface-variant hover:bg-surface-container transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                        <span class="font-cairo">إلغاء</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Assign existing student tab --}}
    @if ($unassigned->isNotEmpty())
        <div x-show="activeTab === 'assign'" x-transition id="tab-assign" role="tabpanel">
            <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
                <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
                    <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[20px] icon-filled">transfer_within_a_station</span>
                        إسناد طالب موجود إلى هذا الفوج
                    </h2>
                    <button @click="activeTab = 'students'"
                            class="p-1.5 rounded-full hover:bg-surface-container text-on-surface-variant transition-colors">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>
                <div class="px-6 py-5">
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1.5 font-cairo">اختر الطالب</label>
                    <div class="flex gap-3 items-end">
                        <div class="flex-1">
                            <select wire:model="assignExistingId"
                                    class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 transition-all font-cairo outline-none appearance-none">
                                <option value="">— اختر طالباً —</option>
                                @foreach ($unassigned as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }} — {{ $s->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button"
                                wire:click="assignExisting"
                                :disabled="! $wire.assignExistingId"
                                class="btn-gradient text-on-primary px-5 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined text-[18px] icon-filled">add_link</span>
                            <span class="font-cairo">إسناد</span>
                        </button>
                    </div>
                    <p class="text-xs text-on-surface-variant mt-2 font-cairo">
                        هؤلاء الطلاب مسجّلون في حسابك لكن غير مُسندين لهذا الفوج.
                    </p>
                </div>
            </div>
        </div>
    @endif

</div>
