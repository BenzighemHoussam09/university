<?php

use App\Livewire\Teacher\Dashboard;
use App\Livewire\Teacher\Exams\Monitor;
use App\Livewire\Teacher\Exams\Results;
use App\Livewire\Teacher\Groups\Show;
use App\Livewire\Teacher\Notifications\Index;
use App\Livewire\Teacher\Profile;
use App\Livewire\Teacher\Questions\Create;
use App\Livewire\Teacher\Questions\Edit;
use App\Livewire\Teacher\Settings;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:teacher'])->group(function () {
    Route::get('/teacher/dashboard', Dashboard::class)->name('teacher.dashboard');
    Route::get('/teacher/profile', Profile::class)->name('teacher.profile');
    Route::get('/teacher/settings', Settings::class)->name('teacher.settings');
    Route::get('/teacher/notifications', Index::class)->name('teacher.notifications');
    Route::get('/teacher/modules', App\Livewire\Teacher\Modules\Index::class)->name('teacher.modules');
    Route::get('/teacher/groups', App\Livewire\Teacher\Groups\Index::class)->name('teacher.groups.index');
    Route::get('/teacher/groups/{group}', Show::class)->name('teacher.groups.show');
    Route::get('/teacher/students/{student}', App\Livewire\Teacher\Students\Show::class)->name('teacher.students.show');
    Route::get('/teacher/questions', App\Livewire\Teacher\Questions\Index::class)->name('teacher.questions.index');
    Route::get('/teacher/questions/create', Create::class)->name('teacher.questions.create');
    Route::get('/teacher/questions/{question}/edit', Edit::class)->name('teacher.questions.edit');
    Route::get('/teacher/exams', App\Livewire\Teacher\Exams\Index::class)->name('teacher.exams.index');
    Route::get('/teacher/exams/create', App\Livewire\Teacher\Exams\Create::class)->name('teacher.exams.create');
    Route::get('/teacher/exams/{exam}', App\Livewire\Teacher\Exams\Show::class)->name('teacher.exams.show');
    Route::get('/teacher/exams/{exam}/monitor', Monitor::class)->name('teacher.exams.monitor');
    Route::get('/teacher/exams/{exam}/results', Results::class)->name('teacher.exams.results');
    Route::get('/teacher/grades/{group}', App\Livewire\Teacher\Grades\Show::class)->name('teacher.grades.show');
});
