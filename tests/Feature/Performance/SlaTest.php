<?php

namespace Tests\Feature\Performance;

use App\Domain\Exam\Actions\SaveDraftAnswerAction;
use App\Domain\Exam\Actions\StartExamAction;
use App\Domain\Exam\Services\HeartbeatMonitor;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Group;
use App\Models\Question;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Validates service level agreements (SLAs) for critical performance paths:
 * - SC-003: Answer persist within 2s
 * - SC-005: Disconnect detection within 15s
 */
class SlaTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $teacher;

    private Group $group;

    private Student $student;

    private Exam $exam;

    private ExamSession $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = Teacher::factory()->create();
        $this->group = Group::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->student = Student::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->group->students()->attach($this->student->id);

        Question::factory(5)->withChoices()->create([
            'teacher_id' => $this->teacher->id,
            'module_id' => $this->group->module_id,
            'level' => $this->group->level,
        ]);

        $this->exam = Exam::factory()->create([
            'teacher_id' => $this->teacher->id,
            'group_id' => $this->group->id,
            'easy_count' => 2,
            'medium_count' => 2,
            'hard_count' => 1,
            'duration_minutes' => 30,
        ]);

        app(StartExamAction::class)->handle($this->exam);
        $this->session = ExamSession::where('student_id', $this->student->id)->first();
        $this->session->update(['last_heartbeat_at' => now()]);
    }

    public function test_sc_003_answer_persisted_within_2_seconds(): void
    {
        $this->actingAs($this->student, 'student');

        $startTime = microtime(true);

        $assignedQuestion = $this->session->assignedQuestions()->first();
        $question = Question::find($assignedQuestion->question_id);
        $choice = $question->choices()->first();

        app(SaveDraftAnswerAction::class)->handle(
            $this->session,
            $question->id,
            $choice->id
        );

        $elapsedMs = (microtime(true) - $startTime) * 1000;

        $this->assertLessThanOrEqual(
            2000,
            $elapsedMs,
            "Answer persistence took {$elapsedMs}ms, exceeds 2000ms SLA"
        );

        $savedAnswer = $this->session->answers()
            ->where('question_id', $question->id)
            ->first();

        $this->assertNotNull($savedAnswer, 'Answer not persisted');
        $this->assertSame($choice->id, $savedAnswer->selected_choice_id);
    }

    public function test_sc_005_disconnect_detected_within_15_seconds(): void
    {
        $startTime = microtime(true);

        $this->session->update(['last_heartbeat_at' => now()->subSeconds(26)]);

        $isConnected = app(HeartbeatMonitor::class)->isConnected($this->session);

        $elapsedMs = (microtime(true) - $startTime) * 1000;

        $this->assertFalse($isConnected, 'Session should be marked disconnected');

        $this->assertLessThanOrEqual(
            15000,
            $elapsedMs,
            "Disconnect detection took {$elapsedMs}ms, exceeds 15000ms SLA"
        );
    }

    public function test_sc_003_multiple_answers_within_budget(): void
    {
        $this->actingAs($this->student, 'student');

        $startTime = microtime(true);
        $assignedQuestions = $this->session->assignedQuestions()->take(3)->get();

        foreach ($assignedQuestions as $assigned) {
            $question = Question::find($assigned->question_id);
            $choice = $question->choices()->first();

            app(SaveDraftAnswerAction::class)->handle(
                $this->session,
                $question->id,
                $choice->id
            );
        }

        $elapsedMs = (microtime(true) - $startTime) * 1000;

        $answerCount = $this->session->answers()->count();
        $this->assertSame(3, $answerCount);

        $this->assertLessThanOrEqual(
            2000,
            $elapsedMs / 3,
            'Average answer time exceeds 2000ms per answer'
        );
    }
}
