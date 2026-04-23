# Livewire Component Contract

Each Livewire component lists its **public properties** (serialized to the client) and
**public methods** (callable via `wire:click`, `wire:poll`, `@this.call(...)`). This is
the UI ↔ server contract of the feature.

---

## `Teacher\Settings`

Public properties:
- `int $examMax`, `int $personalWorkMax`, `int $attendanceMax`, `int $participationMax`

Methods:
- `save()` — validates sum==20, updates teacher's `GradingTemplate`, emits toast.

---

## `Teacher\Modules\Index`

Properties: `array $catalog`, `Collection $myModules`, `string $newName`
Methods: `addFromCatalog(int $catalogId)`, `addCustom()`, `remove(int $moduleId)`

---

## `Teacher\Groups\Index` / `Show`

Index: `Collection $groups`; `create(string $name, int $moduleId, string $level)`, `delete(int $id)`.
Show: `Group $group`, `Collection $students`; `addStudent(string $name, string $email)`, `assignExisting(int $studentId)`, `removeFromGroup(int $studentId)`, `recordAbsence(int $studentId, string $date)`.

---

## `Teacher\Questions\{Index, Create, Edit}`

Index: `Collection $questions`, filter props `?int $moduleId, ?string $level, ?string $difficulty`.
Create/Edit: `string $text, array $choices[4], int $correctIndex, int $moduleId, string $level, string $difficulty`; `save()`.

---

## `Teacher\Exams\Create`

Properties: `int $groupId`, `string $title`, `int $easyCount`, `int $mediumCount`, `int $hardCount`, `int $durationMinutes`, `datetime $scheduledAt`
Methods: `save()` — delegates to `CreateExamAction`; bank-size validation runs server-side.

---

## `Teacher\Exams\Show`

Properties: `Exam $exam`, `Collection $sessions` (one per group student).
Methods: `start()` — delegates to `StartExamAction`, redirects to `Monitor`.

---

## `Teacher\Exams\Monitor` **(critical)**

Polling: `wire:poll.5s="refresh"`.

Properties:
- `Exam $exam`
- `Collection $liveStatuses` — rows of `{student_id, name, status, last_answered_question_index, remaining_seconds, connected:bool, incident_count}`

Methods:
- `refresh()` — recomputes `liveStatuses` from `exam_sessions` + `last_heartbeat_at`; emits Alpine event `student-disconnected` when transitions occur (drives audio alert).
- `extendGlobal(int $minutes)` — `ExtendTimeAction::global`.
- `extendStudent(int $studentId, int $minutes)` — `ExtendTimeAction::student`.
- `endExam()` — `EndExamAction`; finalizes still-active sessions.

---

## `Teacher\Exams\Results`

Properties: `Exam $exam`, `Collection $rows` (per-student score), `array $mostMissed` (top 10 questions by wrong-rate), `float $groupAverage`, `array $passFail`.

---

## `Teacher\Grades\Show`

Properties: `Group $group`, `Collection $entries` (grade_entries joined with students).
Methods: `updateEntry(int $studentId, string $component, float $value)` — validates against template max, upserts `grade_entries`, recomputes `final_grade`.

---

## `Student\Exams\Waiting`

Polling: `wire:poll.5s="poll"`.
Properties: `Exam $exam`, `bool $started`, `int $secondsUntilScheduled`.
Methods: `poll()` — checks if `exam.status === 'active'` AND caller's `ExamSession.status === 'active'`, redirects to `student.exams.session`.

---

## `Student\Exams\Session` **(the locked page)**

Polling: `wire:poll.10s="heartbeat"`.

Properties:
- `ExamSession $session`
- `Collection $assignedQuestions` (Question + choices, already shuffled per render with session-stable seed)
- `string $deadlineIso`
- `int $incidentCount`
- `array $draftSelections` — map {questionId: choiceId}

Methods:
- `heartbeat()` — updates `last_heartbeat_at`, refreshes `$deadlineIso` (picks up extensions), returns no payload.
- `saveDraft(int $questionId, int $choiceId)` — idempotent upsert to `student_answers` via `SaveDraftAnswerAction`.
- `recordIncident(string $kind)` — writes `student_answer_incidents` row; increments `$incidentCount`.
- `submitFinal()` — confirms final submission; delegates to `FinalizeSessionAction`; redirects to results.

Alpine companion script (`alpine/examSession.js`):
- `visibilitychange` / `blur` / `beforeunload` listeners → call `recordIncident`.
- Offline buffer in `localStorage[examSession:{id}:pending]` with retry loop.
- Countdown driven by `$deadlineIso`.

---

## `Student\Exams\Results`

Properties: `ExamSession $session`, `Collection $review` (rows of `{question_text, selected_choice_text, correct_choice_text, is_correct}`), `float $scoreComponent`, `int $rawCorrect`, `int $total`.

---

## Shared: `Auth\*` components

Scaffolded by Breeze Livewire stack; adapted so that login posts to the correct guard
based on the login route (`teacher.login` vs `student.login`).
