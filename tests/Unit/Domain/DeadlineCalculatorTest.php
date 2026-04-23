<?php

namespace Tests\Unit\Domain;

use App\Domain\Exam\Services\DeadlineCalculator;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DeadlineCalculatorTest extends TestCase
{
    private DeadlineCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new DeadlineCalculator;
    }

    public function test_baseline_deadline_is_started_at_plus_duration(): void
    {
        $startedAt = Carbon::parse('2025-06-01 09:00:00');

        $exam = new Exam([
            'duration_minutes' => 60,
            'global_extra_minutes' => 0,
        ]);

        $session = new ExamSession([
            'started_at' => $startedAt,
            'student_extra_minutes' => 0,
        ]);
        $session->setRelation('exam', $exam);

        $deadline = $this->calculator->for($session);

        $this->assertEquals($startedAt->copy()->addMinutes(60), $deadline);
    }

    public function test_global_extra_minutes_are_added(): void
    {
        $startedAt = Carbon::parse('2025-06-01 09:00:00');

        $exam = new Exam([
            'duration_minutes' => 60,
            'global_extra_minutes' => 10,
        ]);

        $session = new ExamSession([
            'started_at' => $startedAt,
            'student_extra_minutes' => 0,
        ]);
        $session->setRelation('exam', $exam);

        $deadline = $this->calculator->for($session);

        $this->assertEquals($startedAt->copy()->addMinutes(70), $deadline);
    }

    public function test_student_extra_minutes_are_added(): void
    {
        $startedAt = Carbon::parse('2025-06-01 09:00:00');

        $exam = new Exam([
            'duration_minutes' => 60,
            'global_extra_minutes' => 0,
        ]);

        $session = new ExamSession([
            'started_at' => $startedAt,
            'student_extra_minutes' => 5,
        ]);
        $session->setRelation('exam', $exam);

        $deadline = $this->calculator->for($session);

        $this->assertEquals($startedAt->copy()->addMinutes(65), $deadline);
    }

    public function test_all_extras_combined(): void
    {
        $startedAt = Carbon::parse('2025-06-01 09:00:00');

        $exam = new Exam([
            'duration_minutes' => 45,
            'global_extra_minutes' => 15,
        ]);

        $session = new ExamSession([
            'started_at' => $startedAt,
            'student_extra_minutes' => 5,
        ]);
        $session->setRelation('exam', $exam);

        $deadline = $this->calculator->for($session);

        // 45 + 15 + 5 = 65 minutes total
        $this->assertEquals($startedAt->copy()->addMinutes(65), $deadline);
    }

    public function test_zero_duration_returns_started_at(): void
    {
        $startedAt = Carbon::parse('2025-06-01 09:00:00');

        $exam = new Exam([
            'duration_minutes' => 0,
            'global_extra_minutes' => 0,
        ]);

        $session = new ExamSession([
            'started_at' => $startedAt,
            'student_extra_minutes' => 0,
        ]);
        $session->setRelation('exam', $exam);

        $deadline = $this->calculator->for($session);

        $this->assertEquals($startedAt, $deadline);
    }
}
