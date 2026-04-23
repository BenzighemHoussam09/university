<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Redirect root based on authentication
Route::get('/', function () {
    if (auth('teacher')->check()) {
        return redirect()->route('teacher.dashboard');
    }
    if (auth('student')->check()) {
        return redirect()->route('student.dashboard');
    }

    return redirect()->route('teacher.login');
})->name('home');

// Teacher auth
Route::middleware('guest:teacher')->group(function () {
    Volt::route('/teacher/login', 'teacher.auth.login')->name('teacher.login');
    Volt::route('/forgot-password', 'pages.auth.forgot-password')->name('password.request');
    Volt::route('/reset-password/{token}', 'pages.auth.reset-password')->name('password.reset');
});

Route::post('/teacher/logout', function () {
    auth('teacher')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('teacher.login');
})->middleware('auth:teacher')->name('teacher.logout');

// Student auth
Route::middleware('guest:student')->group(function () {
    Volt::route('/student/login', 'student.auth.login')->name('student.login');
});

Route::post('/student/logout', function () {
    auth('student')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('student.login');
})->middleware('auth:student')->name('student.logout');

// Legacy profile route — serves Breeze profile components for authenticated teachers
// Will be replaced by teacher.profile route in Phase 3.
Route::view('/profile', 'profile')->middleware(['auth:teacher'])->name('profile');

// Password confirmation (used by Breeze profile components)
Route::middleware('auth:teacher')->group(function () {
    Volt::route('/confirm-password', 'pages.auth.confirm-password')->name('password.confirm');
});
