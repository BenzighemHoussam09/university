<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::broker('teachers')->sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));
            return;
        }

        $this->reset('email');
        session()->flash('status', __($status));
    }
}; ?>

<div class="w-full max-w-md">
    <x-card class="p-8 md:p-10">

        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-xl bg-primary-fixed/30 flex items-center justify-center">
                <span class="material-symbols-outlined text-primary text-[22px] icon-filled">key</span>
            </div>
            <div>
                <h1 class="text-xl font-bold text-on-surface font-headline">استعادة كلمة السر</h1>
            </div>
        </div>

        <p class="text-sm text-on-surface-variant mb-7">
            أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة السر.
        </p>

        @if(session('status'))
            <x-alert type="success" class="mb-5">{{ session('status') }}</x-alert>
        @endif

        <form wire:submit="sendPasswordResetLink" class="space-y-5" novalidate>

            <div class="space-y-1.5">
                <label for="email" class="block text-sm font-semibold text-on-surface-variant">البريد الإلكتروني</label>
                <div class="relative">
                    <span class="material-symbols-outlined absolute inset-y-0 right-3 flex items-center pointer-events-none text-outline text-[20px] h-full">mail</span>
                    <input wire:model="email"
                           id="email" type="email" name="email"
                           placeholder="name@univ.dz"
                           required autofocus
                           class="w-full pr-11 pl-4 py-3 bg-surface-container-highest border-none rounded-lg text-on-surface placeholder:text-outline focus:ring-2 focus:ring-surface-tint transition-all @error('email') ring-2 ring-error @enderror">
                </div>
                @error('email')
                    <p class="text-xs text-error flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full py-3.5 px-6 btn-gradient text-on-primary rounded-lg font-bold shadow-md hover:shadow-lg active:scale-[0.98] transition-all flex items-center justify-center gap-2.5">
                <span>إرسال رابط الاستعادة</span>
                <span class="material-symbols-outlined text-[20px] icon-filled">send</span>
            </button>

        </form>

        <div class="mt-6 text-center">
            <a href="{{ route('teacher.login') }}" wire:navigate
               class="text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors flex items-center justify-center gap-1">
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                العودة إلى تسجيل الدخول
            </a>
        </div>

    </x-card>
</div>
