<?php

namespace App\Domain\Exam\Actions;

use App\Domain\Exam\Exceptions\BankTooSmallException;
use App\Enums\Difficulty;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Question;
use App\Models\Teacher;

/**
 * Creates a new exam after validating the question bank is large enough.
 *
 * Per contracts/domain-actions.md §CreateExamAction.
 */
class CreateExamAction
{
    /**
     * @param  Teacher  $teacher  The owning teacher.
     * @param  array{
     *     group_id: int,
     *     title: string,
     *     easy_count: int,
     *     medium_count: int,
     *     hard_count: int,
     *     duration_minutes: int,
     *     scheduled_at: string|\DateTimeInterface,
     * }  $dto
     *
     * @throws BankTooSmallException
     */
    public function handle(Teacher $teacher, array $dto): Exam
    {
        /** @var Group $group */
        $group = Group::withoutGlobalScopes()
            ->where('id', $dto['group_id'])
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $this->checkBankSize($teacher, $group, $dto);

        return Exam::create([
            'teacher_id' => $teacher->id,
            'group_id' => $group->id,
            'title' => $dto['title'],
            'easy_count' => $dto['easy_count'],
            'medium_count' => $dto['medium_count'],
            'hard_count' => $dto['hard_count'],
            'duration_minutes' => $dto['duration_minutes'],
            'scheduled_at' => $dto['scheduled_at'],
            'status' => 'scheduled',
        ]);
    }

    /**
     * Validate that the bank holds at least the required number of questions per difficulty.
     *
     * @throws BankTooSmallException
     */
    private function checkBankSize(Teacher $teacher, Group $group, array $dto): void
    {
        $deficits = [];

        $required = [
            Difficulty::Easy->value => (int) ($dto['easy_count'] ?? 0),
            Difficulty::Medium->value => (int) ($dto['medium_count'] ?? 0),
            Difficulty::Hard->value => (int) ($dto['hard_count'] ?? 0),
        ];

        foreach ($required as $difficulty => $count) {
            if ($count === 0) {
                continue;
            }

            $available = Question::withoutGlobalScopes()
                ->where('teacher_id', $teacher->id)
                ->where('module_id', $group->module_id)
                ->where('level', $group->level->value)
                ->where('difficulty', $difficulty)
                ->count();

            if ($available < 1) {
                $deficits[$difficulty] = 1 - $available;
            }
        }

        if (! empty($deficits)) {
            throw new BankTooSmallException($deficits);
        }
    }
}
