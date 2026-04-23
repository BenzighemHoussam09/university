<?php

namespace App\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $currentPassword = '';

    public string $password = '';

    public string $passwordConfirmation = '';

    public bool $nameSaved = false;

    public bool $passwordSaved = false;

    public function mount(): void
    {
        $teacher = Auth::guard('teacher')->user();
        $this->name = $teacher->name;
        $this->email = $teacher->email;
    }

    public function updateProfile(): void
    {
        $teacher = Auth::guard('teacher')->user();

        $this->validate([
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:teachers,email,'.$teacher->id,
        ]);

        $teacher->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        $this->nameSaved = true;
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $teacher = Auth::guard('teacher')->user();

        if (! Hash::check($this->currentPassword, $teacher->password)) {
            $this->addError('currentPassword', 'The current password is incorrect.');

            return;
        }

        $teacher->update(['password' => Hash::make($this->password)]);

        $this->reset(['currentPassword', 'password', 'passwordConfirmation']);
        $this->passwordSaved = true;
    }

    public function render(): View
    {
        return view('livewire.teacher.profile');
    }
}
