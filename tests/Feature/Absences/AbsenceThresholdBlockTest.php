<?php

namespace Tests\Feature\Absences;

use App\Models\Absence;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies FR-US7: students at or above the absence threshold are blocked from
 * all protected routes — including login redirect and direct exam URLs.
 */
class AbsenceThresholdBlockTest extends TestCase
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

    public function test_student_below_threshold_can_access_dashboard(): void
    {
        $threshold = config('exam.absence_threshold', 5);

        for ($i = 1; $i < $threshold; $i++) {
            Absence::create([
                'teacher_id' => $this->teacher->id,
                'student_id' => $this->student->id,
                'occurred_on' => now()->subDays($i)->toDateString(),
            ]);
        }

        $this->student->refresh();

        $this->assertSame($threshold - 1, $this->student->absence_count);
        $this->assertNull($this->student->blocked_at);

        $response = $this->actingAs($this->student, 'student')
            ->get(route('student.dashboard'));

        $response->assertOk();
    }

    public function test_student_at_threshold_is_blocked_on_dashboard(): void
    {
        $threshold = config('exam.absence_threshold', 5);

        for ($i = 1; $i <= $threshold; $i++) {
            Absence::create([
                'teacher_id' => $this->teacher->id,
                'student_id' => $this->student->id,
                'occurred_on' => now()->subDays($i)->toDateString(),
            ]);
        }

        $this->student->refresh();

        $this->assertSame($threshold, $this->student->absence_count);
        $this->assertNotNull($this->student->blocked_at);

        $response = $this->actingAs($this->student, 'student')
            ->get(route('student.dashboard'));

        $response->assertRedirect(route('student.login'));
    }

    public function test_blocked_student_cannot_access_direct_exam_url(): void
    {
        $threshold = config('exam.absence_threshold', 5);

        for ($i = 1; $i <= $threshold; $i++) {
            Absence::create([
                'teacher_id' => $this->teacher->id,
                'student_id' => $this->student->id,
                'occurred_on' => now()->subDays($i)->toDateString(),
            ]);
        }

        $this->student->refresh();

        // Attempt to access the exams list directly as a blocked student
        $response = $this->actingAs($this->student, 'student')
            ->get(route('student.exams.index'));

        $response->assertRedirect(route('student.login'));
    }

    public function test_blocked_student_is_set_on_threshold_not_before(): void
    {
        $threshold = config('exam.absence_threshold', 5);

        for ($i = 1; $i < $threshold; $i++) {
            Absence::create([
                'teacher_id' => $this->teacher->id,
                'student_id' => $this->student->id,
                'occurred_on' => now()->subDays($i)->toDateString(),
            ]);
        }

        $this->student->refresh();
        $this->assertNull($this->student->blocked_at, 'Should not be blocked before threshold');

        // Add the threshold absence
        Absence::create([
            'teacher_id' => $this->teacher->id,
            'student_id' => $this->student->id,
            'occurred_on' => now()->toDateString(),
        ]);

        $this->student->refresh();
        $this->assertNotNull($this->student->blocked_at, 'Should be blocked at threshold');
    }

    public function test_dashboard_shows_remaining_absences_count(): void
    {
        $threshold = config('exam.absence_threshold', 5);

        Absence::create([
            'teacher_id' => $this->teacher->id,
            'student_id' => $this->student->id,
            'occurred_on' => now()->toDateString(),
        ]);

        $this->student->refresh();

        $response = $this->actingAs($this->student, 'student')
            ->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSee($threshold - 1 .' remaining before block');
    }
}
