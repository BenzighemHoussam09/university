<?php

namespace Tests\Feature\Monitor;

use App\Domain\Exam\Actions\EndExamAction;
use App\Domain\Exam\Actions\ExtendTimeAction;
use App\Enums\ExamStatus;
use App\Livewire\Teacher\Exams\Monitor;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Group;
use App\Models\Module;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for global and per-student time extension, and EndExamAction.
 *
 * Per tasks.md T077: global and per-student; asserts deadline recomputed correctly
 * and only targeted sessions affected.
 */
class TimeExtensionTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function makeActiveExamWithTwoSessions(): array
    {
        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
        ]);

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'status' => ExamStatus::Active,
            'started_at' => now(),
            'duration_minutes' => 60,
            'global_extra_minutes' => 0,
        ]);

        $studentA = Student::factory()->create(['teacher_id' => $teacher->id]);
        $studentB = Student::factory()->create(['teacher_id' => $teacher->id]);

        $group->students()->attach([$studentA->id, $studentB->id]);

        $sessionA = ExamSession::factory()->active()->create([
            'exam_id' => $exam->id,
            'student_id' => $studentA->id,
            'started_at' => $exam->started_at,
            'deadline' => $exam->started_at->copy()->addMinutes(60),
            'student_extra_minutes' => 0,
        ]);

        $sessionB = ExamSession::factory()->active()->create([
            'exam_id' => $exam->id,
            'student_id' => $studentB->id,
            'started_at' => $exam->started_at,
            'deadline' => $exam->started_at->copy()->addMinutes(60),
            'student_extra_minutes' => 0,
        ]);

        return [$teacher, $exam, $sessionA, $sessionB];
    }

    // ------------------------------------------------------------------
    // ExtendTimeAction::global
    // ------------------------------------------------------------------

    public function test_extend_global_adds_minutes_to_all_active_sessions(): void
    {
        [$teacher, $exam, $sessionA, $sessionB] = $this->makeActiveExamWithTwoSessions();

        $this->actingAs($teacher, 'teacher');

        app(ExtendTimeAction::class)->global($exam, 10);

        $exam->refresh();
        $sessionA->refresh();
        $sessionB->refresh();

        $this->assertEquals(10, $exam->global_extra_minutes);

        $expectedDeadline = $exam->started_at->copy()->addMinutes(70); // 60 + 10

        $this->assertEqualsWithDelta(
            $expectedDeadline->timestamp,
            $sessionA->deadline->timestamp,
            2
        );
        $this->assertEqualsWithDelta(
            $expectedDeadline->timestamp,
            $sessionB->deadline->timestamp,
            2
        );
    }

    public function test_extend_global_multiple_times_accumulates(): void
    {
        [$teacher, $exam, $sessionA, $sessionB] = $this->makeActiveExamWithTwoSessions();

        $this->actingAs($teacher, 'teacher');

        $action = app(ExtendTimeAction::class);
        $action->global($exam, 5);
        $action->global($exam, 3);

        $exam->refresh();
        $sessionA->refresh();

        $this->assertEquals(8, $exam->global_extra_minutes);

        $expectedDeadline = $exam->started_at->copy()->addMinutes(68); // 60 + 8
        $this->assertEqualsWithDelta(
            $expectedDeadline->timestamp,
            $sessionA->deadline->timestamp,
            2
        );
    }

    public function test_extend_global_does_not_affect_completed_sessions(): void
    {
        [$teacher, $exam, $sessionA, $sessionB] = $this->makeActiveExamWithTwoSessions();

        $this->actingAs($teacher, 'teacher');

        // Mark sessionB as completed before extending
        $sessionB->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        app(ExtendTimeAction::class)->global($exam, 10);

        $sessionB->refresh();

        // sessionB deadline should not change (it was completed, skipped by WHERE status='active')
        $this->assertEqualsWithDelta(
            $exam->started_at->copy()->addMinutes(60)->timestamp,
            $sessionB->deadline->timestamp,
            2
        );
    }

    // ------------------------------------------------------------------
    // ExtendTimeAction::student
    // ------------------------------------------------------------------

    public function test_extend_student_adds_minutes_only_to_target_session(): void
    {
        [$teacher, $exam, $sessionA, $sessionB] = $this->makeActiveExamWithTwoSessions();

        $this->actingAs($teacher, 'teacher');

        app(ExtendTimeAction::class)->student($sessionA, 5);

        $sessionA->refresh();
        $sessionB->refresh();

        $this->assertEquals(5, $sessionA->student_extra_minutes);
        $this->assertEquals(0, $sessionB->student_extra_minutes); // unchanged

        $expectedDeadlineA = $exam->started_at->copy()->addMinutes(65); // 60 + 5
        $expectedDeadlineB = $exam->started_at->copy()->addMinutes(60); // unchanged

        $this->assertEqualsWithDelta(
            $expectedDeadlineA->timestamp,
            $sessionA->deadline->timestamp,
            2
        );
        $this->assertEqualsWithDelta(
            $expectedDeadlineB->timestamp,
            $sessionB->deadline->timestamp,
            2
        );
    }

    public function test_extend_student_and_global_compound_correctly(): void
    {
        [$teacher, $exam, $sessionA, $sessionB] = $this->makeActiveExamWithTwoSessions();

        $this->actingAs($teacher, 'teacher');

        $extendAction = app(ExtendTimeAction::class);

        // Global +5, then student A +3
        $extendAction->global($exam, 5);
        $extendAction->student($sessionA, 3);

        $sessionA->refresh();
        $sessionB->refresh();

        // sessionA: 60 + 5 (global) + 3 (student) = 68
        $expectedA = $exam->started_at->copy()->addMinutes(68);
        // sessionB: 60 + 5 (global) = 65
        $expectedB = $exam->started_at->copy()->addMinutes(65);

        $this->assertEqualsWithDelta($expectedA->timestamp, $sessionA->deadline->timestamp, 2);
        $this->assertEqualsWithDelta($expectedB->timestamp, $sessionB->deadline->timestamp, 2);
    }

    // ------------------------------------------------------------------
    // Livewire Monitor: extendGlobal and extendStudent via component
    // ------------------------------------------------------------------

    public function test_monitor_extend_global_via_livewire(): void
    {
        [$teacher, $exam, $sessionA, $sessionB] = $this->makeActiveExamWithTwoSessions();

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Monitor::class, ['exam' => $exam])
            ->call('extendGlobal', 10);

        $exam->refresh();
        $this->assertEquals(10, $exam->global_extra_minutes);
    }

    public function test_monitor_extend_student_via_livewire(): void
    {
        [$teacher, $exam, $sessionA, $sessionB] = $this->makeActiveExamWithTwoSessions();

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Monitor::class, ['exam' => $exam])
            ->call('extendStudent', $sessionA->student_id, 7);

        $sessionA->refresh();
        $sessionB->refresh();

        $this->assertEquals(7, $sessionA->student_extra_minutes);
        $this->assertEquals(0, $sessionB->student_extra_minutes);
    }

    public function test_monitor_extend_global_ignores_zero_or_negative(): void
    {
        [$teacher, $exam, $sessionA] = $this->makeActiveExamWithTwoSessions();

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Monitor::class, ['exam' => $exam])
            ->call('extendGlobal', 0);

        $exam->refresh();
        $this->assertEquals(0, $exam->global_extra_minutes);
    }

    // ------------------------------------------------------------------
    // EndExamAction
    // ------------------------------------------------------------------

    public function test_end_exam_finalizes_all_active_sessions_and_marks_exam_ended(): void
    {
        Notification::fake();

        [$teacher, $exam, $sessionA, $sessionB] = $this->makeActiveExamWithTwoSessions();

        \DB::table('grading_templates')->insertOrIgnore([
            'id' => 1,
            'teacher_id' => null,
            'exam_max' => 12,
            'personal_work_max' => 4,
            'attendance_max' => 2,
            'participation_max' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($teacher, 'teacher');

        app(EndExamAction::class)->handle($exam);

        $exam->refresh();
        $sessionA->refresh();
        $sessionB->refresh();

        $this->assertEquals(ExamStatus::Ended, $exam->status);
        $this->assertNotNull($exam->ended_at);

        $this->assertEquals('completed', $sessionA->status);
        $this->assertEquals('completed', $sessionB->status);
    }

    public function test_end_exam_only_finalizes_active_sessions(): void
    {
        Notification::fake();

        [$teacher, $exam, $sessionA, $sessionB] = $this->makeActiveExamWithTwoSessions();

        \DB::table('grading_templates')->insertOrIgnore([
            'id' => 1, 'teacher_id' => null, 'exam_max' => 12,
            'personal_work_max' => 4, 'attendance_max' => 2, 'participation_max' => 2,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($teacher, 'teacher');

        // Manually complete sessionA before end
        $sessionA->update(['status' => 'completed', 'completed_at' => now()]);

        $originalCompletedAt = $sessionA->fresh()->completed_at;

        app(EndExamAction::class)->handle($exam);

        $exam->refresh();
        $sessionA->refresh();
        $sessionB->refresh();

        $this->assertEquals(ExamStatus::Ended, $exam->status);
        $this->assertEquals('completed', $sessionB->status); // was active, now completed
        // sessionA completed_at should not change (it was already completed)
        $this->assertEqualsWithDelta(
            $originalCompletedAt->timestamp,
            $sessionA->completed_at->timestamp,
            2
        );
    }

    public function test_monitor_end_exam_via_livewire_redirects_to_results(): void
    {
        Notification::fake();

        [$teacher, $exam, $sessionA, $sessionB] = $this->makeActiveExamWithTwoSessions();

        \DB::table('grading_templates')->insertOrIgnore([
            'id' => 1, 'teacher_id' => null, 'exam_max' => 12,
            'personal_work_max' => 4, 'attendance_max' => 2, 'participation_max' => 2,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Monitor::class, ['exam' => $exam])
            ->call('endExam')
            ->assertRedirect(route('teacher.exams.results', $exam));

        $exam->refresh();
        $this->assertEquals(ExamStatus::Ended, $exam->status);
    }
}
