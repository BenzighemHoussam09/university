<?php

namespace App\Domain\Exam\Events;

use App\Models\ExamSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionFinalized
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly ExamSession $session,
        public readonly string $reason
    ) {}
}
