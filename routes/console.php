<?php

use App\Console\Commands\DispatchExamRemindersCommand;
use App\Console\Commands\FinalizeOverdueSessionsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Finalize sessions that have passed their deadline (every 15 seconds via scheduler)
Schedule::command(FinalizeOverdueSessionsCommand::class)->everyFifteenSeconds();

// Dispatch exam reminder notifications to students (every minute)
Schedule::command(DispatchExamRemindersCommand::class)->everyMinute();
