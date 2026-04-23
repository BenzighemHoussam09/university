<?php

namespace App\Domain\Exam\Events;

use App\Models\Exam;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExamStarted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Exam $exam) {}
}
