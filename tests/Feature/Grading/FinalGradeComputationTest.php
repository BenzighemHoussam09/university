<?php

namespace Tests\Feature\Grading;

use App\Domain\Exam\Actions\FinalizeSessionAction;
use App\Domain\Exam\Actions\SaveDraftAnswerAction;
use App\Domain\Exam\Actions\StartExamAction;
use App\Domain\Exam\Exceptions\InvalidGradingTemplateException;
use App\Domain\Exam\Services\GradingService;
use App\Enums\Difficulty;
use App\Enums\ExamStatus;
use App\Enums\Level;
use App\Livewire\Student\Grades\Index as StudentGrades;
use App\Livewire\Teacher\Grades\Show as GradesShow;
use App\Livewire\Teacher\Settings;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\GradeEntry;
use App\Models\GradingTemplate;
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
 * T087 — Final grade computation: US5 acceptance scenarios + edge cases.
 */
class FinalGradeComputationTest extends TestCase
{
    use RefreshDatabase;

    private function seedSystemDefault(int $examMax = 12): void
    {
        \DB::table('grading_templates')->insertOrIgnore([
            'id' => 1,
            'teacher_id' => null,
            'exam_max' => $examMax,
            'personal_work_max' => 4,
            'attendance_max' => 2,
            'participation_max' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function makeScenario(int $examMax = 12): array
    {
        $this->seedSystemDefault($examMax);

        $teacher = Teacher::factory()->create(['grading_template_id' => null]);

        $template = GradingTemplate::withoutEvents(function () use ($teacher, $examMax) {
            return GradingTemplate::create([
                'teacher_id' => $teacher->id,
                'exam_max' => $examMax,
                'personal_work_max' => 4,
                'attendance_max' => 2,
                'participation_max' => 2,
            ]);
        });

        $teacher->grading_template_id = $template->id;
        $teacher->saveQuietly();

        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
        ]);

        $student = Student::factory()->create(['teacher_id' => $teacher->id]);
        $group->students()->attach($student->id);

        // Seed 3 easy + 2 medium + 1 hard questions
        $questionDist = [
            [Difficulty::Easy, 3],
            [Difficulty::Medium, 2],
            [Difficulty::Hard, 1],
        ];
        foreach ($questionDist as [$diff, $count]) {
            Question::factory()->count($count)->withChoices()->create([
                'teacher_id' => $teacher->id,
                'module_id' => $module->id,
                'level' => Level::M1,
                'difficulty' => $diff,
            ]);
        }

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => 3,
            'medium_count' => 2,
            'hard_count' => 1,
            'duration_minutes' => 60,
            'status' => ExamStatus::Scheduled,
        ]);

