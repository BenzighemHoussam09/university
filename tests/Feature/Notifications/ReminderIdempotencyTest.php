<?php

namespace Tests\Feature\Notifications;

use App\Console\Commands\DispatchExamRemindersCommand;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Module;
use App\Models\Student;
use App\Models\Teacher;
use App\Notifications\ExamReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * T098 — Reminder command runs twice, asserts only one dispatch per exam
 * via reminders_sent_at idempotency guard.
 */
class ReminderIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_reminder_dispatched_only_once_per_exam(): void
    {
        Notification::fake();

        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
        ]);
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);
        $group->students()->attach($student->id);

        // Create exam scheduled exactly 30 minutes from now (within the reminder window)
        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'scheduled_at' => now()->addMinutes(30),
            'reminders_sent_at' => null,
        ]);

        // Run the reminder command twice
        $this->artisan(DispatchExamRemindersCommand::class)->assertSuccessful();
        $this->artisan(DispatchExamRemindersCommand::class)->assertSuccessful();

        // Reminder should have been dispatched to the student only once
        Notification::assertSentToTimes($student, ExamReminder::class, 1);
    }

    public function test_reminders_sent_at_is_set_after_dispatch(): void
    {
        Notification::fake();

        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
        ]);
        Student::factory()->create(['teacher_id' => $teacher->id]);

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'scheduled_at' => now()->addMinutes(30),
            'reminders_sent_at' => null,
        ]);

        $this->assertNull($exam->reminders_sent_at);

        $this->artisan(DispatchExamRemindersCommand::class)->assertSuccessful();

        $this->assertNotNull($exam->fresh()->reminders_sent_at);
    }

    public function test_exam_outside_reminder_window_is_not_notified(): void
    {
        Notification::fake();

        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
        ]);
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);
        $group->students()->attach($student->id);

        // Exam in 2 hours — outside the 30-minute window
        Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'scheduled_at' => now()->addHours(2),
            'reminders_sent_at' => null,
        ]);

        $this->artisan(DispatchExamRemindersCommand::class)->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_already_reminded_exam_is_skipped(): void
    {
        Notification::fake();

        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
        ]);
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);
        $group->students()->attach($student->id);

        // Exam in the window but already reminded
        Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'scheduled_at' => now()->addMinutes(30),
            'reminders_sent_at' => now()->subMinutes(5),
        ]);

        $this->artisan(DispatchExamRemindersCommand::class)->assertSuccessful();

        Notification::assertNothingSent();
    }
}
