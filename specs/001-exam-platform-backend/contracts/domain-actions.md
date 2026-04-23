# Domain Action Contract

Invokable PHP classes under `app/Domain/Exam/Actions`. Each exposes a single
`__invoke` or `handle` method and is the ONLY way to mutate the corresponding
aggregate. Livewire components and console commands MUST use these — no inline writes.

All actions run inside a DB transaction where multi-row writes are involved.

---

## `CreateExamAction`

Signature: `handle(Teacher $teacher, array $dto): Exam`

Preconditions:
- `$teacher` matches authenticated teacher guard.
- `$dto.group_id` belongs to `$teacher`.
- Bank size check: for each difficulty, `questions.count(module=$group.module, level=$group.level, difficulty=X) >= X_count`; otherwise returns a `BankTooSmallException` carrying per-difficulty deficit.
- `scheduled_at` > now().

Effects: inserts `exams` row with `status='scheduled'`.

---

## `StartExamAction`

Signature: `handle(Exam $exam): void`

Preconditions:
- `exam.status === 'scheduled'`.
- Called by owning teacher.

Effects (single transaction):
1. Set `exam.status='active'`, `started_at=now()`.
2. Load all students in `exam.group`; for each, create `exam_sessions` row with `status='active'`.
3. Invoke `QuestionAssignmentService::assign($exam, $students)` to populate `exam_session_questions`.
4. For each session, set `deadline = started_at + duration_minutes` (computed via `DeadlineCalculator`).

Emits: `ExamStarted` event.

---

## `AssignQuestionsAction` (thin wrapper)

Delegates to `QuestionAssignmentService`; separated so tests can stub it.

---

## `SaveDraftAnswerAction`

Signature: `handle(ExamSession $session, int $questionId, int $choiceId): StudentAnswer`

Preconditions:
- `$session->student_id === auth('student')->id()`.
- `$session->status === 'active'`.
- `now() < $session->deadline`.
- `$questionId` ∈ session's assigned questions.
- `$choiceId` ∈ that question's choices.

Effects: UPSERT `student_answers` (key: `exam_session_id+question_id`) with
`selected_choice_id`, `status='draft'`.

Idempotent by design: replaying the same call is a no-op beyond `updated_at`.

---

## `FinalizeSessionAction`

Signature: `handle(ExamSession $session, string $reason='manual'): void`  // reason ∈ manual|deadline|teacher_ended

Effects (single transaction):
1. `UPDATE student_answers SET status='final' WHERE session = $session AND status='draft'`.
2. Compute `exam_score_raw` (correct count), `exam_score_component = round(raw / total × template.exam_max, 2)`.
3. Update session: `status='completed', completed_at=now(), exam_score_raw, exam_score_component`.
4. Upsert `grade_entries` (student_id, module_id) with new `exam_component`; recompute `final_grade = sum(4 components)`.
5. Dispatch `ResultsAvailable` notification (queued).

Emits: `SessionFinalized` event.

---

## `ExtendTimeAction`

Methods:
- `global(Exam $exam, int $minutes): void` — add `$minutes` to `exams.global_extra_minutes`; recompute `deadline` for every `exam_sessions` of that exam where `status='active'`.
- `student(ExamSession $session, int $minutes): void` — add to `student_extra_minutes`; recompute that session's `deadline`.

---

## `EndExamAction`

Signature: `handle(Exam $exam): void`

Effects: for every `exam_sessions` where `status='active'`, call
`FinalizeSessionAction::handle($session, 'teacher_ended')`. Then set
`exam.status='ended', ended_at=now()`. Emits `ExamEnded`.

---

## `RecordLockdownIncidentAction`

Signature: `handle(ExamSession $session, string $kind): void`
Inserts a `student_answer_incidents` row. Does NOT change session status. The
Monitor page reads aggregated counts from this table.

---

## `RecordAbsenceAction`

Signature: `handle(Teacher $teacher, Student $student, Carbon $date): Absence`

Effects: insert `absences` row, increment `students.absence_count`, set
`students.blocked_at=now()` if count ≥ threshold.

---

## `FinalizeOverdueSessionsJob` (scheduled, every 15 seconds)

Not an action but a console handler. Queries:
```sql
SELECT id FROM exam_sessions
WHERE status='active' AND deadline < NOW()
```
and dispatches `FinalizeSessionAction` for each with `reason='deadline'`.

---

## Service: `QuestionAssignmentService`

Method: `assign(Exam $exam, Collection $students): void`

Per `research.md Decision 7`. Writes `exam_session_questions` rows.

Property-tested invariants:
- When bank is sufficient, no question appears more than once within a single student's session.
- No two students have identical `display_order → question_id` tuple sequences.
- Count per difficulty per student matches the exam's configured distribution.

---

## Service: `DeadlineCalculator`

Method: `for(ExamSession $session): Carbon` →
`$session->started_at->addMinutes($session->exam->duration_minutes + $session->exam->global_extra_minutes + $session->student_extra_minutes)`

Pure function. Unit-testable with no dependencies.

---

## Service: `GradingService`

Methods:
- `computeExamComponent(int $rawCorrect, int $total, int $examMax): float`
- `computeFinalGrade(GradeEntry $entry): float` → `exam_component + personal_work + attendance + participation`
- `validateComponentValue(string $component, float $value, GradingTemplate $template): void` — throws on out-of-range.
