<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function mount(): void
    {
        $this->form->guard = 'teacher';
    }

    public function login(): void
    {
        $this->validate();
        $this->form->authenticate();
        Session::regenerate();
        $this->redirectIntended(default: route('teacher.dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="max-w-6xl w-full flex flex-col md:flex-row bg-surface-container-lowest rounded-2xl overflow-hidden shadow-[0_20px_40px_rgba(25,28,29,0.06)] min-h-[580px]">

    {{-- Hero / Branding --}}
    <div class="hidden md:flex md:w-5/12 relative overflow-hidden bg-primary p-12 flex-col justify-between text-on-primary">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 2px 2px, white 1px, transparent 0); background-size: 40px 40px;"></div>

        <div class="relative z-10">
            <div class="text-2xl font-extrabold font-headline mb-3">بوابة الامتحانات الجامعية</div>
            <div class="h-0.5 w-16 bg-primary-fixed mb-7"></div>
            <p class="text-base opacity-90 leading-relaxed font-body">
                منصة رقمية متكاملة لإدارة الامتحانات وتتبع نتائج الطلاب، مصممة لتلبية احتياجات الأستاذ الجزائري.
            </p>
        </div>

        <div class="relative z-10">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-white/10 rounded-xl">
                    <span class="material-symbols-outlined text-3xl icon-filled">school</span>
                </div>
                <div>
                    <div class="text-xs font-label uppercase tracking-widest opacity-60">الصفة</div>
                    <div class="text-lg font-bold">أستاذ / مؤطر</div>
                </div>
            </div>
        </div>

        <div class="absolute -bottom-20 -left-20 w-72 h-72 bg-primary-container rounded-full opacity-40 blur-3xl"></div>
    </div>

    {{-- Login Form --}}
    <div class="w-full md:w-7/12 p-8 md:p-14 flex flex-col justify-center">

        <div class="mb-9">
            <h1 class="text-2xl font-bold text-on-surface font-headline mb-1">تسجيل الدخول</h1>
            <p class="text-sm text-on-surface-variant">أدخل بيانات حسابك للوصول إلى لوحة التحكم</p>
        </div>

        {{-- Session status --}}
        @if(session('status'))
            <x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>
        @endif

        <form wire:submit="login" class="space-y-5" novalidate>

            {{-- Email --}}
            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-semibold text-on-surface-variant">
                    البريد الإلكتروني
                </label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute inset-y-0 right-3 flex items-center pointer-events-none text-outline text-[20px] h-full">mail</span>
                    <input wire:model="form.email"
                           id="email" type="email" name="email"
                           placeholder="name@univ-alger.dz"
                           required autofocus autocomplete="username"
                           class="w-full pr-11 pl-4 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface placeholder:text-outline focus:ring-2 focus:ring-surface-tint transition-all @error('form.email') ring-2 ring-error @enderror">
                </div>
                @error('form.email')
                    <p class="text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Password --}}
            <div class="space-y-1.5" x-data="{ show: false }">
                <div class="flex items-center justify-between">
                    <label for="password" class="block text-sm font-semibold text-on-surface-variant">كلمة السر</label>
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" wire:navigate
                           class="text-xs font-semibold text-primary hover:underline">
                            نسيت كلمة السر؟
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <span class="material-symbols-outlined absolute inset-y-0 right-3 flex items-center pointer-events-none text-outline text-[20px] h-full">lock</span>
                    <input wire:model="form.password"
                           id="password" :type="show ? 'text' : 'password'" name="password"
                           required autocomplete="current-password"
                           class="w-full pr-11 pl-11 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface focus:ring-2 focus:ring-surface-tint transition-all @error('form.password') ring-2 ring-error @enderror">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 left-3 flex items-center text-outline hover:text-on-surface transition-colors">
                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                    </button>
                </div>
                @error('form.password')
                    <p class="text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="flex items-center gap-3">
                <input wire:model="form.remember"
                       id="remember" type="checkbox"
                       class="w-4.5 h-4.5 rounded border-outline-variant text-primary focus:ring-surface-tint">
                <label for="remember" class="text-sm text-on-surface-variant">تذكرني على هذا الجهاز</label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full py-3.5 px-6 btn-gradient text-on-primary rounded-lg font-bold shadow-md hover:shadow-lg active:scale-[0.98] transition-all flex items-center justify-center gap-2.5">
                <span>تسجيل الدخول</span>
                <span class="material-symbols-outlined text-[20px] icon-filled">login</span>
            </button>

        </form>

        <div class="mt-8 pt-7 border-t border-surface-container-high text-center">
            <p class="text-sm text-on-surface-variant">
                <a href="{{ route('student.login') }}" wire:navigate class="font-semibold text-secondary hover:underline">
                    تسجيل الدخول كطالب
                </a>
            </p>
        </div>

    </div>
</div>
