<?php

namespace App\Domain\Exam\Actions;

use App\Domain\Exam\Events\ExamStarted;
use App\Domain\Exam\Services\DeadlineCalculator;
use App\Domain\Exam\Services\QuestionAssignmentService;
use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Support\Facades\DB;

/**
 * Transitions an exam from 'scheduled' to 'active', creates per-student sessions,
 * assigns questions, and computes initial deadlines.
 *
 * Per contracts/domain-actions.md §StartExamAction.
 */
class StartExamAction
{
    public function __construct(
        private readonly QuestionAssignmentService $questionAssignment,
        private readonly DeadlineCalculator $deadlineCalculator
    ) {}

    public function handle(Exam $exam): void
    {
        DB::transaction(function () use ($exam) {
            $now = now();

            // 1. Activate the exam
            $exam->update([
                'status' => ExamStatus::Active,
                'started_at' => $now,
            ]);

            // 2. Load all students in the group
            $students = $exam->group->students()->get();

            // 3. Create exam_sessions for each student
            $sessions = collect();
            foreach ($students as $student) {
                $session = ExamSession::create([
                    'exam_id' => $exam->id,
                    'student_id' => $student->id,
                    'status' => 'active',
                    'started_at' => $now,
                    // deadline will be set below after we have the session object
                ]);
                $sessions->push($session);
            }

            // 4. Assign questions (writes exam_session_questions rows)
            // Reload exam with group.module_id to satisfy QuestionAssignmentService
            $exam->load('group');
            $this->questionAssignment->assign($exam, $sessions);

            // 5. Set deadline for each session
            foreach ($sessions as $session) {
                // Reload the session's exam relationship for the calculator
                $session->setRelation('exam', $exam);
                $deadline = $this->deadlineCalculator->for($session);
                $session->update(['deadline' => $deadline]);
            }
        });

        ExamStarted::dispatch($exam->fresh());
    }
}