        return [$teacher, $module, $group, $student, $exam, $template];
    }

    // ------------------------------------------------------------------
    // Scenario 1: exam_component computed via FinalizeSessionAction
    // ------------------------------------------------------------------

    public function test_scenario1_exam_component_upserted_on_finalize(): void
    {
        Notification::fake();

        [$teacher, $module, $group, $student, $exam, $template] = $this->makeScenario(12);

        $this->actingAs($teacher, 'teacher');
        app(StartExamAction::class)->handle($exam);

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->with('assignedQuestions.question.choices')
            ->first();

        // Answer all 6 correctly
        $this->actingAs($student, 'student');
        foreach ($session->assignedQuestions as $esq) {
            $correct = $esq->question->choices->firstWhere('is_correct', true);
            app(SaveDraftAnswerAction::class)->handle($session, $esq->question_id, $correct->id);
        }

        app(FinalizeSessionAction::class)->handle($session, 'manual');

        $entry = GradeEntry::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->where('module_id', $module->id)
            ->first();

        $this->assertNotNull($entry);
        $this->assertEqualsWithDelta(12.0, $entry->exam_component, 0.01);
        $this->assertEqualsWithDelta(12.0, $entry->final_grade, 0.01); // only exam component set
    }

    // ------------------------------------------------------------------
    // Scenario 2: manual components + final_grade = sum
    // ------------------------------------------------------------------

    public function test_scenario2_final_grade_equals_sum_of_components(): void
    {
        Notification::fake();

        [$teacher, $module, $group, $student, $exam, $template] = $this->makeScenario(10);

        $this->actingAs($teacher, 'teacher');
        app(StartExamAction::class)->handle($exam);

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        app(FinalizeSessionAction::class)->handle($session, 'manual');

        // Manually set personal_work=4, attendance=2, participation=3 via grades component
        // exam_component = 0 (no answers), exam_max=10 → 0.0
        // final = 0 + 4 + 2 + 3 = 9
        Livewire::test(GradesShow::class, ['group' => $group])
            ->set("rows.{$student->id}.personal_work", 4)
            ->set("rows.{$student->id}.attendance", 2)
            ->set("rows.{$student->id}.participation", 2)
            ->call('saveRow', $student->id)
            ->assertSet('errorMessage', '')
            ->assertSet('successMessage', 'Grades saved.');

        $entry = GradeEntry::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->where('module_id', $module->id)
            ->first();

        $this->assertEqualsWithDelta(0.0 + 4.0 + 2.0 + 2.0, $entry->final_grade, 0.01);
    }

    // ------------------------------------------------------------------
    // Scenario 3: US5 acceptance — open settings, change maxes, enter grades
    // ------------------------------------------------------------------

    public function test_scenario3_acceptance_test_from_tasks_md(): void
    {
        Notification::fake();

        // Change maxes to (10,4,3,3) via Settings
        $this->seedSystemDefault(12);
        $teacher = Teacher::factory()->create(['grading_template_id' => null]);

        $existingTemplate = GradingTemplate::withoutEvents(fn () => GradingTemplate::create([
            'teacher_id' => $teacher->id,
            'exam_max' => 12,
            'personal_work_max' => 4,
            'attendance_max' => 2,
            'participation_max' => 2,
        ]));
        $teacher->grading_template_id = $existingTemplate->id;
        $teacher->saveQuietly();

        $this->actingAs($teacher, 'teacher');

        // Change settings to (10, 4, 3, 3)
        Livewire::test(Settings::class)
            ->set('examMax', 10)
            ->set('personalWorkMax', 4)
            ->set('attendanceMax', 3)
            ->set('participationMax', 3)
            ->call('save')
            ->assertSet('successMessage', 'Settings saved successfully.');

        // Set up module, group, student, exam
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
        ]);
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);
        $group->students()->attach($student->id);

        foreach ([[Difficulty::Easy, 3], [Difficulty::Medium, 2], [Difficulty::Hard, 1]] as [$diff, $count]) {
            Question::factory()->count($count)->withChoices()->create([
                'teacher_id' => $teacher->id,
                'module_id' => $module->id,
                'level' => Level::M1,
                'difficulty' => $diff,
            ]);
        }

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

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->with('assignedQuestions.question.choices')
            ->first();

        // Answer enough to get exam_component = 8.0 out of 10
        // 8/10 * 10 = 8.0 → need 4 out of 5 correct (but we have 6 questions)
        // Actually: exam_component = raw/total * exam_max
        // We need exam_component=8.0, exam_max=10, total=6
        // raw = 8 * 6 / 10 = 4.8 → not integer
        // Let's just answer all correctly → exam_component = 10.0
        // Or let's manually set the exam_component to 8.0 for a more precise test
        // Task says: "exam_component is 8.0; assert final_grade=17.0"
        // We'll use the direct grade_entry upsert approach to set the exam_component

        // First finalize session with 0 answers to create the grade entry
        app(FinalizeSessionAction::class)->handle($session, 'manual');

        // Now manually set exam_component to 8.0 to match the spec scenario
        $entry = GradeEntry::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->where('module_id', $module->id)
            ->first();

        $this->assertNotNull($entry);

        // Update exam_component to 8.0 directly (simulating a prior finalization that gave 8.0)
        $entry->exam_component = 8.0;
        $entry->save();

        // Now enter personal_work=4, attendance=2, participation=3
        Livewire::test(GradesShow::class, ['group' => $group])
            ->set("rows.{$student->id}.personal_work", 4)
            ->set("rows.{$student->id}.attendance", 2)
            ->set("rows.{$student->id}.participation", 3)
            ->call('saveRow', $student->id)
            ->assertSet('errorMessage', '')
            ->assertSet('successMessage', 'Grades saved.');

        $entry->refresh();
        // 8.0 + 4 + 2 + 3 = 17.0
        $this->assertEqualsWithDelta(17.0, $entry->final_grade, 0.01);

        // Student sees the same breakdown
        $this->actingAs($student, 'student');
        Livewire::test(StudentGrades::class)
            ->assertViewHas('entries', fn ($e) => $e->first()?->final_grade >= 17.0 - 0.01);
    }

    // ------------------------------------------------------------------
    // Scenario 4: Rejects out-of-range component value
    // ------------------------------------------------------------------

    public function test_scenario4_rejects_component_value_above_max(): void
    {
        Notification::fake();

        [$teacher, $module, $group, $student, $exam, $template] = $this->makeScenario(12);

        $this->actingAs($teacher, 'teacher');
        app(StartExamAction::class)->handle($exam);

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->first();

        app(FinalizeSessionAction::class)->handle($session, 'manual');

        // Try to save personal_work=5 when max is 4
        Livewire::test(GradesShow::class, ['group' => $group])
            ->set("rows.{$student->id}.personal_work", 5) // exceeds max of 4
            ->set("rows.{$student->id}.attendance", 1)
            ->set("rows.{$student->id}.participation", 1)
            ->call('saveRow', $student->id)
            ->assertSet('successMessage', '')
            ->assertSet('errorMessage', "Component 'personal_work' value 5 is out of range [0, 4].");
    }

    // ------------------------------------------------------------------
    // Scenario 5: Teacher observer clones template on teacher creation
    // ------------------------------------------------------------------

    public function test_teacher_observer_clones_template_on_creation(): void
    {
        $this->seedSystemDefault(12);

        $teacher = Teacher::factory()->create();

        $this->assertNotNull($teacher->grading_template_id);

        $template = GradingTemplate::withoutGlobalScopes()->find($teacher->grading_template_id);
        $this->assertNotNull($template);
        $this->assertEquals($teacher->id, $template->teacher_id);
        $this->assertEquals(12, $template->exam_max);
        $this->assertEquals(4, $template->personal_work_max);
        $this->assertEquals(2, $template->attendance_max);
        $this->assertEquals(2, $template->participation_max);
    }

    // ------------------------------------------------------------------
    // Scenario 6: GradingService::validateComponentValue correctness
    // ------------------------------------------------------------------

    public function test_grading_service_validates_component_values(): void
    {
        $service = app(GradingService::class);

        $template = GradingTemplate::withoutEvents(fn () => GradingTemplate::make([
            'exam_max' => 12,
            'personal_work_max' => 4,
            'attendance_max' => 2,
            'participation_max' => 2,
        ]));

        // Valid
        $service->validateComponentValue('personal_work', 4.0, $template);
        $service->validateComponentValue('personal_work', 0.0, $template);

        // Invalid
        $this->expectException(InvalidGradingTemplateException::class);
        $service->validateComponentValue('personal_work', 5.0, $template);
    }

    // ------------------------------------------------------------------
    // Scenario 7: computeFinalGrade sums correctly
    // ------------------------------------------------------------------

    public function test_compute_final_grade_sums_all_components(): void
    {
        $service = app(GradingService::class);

        $entry = GradeEntry::make([
            'exam_component' => 8.0,
            'personal_work' => 4.0,
            'attendance' => 2.0,
            'participation' => 3.0,
        ]);

        $this->assertEqualsWithDelta(17.0, $service->computeFinalGrade($entry), 0.01);
    }
}
