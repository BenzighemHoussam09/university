<?php

namespace Tests\Feature\Absences;

use App\Domain\Students\Actions\RecordAbsenceAction;
use App\Models\Absence;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies that the AbsenceObserver keeps `students.absence_count` and
 * `students.blocked_at` consistent with the actual absence rows.
 */
class AbsenceCounterSyncTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $teacher;

    private Student $student;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = Teacher::factory()->create();
        $this->student = Student::factory()->create([
            'teacher_id' => $this->teacher->id,
            'absence_count' => 0,
            'blocked_at' => null,
        ]);
    }

    public function test_absence_count_increments_on_create(): void
    {
        (new RecordAbsenceAction)->execute($this->student, now()->toDateString());

        $this->student->refresh();

        $this->assertSame(1, $this->student->absence_count);
    }

    public function test_absence_count_increments_correctly_for_multiple_absences(): void
    {
        $action = new RecordAbsenceAction;

        for ($i = 1; $i <= 3; $i++) {
            $action->execute($this->student, now()->subDays($i)->toDateString());
        }

        $this->student->refresh();

        $this->assertSame(3, $this->student->absence_count);
        $this->assertSame(3, Absence::where('student_id', $this->student->id)->count());
    }

    public function test_absence_count_decrements_on_delete(): void
    {
        $action = new RecordAbsenceAction;
        $absence1 = $action->execute($this->student, now()->subDays(2)->toDateString());
        $absence2 = $action->execute($this->student, now()->subDays(1)->toDateString());

        $this->student->refresh();
        $this->assertSame(2, $this->student->absence_count);

        $absence1->delete();

        $this->student->refresh();
        $this->assertSame(1, $this->student->absence_count);
    }

    public function test_blocked_at_cleared_when_count_drops_below_threshold(): void
    {
        $threshold = config('exam.absence_threshold', 5);
        $action = new RecordAbsenceAction;

        $absences = [];
        for ($i = 1; $i <= $threshold; $i++) {
            $absences[] = $action->execute($this->student, now()->subDays($i)->toDateString());
        }

        $this->student->refresh();
        $this->assertNotNull($this->student->blocked_at, 'Student should be blocked at threshold');

        // Remove one absence — should unblock
        array_pop($absences)->delete();

        $this->student->refresh();
        $this->assertNull($this->student->blocked_at, 'Student should be unblocked below threshold');
        $this->assertSame($threshold - 1, $this->student->absence_count);
    }

    public function test_absence_count_matches_actual_row_count_after_mixed_operations(): void
    {
        $action = new RecordAbsenceAction;

        $a1 = $action->execute($this->student, now()->subDays(5)->toDateString());
        $a2 = $action->execute($this->student, now()->subDays(4)->toDateString());
        $a3 = $action->execute($this->student, now()->subDays(3)->toDateString());

        $a1->delete();

        $a4 = $action->execute($this->student, now()->subDays(2)->toDateString());

        $a2->delete();

        $this->student->refresh();

        $actualCount = Absence::where('student_id', $this->student->id)->count();
        $this->assertSame($actualCount, $this->student->absence_count);
        $this->assertSame(2, $this->student->absence_count);
    }

    public function test_direct_absence_model_create_also_increments_counter(): void
    {
        Absence::create([
            'teacher_id' => $this->teacher->id,
            'student_id' => $this->student->id,
            'occurred_on' => now()->toDateString(),
        ]);

        $this->student->refresh();

        $this->assertSame(1, $this->student->absence_count);
    }
}
