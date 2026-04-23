<?php

use App\Livewire\Student\Dashboard;
use App\Livewire\Student\Exams\Results;
use App\Livewire\Student\Exams\Session;
use App\Livewire\Student\Exams\Waiting;
use App\Livewire\Student\Notifications\Index;
use App\Livewire\Student\Profile;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:student', 'absence.threshold'])->group(function () {
    Route::get('/student/dashboard', Dashboard::class)->name('student.dashboard');
    Route::get('/student/profile', Profile::class)->name('student.profile');
    Route::get('/student/notifications', Index::class)->name('student.notifications');
    Route::get('/student/exams', App\Livewire\Student\Exams\Index::class)->name('student.exams.index');
    Route::get('/student/exams/{exam}/waiting', Waiting::class)->name('student.exams.waiting');
    Route::get('/student/exams/{exam}/session', Session::class)
        ->middleware('exam.not_completed')
        ->name('student.exams.session');
    Route::get('/student/exams/{exam}/results', Results::class)->name('student.exams.results');
    Route::get('/student/grades', App\Livewire\Student\Grades\Index::class)->name('student.grades');
});
