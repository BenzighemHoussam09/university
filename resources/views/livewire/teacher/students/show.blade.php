<div class="p-6 lg:p-8 space-y-6" x-data="{ activeTab: 'profile' }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant mb-2 font-cairo">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <a href="{{ route('teacher.groups.index') }}" class="hover:text-primary transition-colors">المجموعات</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <span class="text-on-surface font-semibold">{{ $student->name }}</span>
            </nav>
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-full bg-primary-fixed flex items-center justify-center text-on-primary-fixed-variant font-bold text-xl flex-shrink-0">
                    {{ mb_substr($student->name, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">{{ $student->name }}</h1>
                    <div class="flex items-center gap-2 mt-1 flex-wrap">
                        <span class="text-on-surface-variant text-sm font-cairo" dir="ltr">{{ $student->email }}</span>
                        @if ($student->blocked_at)
                            <span class="bg-error-container text-on-error-container px-2.5 py-0.5 rounded-full text-xs font-bold font-cairo flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">block</span>
                                محظور
                            </span>
                        @else
                            <span class="bg-primary/10 text-on-primary-fixed-variant px-2.5 py-0.5 rounded-full text-xs font-bold font-cairo flex items-center gap-1">
                                <span class="material-symbols-outlined text-[12px]">check_circle</span>
                                نشط
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tab bar --}}
    <div class="flex gap-1 bg-surface-container rounded-xl p-1 w-fit flex-wrap"
         role="tablist" aria-label="أقسام ملف الطالب">
        <button @click="activeTab = 'profile'"
                :class="activeTab === 'profile' ? 'bg-surface-container-lowest text-primary shadow-sm font-bold' : 'text-on-surface-variant hover:text-on-surface'"
                class="px-4 py-2 rounded-lg text-sm transition-all font-cairo flex items-center gap-1.5"
                role="tab" :aria-selected="activeTab === 'profile'" aria-controls="stab-profile">
            <span class="material-symbols-outlined text-[18px]"
                  :class="activeTab === 'profile' ? 'icon-filled' : ''">person</span>
            الملف الشخصي
        </button>
        <button @click="activeTab = 'groups'"
                :class="activeTab === 'groups' ? 'bg-surface-container-lowest text-primary shadow-sm font-bold' : 'text-on-surface-variant hover:text-on-surface'"
                class="px-4 py-2 rounded-lg text-sm transition-all font-cairo flex items-center gap-1.5"
                role="tab" :aria-selected="activeTab === 'groups'" aria-controls="stab-groups">
            <span class="material-symbols-outlined text-[18px]"
                  :class="activeTab === 'groups' ? 'icon-filled' : ''">groups</span>
            الأفواج
            @if ($groups->isNotEmpty())
                <span class="bg-primary/10 text-primary px-1.5 py-0.5 rounded-full text-[10px] font-bold">{{ $groups->count() }}</span>
            @endif
        </button>
        <button @click="activeTab = 'absences'"
                :class="activeTab === 'absences' ? 'bg-surface-container-lowest text-primary shadow-sm font-bold' : 'text-on-surface-variant hover:text-on-surface'"
                class="px-4 py-2 rounded-lg text-sm transition-all font-cairo flex items-center gap-1.5"
                role="tab" :aria-selected="activeTab === 'absences'" aria-controls="stab-absences">
            <span class="material-symbols-outlined text-[18px]"
                  :class="activeTab === 'absences' ? 'icon-filled' : ''">event_busy</span>
            الغيابات
            @if ($student->absence_count > 0)
                <span class="bg-error/10 text-error px-1.5 py-0.5 rounded-full text-[10px] font-bold">{{ $student->absence_count }}</span>
            @endif
        </button>
        <button @click="activeTab = 'grades'"
                :class="activeTab === 'grades' ? 'bg-surface-container-lowest text-primary shadow-sm font-bold' : 'text-on-surface-variant hover:text-on-surface'"
                class="px-4 py-2 rounded-lg text-sm transition-all font-cairo flex items-center gap-1.5"
                role="tab" :aria-selected="activeTab === 'grades'" aria-controls="stab-grades">
            <span class="material-symbols-outlined text-[18px]"
                  :class="activeTab === 'grades' ? 'icon-filled' : ''">grade</span>
            الدرجات
        </button>
    </div>

    {{-- Profile tab --}}
    <div x-show="activeTab === 'profile'" x-transition id="stab-profile" role="tabpanel">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient">
                <div class="flex justify-between items-start mb-3">
                    <div class="p-2.5 bg-primary-fixed rounded-lg text-on-primary-fixed-variant">
                        <span class="material-symbols-outlined text-[20px]">badge</span>
                    </div>
                </div>
                <div class="text-xs text-on-surface-variant font-cairo mb-1">الاسم الكامل</div>
                <div class="font-bold text-on-surface font-cairo">{{ $student->name }}</div>
            </div>
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient">
                <div class="flex justify-between items-start mb-3">
                    <div class="p-2.5 bg-secondary-fixed rounded-lg text-on-secondary-fixed-variant">
                        <span class="material-symbols-outlined text-[20px]">mail</span>
                    </div>
                </div>
                <div class="text-xs text-on-surface-variant font-cairo mb-1">البريد الإلكتروني</div>
                <div class="font-bold text-on-surface text-sm" dir="ltr">{{ $student->email }}</div>
            </div>
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient">
                <div class="flex justify-between items-start mb-3">
                    <div class="p-2.5 {{ $student->absence_count > 0 ? 'bg-error-container' : 'bg-tertiary-fixed' }} rounded-lg {{ $student->absence_count > 0 ? 'text-on-error-container' : 'text-on-tertiary-fixed-variant' }}">
                        <span class="material-symbols-outlined text-[20px]">event_busy</span>
                    </div>
                </div>
                <div class="text-xs text-on-surface-variant font-cairo mb-1">عدد الغيابات</div>
                <div class="text-3xl font-bold font-headline {{ $student->absence_count > 0 ? 'text-error' : 'text-primary' }}">{{ $student->absence_count }}</div>
            </div>
            <div class="bg-surface-container-lowest rounded-xl p-5 shadow-ambient">
                <div class="flex justify-between items-start mb-3">
                    <div class="p-2.5 {{ $student->blocked_at ? 'bg-error-container' : 'bg-primary-fixed' }} rounded-lg {{ $student->blocked_at ? 'text-on-error-container' : 'text-on-primary-fixed-variant' }}">
                        <span class="material-symbols-outlined text-[20px]">{{ $student->blocked_at ? 'block' : 'check_circle' }}</span>
                    </div>
                </div>
                <div class="text-xs text-on-surface-variant font-cairo mb-1">الحالة</div>
                @if ($student->blocked_at)
                    <div class="font-bold text-error font-cairo text-sm">محظور</div>
                    <div class="text-xs text-on-surface-variant font-cairo">منذ {{ $student->blocked_at->format('d/m/Y') }}</div>
                @else
                    <div class="font-bold text-primary font-cairo">نشط</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Groups tab --}}
    <div x-show="activeTab === 'groups'" x-transition id="stab-groups" role="tabpanel">
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
            <div class="bg-surface-container-low px-6 py-4">
                <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px] icon-filled">groups</span>
                    الأفواج المسجّل فيها
                </h2>
            </div>
            @if ($groups->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">group_off</span>
                    <p class="text-on-surface-variant text-sm font-cairo">لم يُسنَد هذا الطالب لأي فوج بعد.</p>
                </div>
            @else
                <div class="divide-y divide-outline-variant/10">
                    @foreach ($groups as $group)
                        <div class="flex items-center justify-between px-6 py-4 hover:bg-surface-container-low transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-secondary-fixed flex items-center justify-center rounded-lg text-on-secondary-fixed-variant flex-shrink-0">
                                    <span class="material-symbols-outlined text-[20px]">group</span>
                                </div>
                                <div>
                                    <a href="{{ route('teacher.groups.show', $group) }}"
                                       class="font-semibold text-on-surface hover:text-primary transition-colors font-cairo">
                                        {{ $group->name }}
                                    </a>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-xs text-on-surface-variant font-cairo">{{ $group->module->name }}</span>
                                        <span class="bg-primary-fixed text-on-primary-fixed-variant px-2 py-0.5 rounded-full text-[10px] font-bold font-cairo">{{ $group->level->value }}</span>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('teacher.groups.show', $group) }}"
                               class="p-2 text-on-primary-fixed-variant hover:bg-primary-fixed rounded-lg transition-colors">
                                <span class="material-symbols-outlined text-[18px]">arrow_back_ios</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Absences tab --}}
    <div x-show="activeTab === 'absences'" x-transition id="stab-absences" role="tabpanel">
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
            <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px] icon-filled">event_busy</span>
                    سجل الغيابات
                    <span class="text-sm font-normal text-on-surface-variant font-cairo">({{ $student->absence_count }} غياب)</span>
                </h2>
            </div>

            {{-- Add absence form --}}
            <div class="px-6 py-4 border-b border-outline-variant/10 bg-surface-container-low/50">
                <form wire:submit="addAbsence" class="flex items-end gap-3 flex-wrap">
                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-1.5 font-cairo">تاريخ الغياب</label>
                        <input type="date"
                               wire:model="absenceDate"
                               max="{{ now()->toDateString() }}"
                               class="bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 focus:border-surface-tint/50 transition-all outline-none"
                               dir="ltr"/>
                        @error('absenceDate')
                            <p class="text-xs text-error mt-1 font-cairo flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <button type="submit"
                            class="btn-gradient text-on-primary px-5 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-[18px] icon-filled">add_circle</span>
                        <span class="font-cairo">تسجيل غياب</span>
                    </button>
                </form>
            </div>

            {{-- Absence history --}}
            @if ($absences->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">event_available</span>
                    <p class="text-on-surface-variant text-sm font-cairo">لا توجد غيابات مسجّلة لهذا الطالب.</p>
                </div>
            @else
                <div class="divide-y divide-outline-variant/10">
                    @foreach ($absences as $absence)
                        <div class="flex items-center justify-between px-6 py-3 hover:bg-surface-container-low transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-error-container flex items-center justify-center rounded-lg text-on-error-container flex-shrink-0">
                                    <span class="material-symbols-outlined text-[16px]">event_busy</span>
                                </div>
                                <span class="text-sm text-on-surface font-cairo">
                                    {{ $absence->occurred_on->format('d/m/Y') }}
                                </span>
                            </div>
                            <button wire:click="deleteAbsence({{ $absence->id }})"
                                    wire:confirm="حذف هذا السجل؟"
                                    class="p-2 text-error hover:bg-error-container rounded-lg transition-colors">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Grades tab --}}
    <div x-show="activeTab === 'grades'" x-transition id="stab-grades" role="tabpanel">
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
            <div class="bg-surface-container-low px-6 py-4">
                <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px] icon-filled">grade</span>
                    الدرجات
                </h2>
            </div>
            @if ($grades->isEmpty())
                <div class="flex flex-col items-center justify-center py-12 text-center px-6">
                    <span class="material-symbols-outlined text-5xl text-outline-variant mb-3">analytics</span>
                    <p class="text-on-surface-variant text-sm font-cairo">لا توجد درجات مسجّلة بعد.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20 bg-surface-container-lowest">
                                <th scope="col" class="px-6 py-4 font-cairo text-start">المقياس</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">الامتحان</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">العمل الشخصي</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">الحضور</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">المشاركة</th>
                                <th scope="col" class="px-6 py-4 font-cairo text-center">النهائي</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            @foreach ($grades as $grade)
                                <tr class="hover:bg-surface-container-low transition-colors">
                                    <td class="px-6 py-4 font-bold text-on-surface font-cairo">{{ $grade->module_name }}</td>
                                    <td class="px-6 py-4 text-center text-on-surface-variant font-cairo">{{ $grade->exam_component ?? '—' }}</td>
                                    <td class="px-6 py-4 text-center text-on-surface-variant font-cairo">{{ $grade->personal_work ?? '—' }}</td>
                                    <td class="px-6 py-4 text-center text-on-surface-variant font-cairo">{{ $grade->attendance ?? '—' }}</td>
                                    <td class="px-6 py-4 text-center text-on-surface-variant font-cairo">{{ $grade->participation ?? '—' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if ($grade->final_grade !== null)
                                            <span class="font-bold text-primary font-headline text-lg">{{ $grade->final_grade }}</span>
                                            <span class="text-xs text-on-surface-variant font-cairo">/20</span>
                                        @else
                                            <span class="text-on-surface-variant font-cairo">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
