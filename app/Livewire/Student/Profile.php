<?php

namespace App\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.student')]
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
        $student = Auth::guard('student')->user();
        $this->name = $student->name;
        $this->email = $student->email;
    }

    public function updateProfile(): void
    {
        $student = Auth::guard('student')->user();

        $this->validate([
            'name' => 'required|string|max:120',
        ]);

        $student->update(['name' => $this->name]);

        $this->nameSaved = true;
    }

    public function updatePassword(): void
    {
        $this->validate([
            'currentPassword' => 'required|string',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $student = Auth::guard('student')->user();

        if (! Hash::check($this->currentPassword, $student->password)) {
            $this->addError('currentPassword', 'The current password is incorrect.');

            return;
        }

        $student->update(['password' => Hash::make($this->password)]);

        $this->reset(['currentPassword', 'password', 'passwordConfirmation']);
        $this->passwordSaved = true;
    }

    public function render(): View
    {
        return view('livewire.student.profile');
    }
}
