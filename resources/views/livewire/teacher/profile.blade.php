<div class="p-6 lg:p-8 max-w-2xl space-y-6">

    {{-- Page heading --}}
    <div>
        <h1 class="text-3xl font-extrabold text-primary font-headline tracking-tight">الملف الشخصي</h1>
        <p class="text-on-surface-variant mt-1 text-sm">إدارة معلومات حسابك وكلمة المرور.</p>
    </div>

    {{-- Profile information --}}
    <div class="bg-surface-container-lowest rounded-xl p-6 shadow-ambient">
        <h2 class="text-base font-bold text-on-surface mb-5 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">person</span>
            معلومات الحساب
        </h2>

        <form wire:submit="updateProfile" class="space-y-5">

            <div>
                <label for="name" class="block text-sm font-semibold text-on-surface mb-1.5">
                    الاسم الكامل
                </label>
                <input
                    wire:model="name"
                    id="name"
                    type="text"
                    autocomplete="name"
                    class="w-full bg-surface-container rounded-lg border border-outline-variant/40 px-4 py-2.5 text-sm text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-surface-tint focus:border-transparent transition-all
                           @error('name') border-error focus:ring-error/30 @enderror"
                >
                @error('name')
                    <p class="mt-1.5 text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-on-surface mb-1.5">
                    البريد الإلكتروني
                </label>
                <input
                    wire:model="email"
                    id="email"
                    type="email"
                    autocomplete="email"
                    class="w-full bg-surface-container rounded-lg border border-outline-variant/40 px-4 py-2.5 text-sm text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-surface-tint focus:border-transparent transition-all
                           @error('email') border-error focus:ring-error/30 @enderror"
                >
                @error('email')
                    <p class="mt-1.5 text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="btn-gradient text-on-primary px-5 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 transition-opacity">
                    حفظ المعلومات
                </button>
                @if ($nameSaved)
                    <span class="flex items-center gap-1 text-sm text-primary font-medium"
                          x-data x-init="setTimeout(() => $el.remove(), 3000)">
                        <span class="material-symbols-outlined text-[16px] icon-filled">check_circle</span>
                        تم الحفظ بنجاح
                    </span>
                @endif
            </div>

        </form>
    </div>

    {{-- Change password --}}
    <div class="bg-surface-container-lowest rounded-xl p-6 shadow-ambient">
        <h2 class="text-base font-bold text-on-surface mb-5 flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px] text-on-surface-variant">lock</span>
            تغيير كلمة المرور
        </h2>

        <form wire:submit="updatePassword" class="space-y-5">

            <div>
                <label for="currentPassword" class="block text-sm font-semibold text-on-surface mb-1.5">
                    كلمة المرور الحالية
                </label>
                <input
                    wire:model="currentPassword"
                    id="currentPassword"
                    type="password"
                    autocomplete="current-password"
                    class="w-full bg-surface-container rounded-lg border border-outline-variant/40 px-4 py-2.5 text-sm text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-surface-tint focus:border-transparent transition-all
                           @error('currentPassword') border-error focus:ring-error/30 @enderror"
                >
                @error('currentPassword')
                    <p class="mt-1.5 text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-on-surface mb-1.5">
                    كلمة المرور الجديدة
                </label>
                <input
                    wire:model="password"
                    id="password"
                    type="password"
                    autocomplete="new-password"
                    class="w-full bg-surface-container rounded-lg border border-outline-variant/40 px-4 py-2.5 text-sm text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-surface-tint focus:border-transparent transition-all
                           @error('password') border-error focus:ring-error/30 @enderror"
                >
                @error('password')
                    <p class="mt-1.5 text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div>
                <label for="passwordConfirmation" class="block text-sm font-semibold text-on-surface mb-1.5">
                    تأكيد كلمة المرور
                </label>
                <input
                    wire:model="passwordConfirmation"
                    id="passwordConfirmation"
                    type="password"
                    autocomplete="new-password"
                    class="w-full bg-surface-container rounded-lg border border-outline-variant/40 px-4 py-2.5 text-sm text-on-surface
                           focus:outline-none focus:ring-2 focus:ring-surface-tint focus:border-transparent transition-all"
                >
            </div>

            <div class="flex items-center gap-3 pt-1">
                <button type="submit"
                        class="btn-gradient text-on-primary px-5 py-2.5 rounded-lg text-sm font-semibold hover:opacity-90 transition-opacity">
                    تحديث كلمة المرور
                </button>
                @if ($passwordSaved)
                    <span class="flex items-center gap-1 text-sm text-primary font-medium"
                          x-data x-init="setTimeout(() => $el.remove(), 3000)">
                        <span class="material-symbols-outlined text-[16px] icon-filled">check_circle</span>
                        تم التحديث
                    </span>
                @endif
            </div>

        </form>
    </div>

</div>
