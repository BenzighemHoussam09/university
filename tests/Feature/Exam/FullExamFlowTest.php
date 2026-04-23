<?php

namespace Tests\Feature\Exam;

use App\Domain\Exam\Actions\CreateExamAction;
use App\Domain\Exam\Actions\FinalizeSessionAction;
use App\Domain\Exam\Actions\SaveDraftAnswerAction;
use App\Domain\Exam\Actions\StartExamAction;
use App\Domain\Exam\Exceptions\BankTooSmallException;
use App\Enums\Difficulty;
use App\Enums\ExamStatus;
use App\Enums\Level;
use App\Livewire\Student\Exams\Session as StudentSession;
use App\Livewire\Teacher\Exams\Create as TeacherCreate;
use App\Livewire\Teacher\Exams\Results as TeacherResults;
use App\Livewire\Teacher\Exams\Show as TeacherShow;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;
use App\Models\Group;
use App\Models\Module;
use App\Models\Question;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * US3 end-to-end exam flow:
 * Teacher creates a 6-question exam (3/2/1), 3 students take it, finalize,
 * results are computed correctly.
 */
class FullExamFlowTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function makeTeacherWithGroupAndQuestions(int $easy = 3, int $medium = 2, int $hard = 1): array
    {
        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
        ]);

        $students = Student::factory()->count(3)->create(['teacher_id' => $teacher->id]);
        foreach ($students as $student) {
            $group->students()->attach($student->id);
        }

        // Seed question bank
        Question::factory()->count($easy)->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
            'difficulty' => Difficulty::Easy,
        ]);
        Question::factory()->count($medium)->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
            'difficulty' => Difficulty::Medium,
        ]);
        Question::factory()->count($hard)->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
            'difficulty' => Difficulty::Hard,
        ]);

        return [$teacher, $module, $group, $students];
    }

    // ------------------------------------------------------------------
    // T1: Teacher creates exam with correct distribution
    // ------------------------------------------------------------------

    public function test_teacher_can_create_exam_via_livewire(): void
    {
        [$teacher, $module, $group] = $this->makeTeacherWithGroupAndQuestions();

        $this->actingAs($teacher, 'teacher');

        Livewire::test(TeacherCreate::class)
            ->set('title', 'Midterm 2025')
            ->set('groupId', $group->id)
            ->set('easyCount', 3)
            ->set('mediumCount', 2)
            ->set('hardCount', 1)
            ->set('durationMinutes', 60)
            ->set('scheduledAt', now()->addHour()->format('Y-m-d\TH:i'))
            ->call('save');

        $this->assertDatabaseHas('exams', [
            'teacher_id' => $teacher->id,
            'title' => 'Midterm 2025',
            'easy_count' => 3,
            'medium_count' => 2,
            'hard_count' => 1,
            'status' => 'scheduled',
        ]);
    }

    // ------------------------------------------------------------------
    // T2: CreateExamAction rejects insufficient bank
    // ------------------------------------------------------------------

    public function test_create_exam_rejects_insufficient_bank(): void
    {
        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
        ]);

        // Only 1 easy question in bank, but exam needs 3
        Question::factory()->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
            'difficulty' => Difficulty::Easy,
        ]);

        $this->actingAs($teacher, 'teacher');

        $this->expectException(BankTooSmallException::class);

        app(CreateExamAction::class)->handle($teacher, [
            'group_id' => $group->id,
            'title' => 'Test',
            'easy_count' => 3,
            'medium_count' => 0,
            'hard_count' => 0,
            'duration_minutes' => 60,
            'scheduled_at' => now()->addHour(),
        ]);
    }

    // ------------------------------------------------------------------
    // T3: Teacher starts exam — sessions and questions assigned
    // ------------------------------------------------------------------

    public function test_starting_exam_creates_sessions_and_assigns_questions(): void
    {
        [$teacher, , $group, $students] = $this->makeTeacherWithGroupAndQuestions();

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => 3,
            'medium_count' => 2,
            'hard_count' => 1,
            'duration_minutes' => 60,
            'status' => ExamStatus::Scheduled,
        ]);

        $this->actingAs($teacher, 'teacher');

        app(StartExamAction::class)->handle($exam);

        $exam->refresh();
        $this->assertEquals(ExamStatus::Active, $exam->status);
        $this->assertNotNull($exam->started_at);

        // 3 sessions created
        $this->assertEquals(3, ExamSession::where('exam_id', $exam->id)->count());

        // Each session has 6 questions (3+2+1)
        foreach ($students as $student) {
            $session = ExamSession::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->first();

            $this->assertNotNull($session);
            $this->assertEquals('active', $session->status);
            $this->assertNotNull($session->deadline);
            $this->assertEquals(6, ExamSessionQuestion::where('exam_session_id', $session->id)->count());
        }
    }

    // ------------------------------------------------------------------
    // T4: StartExamAction via Livewire Show component
    // ------------------------------------------------------------------

    public function test_teacher_starts_exam_via_livewire_and_redirects_to_monitor(): void
    {
        [$teacher, , $group] = $this->makeTeacherWithGroupAndQuestions();

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => 3,
            'medium_count' => 2,
            'hard_count' => 1,
            'duration_minutes' => 60,
            'status' => ExamStatus::Scheduled,
        ]);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(TeacherShow::class, ['exam' => $exam])
            ->call('start')
            ->assertRedirect(route('teacher.exams.monitor', $exam));

        $exam->refresh();
        $this->assertEquals(ExamStatus::Active, $exam->status);
    }

    // ------------------------------------------------------------------
    // T5: Student sees session page after exam started
    // ------------------------------------------------------------------

    public function test_student_session_page_loads_with_assigned_questions(): void
    {
        [$teacher, , $group, $students] = $this->makeTeacherWithGroupAndQuestions();

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => 3,
            'medium_count' => 2,
            'hard_count' => 1,
            'duration_minutes' => 60,
            'status' => ExamStatus::Scheduled,
        ]);

        app(StartExamAction::class)->handle($exam);

        $student = $students->first();

        $this->actingAs($student, 'student');

        Livewire::test(StudentSession::class, ['exam' => $exam])
            ->assertOk()
            ->assertViewHas('assignedQuestions', fn ($qs) => $qs->count() === 6);
    }

    // ------------------------------------------------------------------
    // T6: saveDraft persists correctly
    // ------------------------------------------------------------------

    public function test_student_can_save_draft_answer(): void
    {
        [$teacher, , $group, $students] = $this->makeTeacherWithGroupAndQuestions();

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => 3,
            'medium_count' => 2,
            'hard_count' => 1,
            'duration_minutes' => 60,
            'status' => ExamStatus::Scheduled,
        ]);

        app(StartExamAction::class)->handle($exam);

        $student = $students->first();
        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        $esq = ExamSessionQuestion::where('exam_session_id', $session->id)->first();
        $choice = $esq->question->choices->first();

        $this->actingAs($student, 'student');

        Livewire::test(StudentSession::class, ['exam' => $exam])
            ->call('saveDraft', $esq->question_id, $choice->id);

        $this->assertDatabaseHas('student_answers', [
            'exam_session_id' => $session->id,
            'question_id' => $esq->question_id,
            'selected_choice_id' => $choice->id,
            'status' => 'draft',
        ]);
    }

    // ------------------------------------------------------------------
    // T7: Full finalization — score computed correctly
    // ------------------------------------------------------------------

    public function test_finalizing_session_computes_scores_correctly(): void
    {
        Notification::fake();

        [$teacher, , $group, $students] = $this->makeTeacherWithGroupAndQuestions();

        // Seed system default grading template
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

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => 3,
            'medium_count' => 2,
            'hard_count' => 1,
            'duration_minutes' => 60,
            'status' => ExamStatus::Scheduled,
        ]);

        app(StartExamAction::class)->handle($exam);

        $student = $students->first();
        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->with('assignedQuestions.question.choices')
            ->first();

        // Answer all questions correctly
        $saveAction = app(SaveDraftAnswerAction::class);
        $this->actingAs($student, 'student');

        foreach ($session->assignedQuestions as $esq) {
            $correctChoice = $esq->question->choices->firstWhere('is_correct', true);
            $saveAction->handle($session, $esq->question_id, $correctChoice->id);
        }

        // Finalize
        app(FinalizeSessionAction::class)->handle($session, 'manual');

        $session->refresh();

        $this->assertEquals('completed', $session->status);
        $this->assertEquals(6, $session->exam_score_raw);
        $this->assertEqualsWithDelta(12.0, $session->exam_score_component, 0.01);
        $this->assertNotNull($session->completed_at);
    }

    // ------------------------------------------------------------------
    // T8: Results page shows per-student scores and group average
    // ------------------------------------------------------------------

    public function test_results_page_shows_scores(): void
    {
        Notification::fake();

        [$teacher, , $group, $students] = $this->makeTeacherWithGroupAndQuestions();

        \DB::table('grading_templates')->insertOrIgnore([
            'id' => 1, 'teacher_id' => null, 'exam_max' => 12,
            'personal_work_max' => 4, 'attendance_max' => 2, 'participation_max' => 2,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => 3,
            'medium_count' => 2,
            'hard_count' => 1,
            'duration_minutes' => 60,
            'status' => ExamStatus::Scheduled,
        ]);

        app(StartExamAction::class)->handle($exam);

        $finalizeAction = app(FinalizeSessionAction::class);

        // Finalize all 3 students without answering (score = 0)
        foreach ($students as $student) {
            $session = ExamSession::where('exam_id', $exam->id)
                ->where('student_id', $student->id)
                ->first();
            $finalizeAction->handle($session, 'manual');
        }

        $exam->update(['status' => ExamStatus::Ended, 'ended_at' => now()]);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(TeacherResults::class, ['exam' => $exam])
            ->assertOk()
            ->assertViewHas('rows', fn ($rows) => $rows->count() === 3)
            ->assertViewHas('groupAverage', 0.0);
    }
}
