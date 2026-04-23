<?php

namespace Tests\Feature\Exam;

use App\Domain\Exam\Actions\SaveDraftAnswerAction;
use App\Domain\Exam\Actions\StartExamAction;
use App\Enums\Difficulty;
use App\Enums\ExamStatus;
use App\Enums\Level;
use App\Livewire\Student\Exams\Session as StudentSession;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;
use App\Models\Group;
use App\Models\Module;
use App\Models\Question;
use App\Models\Student;
use App\Models\StudentAnswer;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

/**
 * Verifies every saveDraft call writes immediately and is idempotent.
 */
class DraftAnswerPersistenceTest extends TestCase
{
    use RefreshDatabase;

    private function setupActiveSession(): array
    {
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

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        return [$teacher, $student, $exam, $session];
    }

    // ------------------------------------------------------------------
    // SaveDraftAnswerAction — direct call
    // ------------------------------------------------------------------

    public function test_save_draft_writes_immediately(): void
    {
        [$teacher, $student, $exam, $session] = $this->setupActiveSession();

        $esq = ExamSessionQuestion::where('exam_session_id', $session->id)->first();
        $choice = $esq->question->choices->first();

        $this->actingAs($student, 'student');

        app(SaveDraftAnswerAction::class)->handle($session, $esq->question_id, $choice->id);

        $this->assertDatabaseHas('student_answers', [
            'exam_session_id' => $session->id,
            'question_id' => $esq->question_id,
            'selected_choice_id' => $choice->id,
            'status' => 'draft',
        ]);
    }

    // ------------------------------------------------------------------
    // SaveDraftAnswerAction — idempotent (second call same choice)
    // ------------------------------------------------------------------

    public function test_save_draft_is_idempotent_same_choice(): void
    {
        [$teacher, $student, $exam, $session] = $this->setupActiveSession();

        $esq = ExamSessionQuestion::where('exam_session_id', $session->id)->first();
        $choice = $esq->question->choices->first();

        $this->actingAs($student, 'student');

        $action = app(SaveDraftAnswerAction::class);
        $action->handle($session, $esq->question_id, $choice->id);
        $action->handle($session, $esq->question_id, $choice->id); // replay

        // Still exactly one row
        $count = StudentAnswer::where('exam_session_id', $session->id)
            ->where('question_id', $esq->question_id)
            ->count();

        $this->assertEquals(1, $count, 'Must have exactly one answer row after two identical calls.');
    }

    // ------------------------------------------------------------------
    // SaveDraftAnswerAction — changing choice updates existing row
    // ------------------------------------------------------------------

    public function test_save_draft_updates_when_choice_changes(): void
    {
        [$teacher, $student, $exam, $session] = $this->setupActiveSession();

        $esq = ExamSessionQuestion::where('exam_session_id', $session->id)->first();
        $choices = $esq->question->choices;

        $this->actingAs($student, 'student');

        $action = app(SaveDraftAnswerAction::class);
        $action->handle($session, $esq->question_id, $choices[0]->id);
        $action->handle($session, $esq->question_id, $choices[1]->id); // change choice

        $answer = StudentAnswer::where('exam_session_id', $session->id)
            ->where('question_id', $esq->question_id)
            ->first();

        $this->assertEquals($choices[1]->id, $answer->selected_choice_id,
            'Choice should be updated to the second selection.');
    }

    // ------------------------------------------------------------------
    // SaveDraftAnswerAction — via Livewire component
    // ------------------------------------------------------------------

    public function test_save_draft_via_livewire_component(): void
    {
        [$teacher, $student, $exam, $session] = $this->setupActiveSession();

        $esq = ExamSessionQuestion::where('exam_session_id', $session->id)->first();
        $choice = $esq->question->choices->first();

        $this->actingAs($student, 'student');

        Livewire::test(StudentSession::class, ['exam' => $exam])
            ->call('saveDraft', $esq->question_id, $choice->id);

        $this->assertDatabaseHas('student_answers', [
            'exam_session_id' => $session->id,
            'question_id' => $esq->question_id,
            'selected_choice_id' => $choice->id,
        ]);
    }

    // ------------------------------------------------------------------
    // SaveDraftAnswerAction — rejects invalid question
    // ------------------------------------------------------------------

    public function test_save_draft_rejects_unassigned_question(): void
    {
        [$teacher, $student, $exam, $session] = $this->setupActiveSession();

        // Create a question NOT assigned to this session
        $otherModule = Module::factory()->create(['teacher_id' => $teacher->id]);
        $otherQuestion = Question::factory()->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $otherModule->id,
        ]);
        $otherChoice = $otherQuestion->choices->first();

        $this->actingAs($student, 'student');

        $this->expectException(HttpException::class);

        app(SaveDraftAnswerAction::class)->handle($session, $otherQuestion->id, $otherChoice->id);
    }

    // ------------------------------------------------------------------
    // SaveDraftAnswerAction — rejects after deadline
    // ------------------------------------------------------------------

    public function test_save_draft_rejects_after_deadline(): void
    {
        [$teacher, $student, $exam, $session] = $this->setupActiveSession();

        // Manually set deadline to the past
        $session->update(['deadline' => now()->subMinutes(5)]);

        $esq = ExamSessionQuestion::where('exam_session_id', $session->id)->first();
        $choice = $esq->question->choices->first();

        $this->actingAs($student, 'student');

        $this->expectException(HttpException::class);

        app(SaveDraftAnswerAction::class)->handle($session, $esq->question_id, $choice->id);
    }
}
