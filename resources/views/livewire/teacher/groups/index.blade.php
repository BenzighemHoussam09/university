<div class="p-6 lg:p-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <nav class="flex items-center gap-1.5 text-sm text-on-surface-variant mb-2 font-cairo">
                <a href="{{ route('teacher.dashboard') }}" class="hover:text-primary transition-colors">الرئيسية</a>
                <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                <span class="text-on-surface font-semibold">المجموعات</span>
            </nav>
            <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">المجموعات</h1>
            <p class="text-on-surface-variant mt-1 text-sm font-cairo">إدارة أفواج الطلاب المرتبطة بمقاييسك</p>
        </div>
        <button wire:click="$set('showForm', true)"
                class="btn-gradient text-on-primary px-5 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity self-start sm:self-auto">
            <span class="material-symbols-outlined text-[20px] icon-filled">group_add</span>
            <span class="font-cairo">فوج جديد</span>
        </button>
    </div>

    {{-- Create form --}}
    @if ($showForm)
        <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden"
             x-data x-init="$el.scrollIntoView({ behavior: 'smooth', block: 'start' })">
            <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
                <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px] icon-filled">add_circle</span>
                    إنشاء فوج جديد
                </h2>
                <button wire:click="$set('showForm', false)"
                        class="p-1.5 rounded-full hover:bg-surface-container text-on-surface-variant transition-colors">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>
            <form wire:submit="create" class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-1.5 font-cairo">اسم الفوج</label>
                        <input wire:model="name"
                               type="text"
                               placeholder="مثال: م.د.إ-1"
                               class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 focus:border-surface-tint/50 transition-all font-cairo outline-none"/>
                        @error('name')
                            <p class="text-xs text-error mt-1 font-cairo flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-1.5 font-cairo">المقياس</label>
                        <select wire:model="moduleId"
                                class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 transition-all font-cairo outline-none appearance-none">
                            <option value="">— اختر المقياس —</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module->id }}">{{ $module->name }}</option>
                            @endforeach
                        </select>
                        @error('moduleId')
                            <p class="text-xs text-error mt-1 font-cairo flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-on-surface-variant mb-1.5 font-cairo">المستوى</label>
                        <select wire:model="level"
                                class="w-full bg-surface-container border border-outline-variant/30 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-surface-tint/30 transition-all font-cairo outline-none appearance-none">
                            <option value="">— اختر المستوى —</option>
                            @foreach ($levels as $lvl)
                                <option value="{{ $lvl->value }}">{{ $lvl->value }}</option>
                            @endforeach
                        </select>
                        @error('level')
                            <p class="text-xs text-error mt-1 font-cairo flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                            class="btn-gradient text-on-primary px-6 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity">
                        <span class="material-symbols-outlined text-[18px] icon-filled">save</span>
                        <span class="font-cairo">إنشاء الفوج</span>
                    </button>
                    <button type="button" wire:click="$set('showForm', false)"
                            class="px-6 py-2.5 rounded-xl font-semibold border border-outline-variant/30 text-on-surface-variant hover:bg-surface-container transition-colors flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                        <span class="font-cairo">إلغاء</span>
                    </button>
                </div>
            </form>
        </div>
    @endif

    {{-- Groups table --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-ambient overflow-hidden">
        <div class="bg-surface-container-low px-6 py-4 flex items-center justify-between">
            <h2 class="font-bold text-on-surface font-cairo flex items-center gap-2">
                <span class="material-symbols-outlined text-primary text-[20px] icon-filled">groups</span>
                قائمة الأفواج
            </h2>
            <span class="text-sm text-on-surface-variant font-cairo bg-surface-container px-3 py-1 rounded-full">
                {{ $groups->count() }} فوج
            </span>
        </div>

        @if ($groups->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center px-6">
                <div class="w-20 h-20 bg-surface-container rounded-full flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-5xl text-outline-variant">groups</span>
                </div>
                <h3 class="text-xl font-bold text-on-surface font-cairo mb-2">لا توجد أفواج بعد</h3>
                <p class="text-on-surface-variant text-sm max-w-md font-cairo leading-relaxed mb-6">
                    أنشئ فوجك الأول لتتمكن من إضافة الطلاب وإدارة الامتحانات.
                </p>
                <button wire:click="$set('showForm', true)"
                        class="btn-gradient text-on-primary px-5 py-2.5 rounded-xl font-semibold shadow-ambient flex items-center gap-2 hover:opacity-90 transition-opacity">
                    <span class="material-symbols-outlined text-[18px] icon-filled">add_circle</span>
                    <span class="font-cairo">إنشاء فوج جديد</span>
                </button>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="text-on-surface-variant text-xs font-bold border-b border-outline-variant/20 bg-surface-container-lowest">
                            <th scope="col" class="px-6 py-4 font-cairo text-start">الفوج</th>
                            <th scope="col" class="px-6 py-4 font-cairo">المقياس</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">المستوى</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الطلاب</th>
                            <th scope="col" class="px-6 py-4 font-cairo text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        @foreach ($groups as $group)
                            <tr class="hover:bg-surface-container-low transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-secondary-fixed flex items-center justify-center rounded-lg text-on-secondary-fixed-variant flex-shrink-0">
                                            <span class="material-symbols-outlined text-[20px]">group</span>
                                        </div>
                                        <a href="{{ route('teacher.groups.show', $group) }}"
                                           class="font-bold text-on-surface hover:text-primary transition-colors font-cairo">
                                            {{ $group->name }}
                                        </a>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-on-surface-variant font-cairo text-sm">
                                    {{ $group->module->name }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="bg-primary-fixed text-on-primary-fixed-variant px-2.5 py-1 rounded-full text-xs font-bold font-cairo">
                                        {{ $group->level->value }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-bold text-on-surface font-cairo">{{ $group->students_count }}</span>
                                    <span class="text-on-surface-variant text-xs font-cairo mr-1">طالب</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('teacher.groups.show', $group) }}"
                                           class="p-2 text-on-primary-fixed-variant hover:bg-primary-fixed rounded-lg transition-colors flex items-center gap-1 text-sm font-bold font-cairo">
                                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                                            عرض
                                        </a>
                                        <button wire:click="delete({{ $group->id }})"
                                                wire:confirm="حذف الفوج '{{ $group->name }}'؟ هذا الإجراء لا يمكن التراجع عنه."
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
