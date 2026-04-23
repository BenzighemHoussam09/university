<?php

namespace Tests\Feature\Exam;

use App\Domain\Exam\Actions\StartExamAction;
use App\Enums\Difficulty;
use App\Enums\ExamStatus;
use App\Enums\Level;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Group;
use App\Models\Module;
use App\Models\Question;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Verifies that sessions past their deadline are finalized by the
 * FinalizeOverdueSessionsCommand.
 *
 * Freezes time past deadline, runs command, asserts drafts→final and session completed.
 */
class FinalizeOnDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_overdue_sessions_are_finalized_by_command(): void
    {
        Notification::fake();

        \DB::table('grading_templates')->insertOrIgnore([
            'id' => 1, 'teacher_id' => null, 'exam_max' => 12,
            'personal_work_max' => 4, 'attendance_max' => 2, 'participation_max' => 2,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
        ]);

        $student = Student::factory()->create(['teacher_id' => $teacher->id]);
        $group->students()->attach($student->id);

        Question::factory()->count(3)->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
            'difficulty' => Difficulty::Easy,
        ]);

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => 3,
            'medium_count' => 0,
            'hard_count' => 0,
            'duration_minutes' => 60,
            'status' => ExamStatus::Scheduled,
        ]);

        $this->actingAs($teacher, 'teacher');
        app(StartExamAction::class)->handle($exam);

        // Retrieve the session
        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        $this->assertEquals('active', $session->status);

        // Fast-forward time past the deadline
        Carbon::setTestNow($session->deadline->addMinutes(5));

        // Run the finalize command
        Artisan::call('app:finalize-overdue-sessions');

        $session->refresh();

        $this->assertEquals('completed', $session->status, 'Session should be completed after deadline.');
        $this->assertNotNull($session->completed_at);
        $this->assertNotNull($session->exam_score_raw);

        // All answers (draft) should now be final
        $draftCount = \DB::table('student_answers')
            ->where('exam_session_id', $session->id)
            ->where('status', 'draft')
            ->count();

        $this->assertEquals(0, $draftCount, 'No draft answers should remain after finalization.');

        Carbon::setTestNow();
    }

    public function test_command_does_not_finalize_sessions_before_deadline(): void
    {
        Notification::fake();

        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
        ]);

        $student = Student::factory()->create(['teacher_id' => $teacher->id]);
        $group->students()->attach($student->id);

        Question::factory()->count(2)->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
            'difficulty' => Difficulty::Easy,
        ]);

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => 2,
            'medium_count' => 0,
            'hard_count' => 0,
            'duration_minutes' => 60,
            'status' => ExamStatus::Scheduled,
        ]);

        $this->actingAs($teacher, 'teacher');
        app(StartExamAction::class)->handle($exam);

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        // Command runs while session is still live
        Artisan::call('app:finalize-overdue-sessions');

        $session->refresh();

        $this->assertEquals('active', $session->status, 'Session before deadline must stay active.');
    }
}
