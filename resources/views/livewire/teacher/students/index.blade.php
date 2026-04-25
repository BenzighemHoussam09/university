<div class="p-6 lg:p-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant mb-2 font-cairo">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <span class="text-on-surface font-semibold">الطلاب</span>
            </nav>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">الطلاب</h1>
            <p class="text-on-surface-variant mt-1 text-sm font-cairo">جميع الطلاب المرتبطين بحسابك</p>
        </div>
    </div>

    {{-- Students table --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
        <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
            <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px] icon-filled">school</span>
                قائمة الطلاب
            </h2>
            <span class="text-sm text-on-surface-variant font-cairo bg-surface-container px-3 py-1 rounded-full">
                {{ $students->count() }} طالب
            </span>
        </div>

        @if ($students->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <div class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-5xl text-outline-variant">school</span>
                </div>
                <h3 class="text-xl font-bold text-on-surface font-cairo mb-2">لا يوجد طلاب بعد</h3>
                <p class="text-on-surface-variant text-sm max-w-md font-cairo leading-relaxed">
                    أضف طلاباً من خلال صفحة تفاصيل الفوج.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20 bg-surface-container-lowest">
                            <th scope="col" class="px-6 py-4 font-cairo text-start">الطالب</th>
                            <th scope="col" class="px-6 py-4 font-cairo">البريد الإلكتروني</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الأفواج</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الغيابات</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الحالة</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach ($students as $student)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-primary-fixed flex items-center justify-center text-on-primary-fixed-variant font-bold text-sm flex-shrink-0">
                                            {{ mb_substr($student->name, 0, 1) }}
                                        </div>
                                        <a href="{{ route('teacher.students.show', $student) }}"
                                           class="font-bold text-on-surface hover:text-primary transition-colors font-cairo">
                                            {{ $student->name }}
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant text-sm font-cairo" dir="ltr">
                                    {{ $student->email }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-bold text-on-surface font-cairo">{{ $student->groups_count }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($student->absence_count > 0)
                                        <span class="font-bold text-error font-cairo">{{ $student->absence_count }}</span>
                                    @else
                                        <span class="text-on-surface-variant font-cairo">0</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($student->blocked_at)
                                        <span class="bg-error-container text-on-error-container px-2.5 py-0.5 rounded-full text-xs font-bold font-cairo">محظور</span>
                                    @else
                                        <span class="bg-primary/10 text-primary px-2.5 py-0.5 rounded-full text-xs font-bold font-cairo">نشط</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('teacher.students.show', $student) }}"
                                           class="p-2 text-on-primary-fixed-variant hover:bg-primary-fixed rounded-lg transition-colors flex items-center gap-1 text-sm font-bold font-cairo">
                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            عرض
                                        </a>
                                        <button wire:click="delete({{ $student->id }})"
                                                wire:confirm="حذف الطالب '{{ $student->name }}'؟ هذا الإجراء لا يمكن التراجع عنه."
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
        @endif
    </div>

</div>
