<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->string('email');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker('teachers')->reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));
            return;
        }

        Session::flash('status', __($status));
        $this->redirectRoute('teacher.login', navigate: true);
    }
}; ?>

<div class="w-full max-w-md">
    <x-card class="p-8 md:p-10">

        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 rounded-xl bg-primary-fixed/30 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary text-[22px] icon-filled">lock_reset</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-on-surface font-headline">تعيين كلمة سر جديدة</h1>
                <p class="text-xs text-on-surface-variant">أدخل كلمة السر الجديدة لحسابك</p>
            </div>
        </div>

        <form wire:submit="resetPassword" class="space-y-5" novalidate>

            {{-- Email --}}
            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-semibold text-on-surface-variant">البريد الإلكتروني</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute inset-y-0 right-3 flex items-center pointer-events-none text-outline text-[20px] h-full">mail</span>
                    <input wire:model="email"
                           id="email" type="email" name="email"
                           required autofocus autocomplete="username"
                           class="w-full pr-11 pl-4 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface focus:ring-2 focus:ring-surface-tint transition-all @error('email') ring-2 ring-error @enderror">
                </div>
                @error('email')
                    <p class="text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- New password --}}
            <div class="space-y-1.5" x-data="{ show: false }">
                <label for="password" class="block text-sm font-semibold text-on-surface-variant">كلمة السر الجديدة</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute inset-y-0 right-3 flex items-center pointer-events-none text-outline text-[20px] h-full">lock</span>
                    <input wire:model="password"
                           id="password" :type="show ? 'text' : 'password'" name="password"
                           required autocomplete="new-password"
                           class="w-full pr-11 pl-11 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface focus:ring-2 focus:ring-surface-tint transition-all @error('password') ring-2 ring-error @enderror">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 left-3 flex items-center text-outline hover:text-on-surface transition-colors">
                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                    </button>
                </div>
                @error('password')
                    <p class="text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Confirm password --}}
            <div class="space-y-1.5" x-data="{ show: false }">
                <label for="password_confirmation" class="block text-sm font-semibold text-on-surface-variant">تأكيد كلمة السر</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute inset-y-0 right-3 flex items-center pointer-events-none text-outline text-[20px] h-full">lock_clock</span>
                    <input wire:model="password_confirmation"
                           id="password_confirmation" :type="show ? 'text' : 'password'" name="password_confirmation"
                           required autocomplete="new-password"
                           class="w-full pr-11 pl-11 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface focus:ring-2 focus:ring-surface-tint transition-all @error('password_confirmation') ring-2 ring-error @enderror">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 left-3 flex items-center text-outline hover:text-on-surface transition-colors">
                        <span class="material-symbols-outlined text-[20px]" x-text="show ? 'visibility_off' : 'visibility'">visibility</span>
                    </button>
                </div>
                @error('password_confirmation')
                    <p class="text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full py-3.5 px-6 btn-gradient text-on-primary rounded-lg font-bold shadow-md hover:shadow-lg active:scale-[0.98] transition-all flex items-center justify-center gap-2.5 mt-2">
                <span>تعيين كلمة السر</span>
                <span class="material-symbols-outlined text-[20px] icon-filled">check_circle</span>
            </button>

        </form>

    </x-card>
</div>
