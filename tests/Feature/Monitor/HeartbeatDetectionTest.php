<?php

namespace Tests\Feature\Monitor;

use App\Domain\Exam\Services\HeartbeatMonitor;
use App\Enums\ExamStatus;
use App\Livewire\Teacher\Exams\Monitor;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Group;
use App\Models\Module;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests that the heartbeat detection logic correctly identifies connected and
 * disconnected students.
 *
 * Per tasks.md T076: sets last_heartbeat_at > window ago, refreshes monitor,
 * asserts disconnected state.
 */
class HeartbeatDetectionTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function makeActiveExamWithSessions(int $studentCount = 2): array
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
            'started_at' => now()->subMinutes(10),
        ]);

        $sessions = collect();
        for ($i = 0; $i < $studentCount; $i++) {
            $student = Student::factory()->create(['teacher_id' => $teacher->id]);
            $group->students()->attach($student->id);

            $session = ExamSession::factory()->active()->create([
                'exam_id' => $exam->id,
                'student_id' => $student->id,
                'last_heartbeat_at' => now(), // connected by default
            ]);
            $sessions->push($session);
        }

        return [$teacher, $exam, $sessions];
    }

    // ------------------------------------------------------------------
    // Unit: HeartbeatMonitor service
    // ------------------------------------------------------------------

    public function test_student_is_connected_when_heartbeat_is_recent(): void
    {
        $monitor = app(HeartbeatMonitor::class);
        $windowSeconds = config('exam.heartbeat_window_seconds', 25);

        $session = new ExamSession;
        $session->last_heartbeat_at = now()->subSeconds($windowSeconds - 5);

        $this->assertTrue($monitor->isConnected($session));
    }

    public function test_student_is_disconnected_when_heartbeat_is_stale(): void
    {
        $monitor = app(HeartbeatMonitor::class);
        $windowSeconds = config('exam.heartbeat_window_seconds', 25);

        $session = new ExamSession;
        $session->last_heartbeat_at = now()->subSeconds($windowSeconds + 5);

        $this->assertFalse($monitor->isConnected($session));
    }

    public function test_student_is_disconnected_when_heartbeat_is_null(): void
    {
        $monitor = app(HeartbeatMonitor::class);

        $session = new ExamSession;
        $session->last_heartbeat_at = null;

        $this->assertFalse($monitor->isConnected($session));
    }

    public function test_student_is_disconnected_exactly_at_window_boundary(): void
    {
        $monitor = app(HeartbeatMonitor::class);
        $windowSeconds = config('exam.heartbeat_window_seconds', 25);

        // Exactly at the boundary (not strictly inside window) → disconnected
        $session = new ExamSession;
        $session->last_heartbeat_at = now()->subSeconds($windowSeconds);

        // now() - windowSeconds is NOT gt now() - windowSeconds, so isConnected = false
        $this->assertFalse($monitor->isConnected($session));
    }

    // ------------------------------------------------------------------
    // Feature: Monitor component renders connected/disconnected correctly
    // ------------------------------------------------------------------

    public function test_monitor_shows_connected_student(): void
    {
        [$teacher, $exam, $sessions] = $this->makeActiveExamWithSessions(1);

        // Heartbeat is recent
        $sessions->first()->update(['last_heartbeat_at' => now()]);

        $this->actingAs($teacher, 'teacher');

        $component = Livewire::test(Monitor::class, ['exam' => $exam]);

        $liveStatuses = $component->viewData('liveStatuses');
        $this->assertTrue($liveStatuses->first()['connected']);
    }

    public function test_monitor_shows_disconnected_student_when_heartbeat_is_stale(): void
    {
        [$teacher, $exam, $sessions] = $this->makeActiveExamWithSessions(1);

        $windowSeconds = config('exam.heartbeat_window_seconds', 25);

        // Heartbeat is stale
        $sessions->first()->update([
            'last_heartbeat_at' => now()->subSeconds($windowSeconds + 10),
        ]);

        $this->actingAs($teacher, 'teacher');

        $component = Livewire::test(Monitor::class, ['exam' => $exam]);

        $liveStatuses = $component->viewData('liveStatuses');
        $this->assertFalse($liveStatuses->first()['connected']);
    }

    public function test_monitor_shows_disconnected_when_no_heartbeat(): void
    {
        [$teacher, $exam, $sessions] = $this->makeActiveExamWithSessions(1);

        $sessions->first()->update(['last_heartbeat_at' => null]);

        $this->actingAs($teacher, 'teacher');

        $component = Livewire::test(Monitor::class, ['exam' => $exam]);

        $liveStatuses = $component->viewData('liveStatuses');
        $this->assertFalse($liveStatuses->first()['connected']);
    }

    // ------------------------------------------------------------------
    // Feature: refresh() dispatches student-disconnected on transition
    // ------------------------------------------------------------------

    public function test_refresh_dispatches_event_on_connected_to_disconnected_transition(): void
    {
        [$teacher, $exam, $sessions] = $this->makeActiveExamWithSessions(1);

        $session = $sessions->first();
        $session->update(['last_heartbeat_at' => now()]);

        $this->actingAs($teacher, 'teacher');

        $windowSeconds = config('exam.heartbeat_window_seconds', 25);

        // Mount with student connected
        $component = Livewire::test(Monitor::class, ['exam' => $exam]);

        // Simulate heartbeat going stale
        $session->update([
            'last_heartbeat_at' => now()->subSeconds($windowSeconds + 10),
        ]);

        // Trigger refresh — should detect the connected→disconnected transition
        $component->call('refresh');

        $component->assertDispatched('student-disconnected');
    }

    public function test_refresh_does_not_dispatch_event_when_student_was_never_connected(): void
    {
        [$teacher, $exam, $sessions] = $this->makeActiveExamWithSessions(1);

        $windowSeconds = config('exam.heartbeat_window_seconds', 25);

        // Student has never sent a heartbeat
        $sessions->first()->update(['last_heartbeat_at' => null]);

        $this->actingAs($teacher, 'teacher');

        // First mount — previouslyConnected is empty (null entry), no transition
        $component = Livewire::test(Monitor::class, ['exam' => $exam]);
        $component->call('refresh');

        $component->assertNotDispatched('student-disconnected');
    }

    public function test_refresh_does_not_re_dispatch_event_when_student_stays_disconnected(): void
    {
        [$teacher, $exam, $sessions] = $this->makeActiveExamWithSessions(1);

        $session = $sessions->first();
        $windowSeconds = config('exam.heartbeat_window_seconds', 25);

        // Start connected
        $session->update(['last_heartbeat_at' => now()]);

        $this->actingAs($teacher, 'teacher');

        $component = Livewire::test(Monitor::class, ['exam' => $exam]);

        // Go disconnected → first refresh fires the event
        $session->update([
            'last_heartbeat_at' => now()->subSeconds($windowSeconds + 10),
        ]);
        $component->call('refresh');
        $component->assertDispatched('student-disconnected');

        // Second refresh — student is still disconnected, no new event
        $component->call('refresh');
        $component->assertNotDispatched('student-disconnected');
    }
}
