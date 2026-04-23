<?php

namespace App\Domain\Exam\Services;

use App\Enums\Difficulty;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;
use App\Models\Question;
use Illuminate\Support\Collection;

/**
 * Assigns questions to each student's exam session.
 *
 * Algorithm (research.md Decision 7):
 *
 * For each difficulty:
 *   - If bank_size >= required (bank sufficient for uniqueness):
 *       Each student gets a freshly-shuffled slice of the bank (no within-session repeats).
 *   - If bank_size < required (bank insufficient):
 *       Cycle through the bank per student; within-session repeats are unavoidable
 *       and acceptable per spec.
 *
 * After collecting questions per difficulty, combine all difficulties for each student,
 * shuffle the combined list, and persist as exam_session_questions with display_order.
 *
 * Invariants guaranteed:
 * - No question appears more than once within a single student's session
 *   when bank_size >= required.
 * - display_order values are unique within each session.
 * - Per-difficulty count matches exam configuration.
 * - Two students will have different display_order sequences (probabilistic via shuffle).
 */
class QuestionAssignmentService
{
    /**
     * Assign questions to every session in a single transaction scope.
     *
     * @param  Collection  $sessions  Collection of ExamSession (already persisted).
     */
    public function assign(Exam $exam, Collection $sessions): void
    {
        $studentCount = $sessions->count();

        if ($studentCount === 0) {
            return;
        }

        // Map: sessionId → [questionId, ...]
        $perSessionQuestions = $sessions->mapWithKeys(fn ($s) => [$s->id => []]);

        foreach (Difficulty::cases() as $difficulty) {
            $required = match ($difficulty) {
                Difficulty::Easy => $exam->easy_count,
                Difficulty::Medium => $exam->medium_count,
                Difficulty::Hard => $exam->hard_count,
            };

            if ($required === 0) {
                continue;
            }

            $bankIds = Question::where('module_id', $exam->group->module_id)
                ->where('level', $exam->group->level->value)
                ->where('difficulty', $difficulty->value)
                ->pluck('id')
                ->toArray();

            $bankSize = count($bankIds);

            if ($bankSize === 0) {
                // Nothing to assign; caller should have validated via CreateExamAction
                continue;
            }

            foreach ($sessions as $session) {
                $slice = $this->pickForStudent($bankIds, $bankSize, $required);

                $perSessionQuestions[$session->id] = array_merge(
                    $perSessionQuestions[$session->id],
                    $slice
                );
            }
        }

        // Persist: for each session, shuffle combined questions and insert rows
        foreach ($sessions as $session) {
            $combined = $perSessionQuestions[$session->id];
            shuffle($combined);

            $rows = [];
            foreach ($combined as $order => $questionId) {
                $rows[] = [
                    'exam_session_id' => $session->id,
                    'question_id' => $questionId,
                    'display_order' => $order + 1,
                ];
            }

            if (! empty($rows)) {
                ExamSessionQuestion::insert($rows);
            }
        }
    }

    /**
     * Pick `$required` question IDs for one student from the bank.
     *
     * - When bank_size >= required: shuffle a fresh copy, take first $required.
     *   Guarantees no within-session duplicates.
     * - When bank_size < required: cycle through the bank (re-shuffling on each
     *   cycle). Within-session duplicates are expected and acceptable.
     *
     * @param  int[]  $bankIds
     * @return int[]
     */
    private function pickForStudent(array $bankIds, int $bankSize, int $required): array
    {
        if ($bankSize >= $required) {
            // Sufficient bank — freshly shuffle, take first $required
            $copy = $bankIds;
            shuffle($copy);

            return array_slice($copy, 0, $required);
        }

        // Insufficient bank — cycle through, re-shuffling on each pass
        $result = [];
        $cycle = $bankIds;
        shuffle($cycle);
        $pos = 0;

        for ($i = 0; $i < $required; $i++) {
            if ($pos >= $bankSize) {
                // Start a new cycle with a fresh shuffle
                shuffle($cycle);
                $pos = 0;
            }
            $result[] = $cycle[$pos++];
        }

        return $result;
    }
}
