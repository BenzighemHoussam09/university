<?php

use App\Http\Middleware\EnsureAbsenceBelowThreshold;
use App\Http\Middleware\EnsureExamNotCompleted;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/teacher.php'));
            Route::middleware('web')->group(base_path('routes/student.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(function ($request) {
            if ($request->is('student/*') || $request->routeIs('student.*')) {
                return route('student.login');
            }
            return route('teacher.login');
        });
        $middleware->alias([
            'absence.threshold' => EnsureAbsenceBelowThreshold::class,
            'exam.not_completed' => EnsureExamNotCompleted::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
