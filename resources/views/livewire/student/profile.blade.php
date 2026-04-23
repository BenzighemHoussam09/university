<div class="p-6 lg:p-8 max-w-2xl space-y-6">

    <div>
        <h1 class="text-2xl font-headline font-bold text-primary">الملف الشخصي</h1>
        <p class="text-sm text-on-surface-variant mt-1">إدارة بيانات حسابك الشخصي</p>
    </div>

    {{-- Profile Information --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-on-surface mb-5 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px] icon-filled">person</span>
            المعلومات الشخصية
        </h2>
        <form wire:submit="updateProfile" class="space-y-5" novalidate>
            <div class="space-y-1.5">
                <label for="name" class="block text-sm font-semibold text-on-surface-variant">الاسم الكامل</label>
                <input wire:model="name" id="name" type="text" autocomplete="name"
                       class="w-full px-4 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface placeholder:text-outline focus:ring-2 focus:ring-surface-tint transition-all @error('name') ring-2 ring-error @enderror">
                @error('name')
                    <p class="text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label class="block text-sm font-semibold text-on-surface-variant">البريد الإلكتروني</label>
                <div class="px-4 py-3 bg-surface-container-high rounded-lg text-on-surface-variant text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">mail</span>
                    {{ $email }}
                </div>
                <p class="text-xs text-on-surface-variant/60">البريد الإلكتروني لا يمكن تغييره. تواصل مع أستاذك إذا لزم الأمر.</p>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2.5 btn-gradient text-on-primary rounded-lg font-bold text-sm hover:opacity-90 transition-all active:scale-[0.98] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    حفظ التغييرات
                </button>
                @if($nameSaved)
                    <span class="text-sm text-on-primary-fixed-variant flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] icon-filled">check_circle</span>
                        تم الحفظ
                    </span>
                @endif
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
        <h2 class="font-semibold text-on-surface mb-5 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary text-[20px] icon-filled">lock</span>
            تغيير كلمة السر
        </h2>
        <form wire:submit="updatePassword" class="space-y-4" novalidate>
            <div class="space-y-1.5">
                <label for="currentPassword" class="block text-sm font-semibold text-on-surface-variant">كلمة السر الحالية</label>
                <input wire:model="currentPassword" id="currentPassword" type="password"
                       autocomplete="current-password"
                       class="w-full px-4 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface focus:ring-2 focus:ring-surface-tint transition-all @error('currentPassword') ring-2 ring-error @enderror">
                @error('currentPassword')
                    <p class="text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-semibold text-on-surface-variant">كلمة السر الجديدة</label>
                <input wire:model="password" id="password" type="password"
                       autocomplete="new-password"
                       class="w-full px-4 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface focus:ring-2 focus:ring-surface-tint transition-all @error('password') ring-2 ring-error @enderror">
                @error('password')
                    <p class="text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <div class="space-y-1.5">
                <label for="passwordConfirmation" class="block text-sm font-semibold text-on-surface-variant">تأكيد كلمة السر</label>
                <input wire:model="passwordConfirmation" id="passwordConfirmation" type="password"
                       autocomplete="new-password"
                       class="w-full px-4 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface focus:ring-2 focus:ring-surface-tint transition-all">
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="px-6 py-2.5 btn-gradient text-on-primary rounded-lg font-bold text-sm hover:opacity-90 transition-all active:scale-[0.98] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">key</span>
                    تحديث كلمة السر
                </button>
                @if($passwordSaved)
                    <span class="text-sm text-on-primary-fixed-variant flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] icon-filled">check_circle</span>
                        تم التحديث
                    </span>
                @endif
            </div>
        </form>
    </div>

</div>
