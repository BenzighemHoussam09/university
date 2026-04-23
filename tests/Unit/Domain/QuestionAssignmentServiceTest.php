<?php

namespace Tests\Unit\Domain;

use App\Domain\Exam\Services\QuestionAssignmentService;
use App\Enums\Difficulty;
use App\Enums\ExamStatus;
use App\Enums\Level;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;
use App\Models\Group;
use App\Models\Module;
use App\Models\Question;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Property tests for the QuestionAssignmentService algorithm.
 *
 * Invariants (research.md Decision 7):
 * 1. No question appears more than once within a single student's session when bank is sufficient.
 * 2. Per-difficulty count per student matches the exam distribution.
 * 3. display_order values are unique within each session.
 * 4. When bank is smaller than required, cycling is used and students still get different ordering.
 */
class QuestionAssignmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private QuestionAssignmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new QuestionAssignmentService;
    }

    // ------------------------------------------------------------------
    // Helper
    // ------------------------------------------------------------------

    private function setupScenario(
        int $easyCount,
        int $mediumCount,
        int $hardCount,
        int $bankEasy,
        int $bankMedium,
        int $bankHard,
        int $studentCount
    ): array {
        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
        ]);

        // Seed the question bank
        foreach (range(1, $bankEasy) as $_) {
            Question::factory()->withChoices()->create([
                'teacher_id' => $teacher->id,
                'module_id' => $module->id,
                'level' => Level::M1,
                'difficulty' => Difficulty::Easy,
            ]);
        }
        foreach (range(1, $bankMedium) as $_) {
            Question::factory()->withChoices()->create([
                'teacher_id' => $teacher->id,
                'module_id' => $module->id,
                'level' => Level::M1,
                'difficulty' => Difficulty::Medium,
            ]);
        }
        foreach (range(1, $bankHard) as $_) {
            Question::factory()->withChoices()->create([
                'teacher_id' => $teacher->id,
                'module_id' => $module->id,
                'level' => Level::M1,
                'difficulty' => Difficulty::Hard,
            ]);
        }

        $exam = Exam::factory()->create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'easy_count' => $easyCount,
            'medium_count' => $mediumCount,
            'hard_count' => $hardCount,
            'status' => ExamStatus::Active,
        ]);

        $students = Student::factory()->count($studentCount)->create(['teacher_id' => $teacher->id]);

        $sessions = $students->map(fn ($s) => ExamSession::create([
            'exam_id' => $exam->id,
            'student_id' => $s->id,
            'status' => 'active',
            'started_at' => now(),
        ]));

        // Authenticate as teacher so BelongsToTeacher scope works
        $this->actingAs($teacher, 'teacher');

        return [$exam, $sessions, $students, $teacher];
    }

    // ------------------------------------------------------------------
    // Invariant 1: No duplicates per session when bank is sufficient
    // ------------------------------------------------------------------

    public function test_no_duplicates_per_session_when_bank_is_sufficient(): void
    {
        [$exam, $sessions] = $this->setupScenario(3, 2, 1, 20, 20, 20, 10);

        $this->service->assign($exam, $sessions);

        foreach ($sessions as $session) {
            $questionIds = ExamSessionQuestion::where('exam_session_id', $session->id)
                ->pluck('question_id')
                ->toArray();

            $this->assertSame(count($questionIds), count(array_unique($questionIds)),
                "Session {$session->id} has duplicate questions.");
        }
    }

    // ------------------------------------------------------------------
    // Invariant 2: Per-difficulty count matches exam distribution
    // ------------------------------------------------------------------

    public function test_per_difficulty_counts_match_exam_distribution(): void
    {
        [$exam, $sessions, , $teacher] = $this->setupScenario(3, 2, 1, 15, 15, 15, 5);

        $this->service->assign($exam, $sessions);

        foreach ($sessions as $session) {
            $assignedIds = ExamSessionQuestion::where('exam_session_id', $session->id)
                ->pluck('question_id');

            $counts = Question::withoutGlobalScopes()
                ->whereIn('id', $assignedIds)
                ->selectRaw('difficulty, count(*) as cnt')
                ->groupBy('difficulty')
                ->pluck('cnt', 'difficulty');

            $this->assertEquals(3, $counts['easy'] ?? 0, 'Easy count mismatch');
            $this->assertEquals(2, $counts['medium'] ?? 0, 'Medium count mismatch');
            $this->assertEquals(1, $counts['hard'] ?? 0, 'Hard count mismatch');
        }
    }

    // ------------------------------------------------------------------
    // Invariant 3: display_order is unique within each session
    // ------------------------------------------------------------------

    public function test_display_order_is_unique_within_each_session(): void
    {
        [$exam, $sessions] = $this->setupScenario(3, 2, 1, 10, 10, 10, 5);

        $this->service->assign($exam, $sessions);

        foreach ($sessions as $session) {
            $orders = ExamSessionQuestion::where('exam_session_id', $session->id)
                ->pluck('display_order')
                ->toArray();

            $this->assertSame(count($orders), count(array_unique($orders)),
                "Session {$session->id} has duplicate display_orders.");
        }
    }

    // ------------------------------------------------------------------
    // Invariant 4: Cycling when bank is smaller than students × required
    // ------------------------------------------------------------------

    public function test_cycling_used_when_bank_is_small(): void
    {
        // Bank has 5 questions, 10 students each need 3 → cycling required
        [$exam, $sessions] = $this->setupScenario(3, 0, 0, 5, 0, 0, 10);

        $this->service->assign($exam, $sessions);

        // Each session must still receive exactly 3 easy questions
        foreach ($sessions as $session) {
            $count = ExamSessionQuestion::where('exam_session_id', $session->id)->count();
            $this->assertEquals(3, $count, "Session {$session->id} should have 3 questions.");
        }

        // display_order is still unique per session (no duplicates within a session)
        foreach ($sessions as $session) {
            $orders = ExamSessionQuestion::where('exam_session_id', $session->id)
                ->pluck('display_order')
                ->toArray();
            $this->assertSame(count($orders), count(array_unique($orders)));
        }
    }

    // ------------------------------------------------------------------
    // Total rows across all sessions
    // ------------------------------------------------------------------

    public function test_total_assigned_questions_is_correct(): void
    {
        $studentCount = 3;
        $total = 3 + 2 + 1; // easy + medium + hard per student

        [$exam, $sessions] = $this->setupScenario(3, 2, 1, 20, 20, 20, $studentCount);

        $this->service->assign($exam, $sessions);

        $totalInserted = ExamSessionQuestion::whereIn(
            'exam_session_id', $sessions->pluck('id')
        )->count();

        $this->assertEquals($studentCount * $total, $totalInserted);
    }
}
