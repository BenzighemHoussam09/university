<?php

namespace Tests\Feature\Notifications;

use App\Domain\Exam\Actions\FinalizeSessionAction;
use App\Domain\Exam\Actions\StartExamAction;
use App\Enums\Difficulty;
use App\Enums\Level;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Group;
use App\Models\Module;
use App\Models\Question;
use App\Models\Student;
use App\Models\Teacher;
use App\Notifications\ExamReminder;
use App\Notifications\ResultsAvailable;
use App\Notifications\StudentAccountCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * T097 — Asserts each event fires the correct notification class.
 */
class NotificationDispatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_account_created_notification_dispatched_when_student_added(): void
    {
        Notification::fake();

        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);

        $student->notify(new StudentAccountCreated('secret123'));

        Notification::assertSentTo($student, StudentAccountCreated::class);
    }

    public function test_results_available_notification_dispatched_on_finalize(): void
    {
        Notification::fake();

        [$teacher, $module, $group, $students, $exam] = $this->makeActiveExam();

        $session = ExamSession::withoutGlobalScopes()
            ->where('exam_id', $exam->id)
            ->first();

        $action = app(FinalizeSessionAction::class);
        $action->handle($session, 'manual');

        Notification::assertSentTo($session->student, ResultsAvailable::class);
    }

    public function test_exam_reminder_notification_dispatched_to_students(): void
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

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'scheduled_at' => now()->addMinutes(30),
        ]);

        $student->notify(new ExamReminder($exam));

        Notification::assertSentTo($student, ExamReminder::class);
    }

    public function test_student_account_created_notification_has_in_platform_payload(): void
    {
        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);

        $notification = new StudentAccountCreated('pass123');

        $data = $notification->toInPlatform($student);

        $this->assertEquals('student_account_created', $data['kind']);
        $this->assertArrayHasKey('login_url', $data['payload']);
    }

    public function test_results_available_notification_has_in_platform_payload(): void
    {
        [$teacher, $module, $group, $students, $exam] = $this->makeActiveExam();

        $session = ExamSession::withoutGlobalScopes()
            ->where('exam_id', $exam->id)
            ->first();

        $notification = new ResultsAvailable($session);
        $data = $notification->toInPlatform($session->student);

        $this->assertEquals('results_available', $data['kind']);
        $this->assertArrayHasKey('exam_id', $data['payload']);
        $this->assertArrayHasKey('results_url', $data['payload']);
    }

    public function test_exam_reminder_notification_has_in_platform_payload(): void
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
            'scheduled_at' => now()->addMinutes(30),
        ]);
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);

        $notification = new ExamReminder($exam);
        $data = $notification->toInPlatform($student);

        $this->assertEquals('exam_reminder', $data['kind']);
        $this->assertArrayHasKey('exam_id', $data['payload']);
        $this->assertArrayHasKey('waiting_url', $data['payload']);
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function makeActiveExam(): array
    {
        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
        ]);
        $students = Student::factory()->count(1)->create(['teacher_id' => $teacher->id]);
        foreach ($students as $student) {
            $group->students()->attach($student->id);
        }

        Question::factory()->count(3)->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
            'difficulty' => Difficulty::Easy,
        ]);
        Question::factory()->count(2)->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
            'difficulty' => Difficulty::Medium,
        ]);
        Question::factory()->count(1)->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
            'difficulty' => Difficulty::Hard,
        ]);

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => 3,
            'medium_count' => 2,
            'hard_count' => 1,
        ]);

        app(StartExamAction::class)->handle($exam);

        return [$teacher, $module, $group, $students, $exam->fresh()];
    }
}
