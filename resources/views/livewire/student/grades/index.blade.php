<div class="p-6 lg:p-8 space-y-6">

    <div>
        <h1 class="text-2xl font-headline font-bold text-primary">درجاتي</h1>
        <p class="text-sm text-on-surface-variant mt-1">ملخص أدائك الأكاديمي عبر جميع المواد</p>
    </div>

    @if($entries->isNotEmpty())

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-5 text-center">
                <span class="material-symbols-outlined text-primary text-3xl block mb-2 icon-filled">grade</span>
                <p class="text-3xl font-headline font-extrabold text-primary">
                    {{ number_format($entries->avg('final_grade'), 1) }}
                </p>
                <p class="text-xs text-on-surface-variant uppercase tracking-wider mt-1">المعدل العام / 20</p>
            </div>
            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-5 text-center">
                <span class="material-symbols-outlined text-secondary text-3xl block mb-2 icon-filled">library_books</span>
                <p class="text-3xl font-headline font-extrabold text-primary">{{ $entries->count() }}</p>
                <p class="text-xs text-on-surface-variant uppercase tracking-wider mt-1">عدد المواد</p>
            </div>
            @php $passCount = $entries->filter(fn($e) => ($e->final_grade ?? 0) >= 10)->count(); @endphp
            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-5 text-center">
                <span class="material-symbols-outlined text-3xl block mb-2 {{ $passCount === $entries->count() ? 'text-primary' : 'text-error' }} icon-filled"
                     >
                    {{ $passCount === $entries->count() ? 'workspace_premium' : 'warning' }}
                </span>
                <p class="text-3xl font-headline font-extrabold text-primary">{{ $passCount }}/{{ $entries->count() }}</p>
                <p class="text-xs text-on-surface-variant uppercase tracking-wider mt-1">مواد ناجحة</p>
            </div>
        </div>

        {{-- Grades Table --}}
        <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-surface-container flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[18px]">table_chart</span>
                <h2 class="font-semibold text-on-surface">تفاصيل الدرجات</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-right min-w-[580px]">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant text-xs uppercase tracking-wider font-label">
                            <th scope="col" class="px-5 py-3.5 font-semibold">المادة</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold text-center">الامتحان</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold text-center">العمل الشخصي</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold text-center">الحضور</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold text-center">المشاركة</th>
                            <th scope="col" class="px-5 py-3.5 font-semibold text-center">الدرجة الكلية</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                        @foreach($entries as $entry)
                        <tr class="hover:bg-surface-container-low/50 transition-colors">
                            <td class="px-5 py-4 font-bold text-on-surface">{{ $entry->module?->name ?? '—' }}</td>
                            <td class="px-5 py-4 text-center text-on-surface-variant text-sm">
                                {{ number_format($entry->exam_component, 2) }}
                            </td>
                            <td class="px-5 py-4 text-center text-on-surface-variant text-sm">
                                {{ number_format($entry->personal_work, 2) }}
                            </td>
                            <td class="px-5 py-4 text-center text-on-surface-variant text-sm">
                                {{ number_format($entry->attendance, 2) }}
                            </td>
                            <td class="px-5 py-4 text-center text-on-surface-variant text-sm">
                                {{ number_format($entry->participation, 2) }}
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="font-headline font-extrabold text-lg {{ ($entry->final_grade ?? 0) >= 10 ? 'text-primary' : 'text-error' }}">
                                    {{ number_format($entry->final_grade, 2) }}
                                </span>
                                <span class="text-on-surface-variant text-xs">/20</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    @else
        <div class="bg-surface-container-lowest rounded-xl p-16 text-center shadow-sm">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant/40 mb-3 block">grade</span>
            <p class="text-on-surface-variant font-semibold">لا توجد درجات بعد</p>
            <p class="text-xs text-on-surface-variant/60 mt-1">ستظهر درجاتك هنا بعد انتهاء الامتحانات وتقييمها</p>
        </div>
    @endif

</div>
