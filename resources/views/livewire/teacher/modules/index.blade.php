<div class="p-6 lg:p-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant mb-2 font-cairo">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <span class="text-on-surface font-semibold">المقاييس</span>
            </nav>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">وحداتي التعليمية</h1>
            <p class="text-on-surface-variant mt-1 text-sm font-cairo">إدارة المقاييس التعليمية الخاصة بك</p>
        </div>
    </div>

    {{-- Modules table --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
        <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
            <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px] icon-filled">list_alt</span>
                قائمة المقاييس النشطة
            </h2>
            <span class="text-sm text-on-surface-variant font-cairo bg-surface-container px-3 py-1 rounded-full">
                {{ $myModules->count() }} مقياس
            </span>
        </div>

        @if ($myModules->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <div class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-5xl text-outline-variant">library_books</span>
                </div>
                <h3 class="text-xl font-bold text-on-surface font-cairo mb-2">لا توجد مقاييس بعد</h3>
                <p class="text-on-surface-variant text-sm max-w-md font-cairo leading-relaxed">
                    ابدأ بإضافة مقياسك الأول من الكتالوج أدناه أو أنشئ مقياساً مخصصاً.
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20 bg-surface-container-lowest">
                            <th scope="col" class="px-6 py-4 font-cairo text-start">اسم المقياس</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">المصدر</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">المجموعات</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">بنك الأسئلة</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach ($myModules as $module)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-primary-fixed flex items-center justify-center rounded-lg text-on-primary-fixed-variant flex-shrink-0">
                                            <span class="material-symbols-outlined text-[20px]">menu_book</span>
                                        </div>
                                        <span class="font-bold text-on-surface font-cairo">{{ $module->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @if ($module->created_from_catalog_id)
                                        <span class="bg-secondary-fixed text-on-secondary-fixed-variant px-2.5 py-1 rounded-full text-xs font-bold font-cairo">
                                            كتالوج
                                        </span>
                                    @else
                                        <span class="bg-tertiary-fixed text-on-tertiary-fixed-variant px-2.5 py-1 rounded-full text-xs font-bold font-cairo">
                                            مخصص
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-bold text-on-surface font-cairo">{{ $module->groups_count }}</span>
                                    <span class="text-on-surface-variant text-xs font-cairo mr-1">فوج</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="inline-flex items-center gap-1.5 bg-primary/10 text-on-primary-fixed-variant px-3 py-1 rounded-full text-sm font-bold">
                                        <span class="material-symbols-outlined text-[16px]">quiz</span>
                                        <span class="font-cairo">{{ $module->questions_count }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button wire:click="remove({{ $module->id }})"
                                            wire:confirm="إزالة '{{ $module->name }}' من مقاييسك؟ ستُحذف المجموعات المرتبطة به أيضاً."
                                            class="p-2 text-error hover:bg-error-container rounded-lg transition-colors inline-flex items-center gap-1 text-sm font-bold font-cairo">
                                        <span class="material-symbols-outlined text-[18px]">delete</span>
                                        حذف
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Add from catalog --}}
        @if ($catalog->isNotEmpty())
            <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
                <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
                    <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                        <span class="material-symbols-outlined text-secondary text-[20px] icon-filled">storefront</span>
                        إضافة من الكتالوج
                    </h2>
                    <span class="text-xs text-on-surface-variant font-cairo">{{ $catalog->count() }} متاح</span>
                </div>
                <div class="divide-y divide-outline-variant/10 max-h-72 overflow-y-auto">
                    @foreach ($catalog as $item)
                        <div class="flex items-center justify-between px-6 py-3 hover:bg-surface-container-low transition-colors">
                            <div class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-[16px] text-on-surface-variant">menu_book</span>
                                <span class="text-sm text-on-surface font-cairo">{{ $item->name }}</span>
                            </div>
                            <button wire:click="addFromCatalog({{ $item->id }})"
                                    class="flex items-center gap-1 text-on-primary-fixed-variant hover:bg-primary-fixed px-3 py-1.5 rounded-lg transition-colors text-sm font-bold font-cairo">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                                إضافة
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Add custom module --}}
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
            <div class="bg-surface-container-low px-6 py-4">
                <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                    <span class="material-symbols-outlined text-tertiary text-[20px] icon-filled">add_box</span>
                    إضافة مقياس مخصص
                </h2>
            </div>
            <form wire:submit="addCustom" class="px-6 py-5 space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-on-surface-variant mb-1.5 font-cairo">اسم المقياس</label>
                    <input wire:model="newName"
                           type="text"
                           placeholder="مثال: برمجة الحاسوب..."
                           class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 focus:border-surface-tint/50 transition-all font-cairo outline-none"/>
                    @error('newName')
                        <p class="text-xs text-error mt-1.5 font-cairo flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
                <button type="submit"
                        class="btn-gradient text-on-primary w-full py-2.5 rounded-xl font-semibold shadow-ambient flex items-center justify-center gap-2 hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-[18px] icon-filled">add_circle</span>
                    <span class="font-cairo">إضافة المقياس</span>
                </button>
            </form>
        </div>

    </div>

</div>
