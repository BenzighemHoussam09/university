<?php

namespace App\Domain\Exam\Actions;

use App\Models\ExamSession;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * Idempotent upsert of a student's draft answer.
 *
 * Per contracts/domain-actions.md §SaveDraftAnswerAction.
 */
class SaveDraftAnswerAction
{
    /**
     * @throws ValidationException
     */
    public function handle(ExamSession $session, int $questionId, int $choiceId): StudentAnswer
    {
        $student = Auth::guard('student')->user();

        // Verify the session belongs to the authenticated student
        abort_if($session->student_id !== $student->id, 403);

        // Verify the session is still active
        abort_if($session->status !== 'active', 422, 'Session is not active.');

        // Verify the deadline has not passed
        abort_if(now()->gt($session->deadline), 422, 'Session deadline has passed.');

        // Verify the question is assigned to this session
        $isAssigned = $session->assignedQuestions()
            ->where('question_id', $questionId)
            ->exists();
        abort_if(! $isAssigned, 422, 'Question is not assigned to this session.');

        // Verify the choice belongs to the question
        $validChoice = \DB::table('question_choices')
            ->where('id', $choiceId)
            ->where('question_id', $questionId)
            ->exists();
        abort_if(! $validChoice, 422, 'Choice does not belong to the question.');

        // Upsert — idempotent by (exam_session_id, question_id)
        return StudentAnswer::updateOrCreate(
            [
                'exam_session_id' => $session->id,
                'question_id' => $questionId,
            ],
            [
                'selected_choice_id' => $choiceId,
                'status' => 'draft',
            ]
        );
    }
}
