# Phase 0 Research — Electronic Exam Platform (MVP)

All spec-level ambiguities were resolved in the `/speckit.clarify` step. The remaining
decisions are planning-level: concrete values, library patterns, and Laravel/Livewire
idioms.

---

## Decision 1 — Heartbeat interval and disconnect window

- **Decision**: Student heartbeat every **10 seconds** via Livewire `wire:poll.10s`
  calling a `heartbeat()` method that updates `ExamSession.last_heartbeat_at`.
  Teacher monitor polls every **5 seconds**. A student is considered "disconnected"
  when `last_heartbeat_at < now() - 25s` (2.5 × heartbeat + slack).
- **Rationale**: Gives an end-to-end detection of ≤ 15s with comfortable headroom for
  SC-005. 10s heartbeat keeps per-student request rate at 6 req/min — affordable for
  a classroom of ~50 students (5 req/s sustained).
- **Alternatives considered**:
  - 5s heartbeat: too chatty (600 req/min for 50 students) without UX benefit.
  - 30s heartbeat: misses SC-005 ceiling.
- **Stored in**: `config/exam.php` → `heartbeat_interval_seconds = 10`,
  `heartbeat_window_seconds = 25`, `monitor_poll_interval_seconds = 5`.

## Decision 2 — Reminder dispatch mechanism

- **Decision**: Scheduled console command `app:dispatch-exam-reminders` runs every
  minute via `php artisan schedule:run` (cron). It queries exams whose
  `scheduled_at BETWEEN now()+R-1 AND now()+R` (where `R = reminder_lead_minutes =
  30`) and have `reminders_sent_at IS NULL`, then dispatches `ExamReminder`
  notification to every student in the group via queue.
- **Rationale**: Pure Laravel primitives; deterministic; idempotent via
  `reminders_sent_at` guard; uses existing queue so handles 5xx mail failures.
- **Alternatives considered**:
  - At-schedule-time delayed jobs (dispatch on exam creation with `->delay()`): fails
    if exam is rescheduled; complicates cancellation.
  - Polling from the monitor page: requires teacher to be online.

## Decision 3 — Two-guard Breeze setup

- **Decision**: Install Breeze Livewire scaffolding, then rename the default `web`
  guard to `teacher` and add a parallel `student` guard. Both guards use the
  `bcrypt` password hasher. Separate Eloquent user models (`Teacher`, `Student`) with
  distinct table backings (`teachers`, `students`). Password reset tokens live in one
  `password_reset_tokens` table keyed by email (Laravel default).
- **Rationale**: Minimal deviation from Breeze; keeps per-role attributes naturally on
  their own tables (students have `teacher_id`, `absence_count` cached column;
  teachers have `grading_template_id` etc.); session cookie namespacing via
  `config/session.php` `cookie` can stay shared — Laravel distinguishes by guard.
- **Alternatives considered**:
  - Single `users` table with `role` column: breaks teacher-scoping cleanly because
    students need `teacher_id` while teachers don't; adds polymorphic noise.
  - Jetstream: overkill and conflicts with Livewire 4 paths.

## Decision 4 — `BelongsToTeacher` global scope

- **Decision**: Trait `App\Models\Concerns\BelongsToTeacher` applies a global scope
  `TeacherScope` on every Eloquent boot that filters WHERE `teacher_id =
  auth('teacher')->id()` when the teacher guard is authenticated, OR WHERE
  `teacher_id = auth('student')->user()->teacher_id` when the student guard is
  authenticated. When no guard is authenticated (e.g., in console commands / queue
  workers), the scope is bypassed and the CALLER MUST filter explicitly.
- **Rationale**: Eliminates the need to remember teacher-scoping on ad-hoc queries;
  fail-closed in authenticated web context. Console/queue bypass is deliberate so that
  scheduled tasks (reminder dispatch, cleanup) can query across teachers.
- **Alternatives considered**:
  - Manual `->where('teacher_id', …)` on every query: relies on developer discipline,
    fails closed under forgetfulness. Violates Principle I.
  - Row-level security at DB level: MySQL doesn't support it natively.
- **Test strategy**: `tests/Feature/Scoping/CrossTeacherIsolationTest.php` tries to
  fetch other teachers' resources as an authenticated teacher and asserts 404/empty.

## Decision 5 — Locked exam page & lockdown violation capture

- **Decision**: `Student\Exams\Session` Livewire component embeds an Alpine
  `x-data="examSession()"` directive. The Alpine script:
  - Listens to `document` `visibilitychange` and `window` `blur` events.
  - On each violation, calls `@this.call('recordIncident', {kind})` which invokes
    `RecordLockdownIncidentAction` server-side (writes
    `student_answer_incidents` row, increments a cached counter on the session).
  - Shows an inline warning banner with the running counter.
  - Also tracks navigation attempts via `beforeunload` and blocks them when session
    is active.
- **Rationale**: Keeps the authoritative incident log on the server (audit trail,
  per Principle II), while Alpine handles immediate UX.
- **Note**: Full "kiosk mode" (fullscreen enforcement, right-click block) is out of
  scope for MVP per the brainstorm. We do not attempt browser OS-level lock.

## Decision 6 — Offline answer buffering

- **Decision**: Alpine script wraps each answer click: it calls
  `@this.call('saveDraft', questionId, choiceId)` AND stores `{questionId, choiceId,
  clientTimestamp}` in `localStorage[examSession:{sessionId}:pendingAnswers]`. On
  Livewire success callback, the entry is removed. If Livewire is offline/errors,
  the entry stays and is retried on reconnect (polled every 3s via `navigator.onLine`
  + fetch probe).
- **Rationale**: Satisfies FR-019 and SC-004. The server remains authoritative —
  `saveDraft` is idempotent (upsert by session+question) so replay is safe.

## Decision 7 — Question assignment algorithm

- **Decision**: `QuestionAssignmentService::assign(Exam $exam, Collection $students)`
  runs inside the `StartExamAction` transaction:
  1. For each difficulty (easy/medium/hard), fetch the teacher's bank matching
     `module_id`, `level`, `difficulty`.
  2. Compute how many copies of each question must be distributed across all
     students: `ceil(students × required_per_student / bank_size)` — the cycle count.
  3. Build a flat list by repeating the bank `cycle_count` times, shuffling each cycle
     independently, and truncating to `students × required_per_student`.
  4. Chunk into per-student slices, then shuffle that slice to randomize question
     order per student. Persist as `exam_session_questions` rows with a
     `display_order` column.
- **Rationale**: Deterministic, uses only `shuffle()` (PHP's Fisher–Yates), and
  satisfies both the "exhaust first" rule and the "order differs on reuse" rule with
  high probability. Testable via property test: given `students=10, per=5, bank=20`,
  no duplicates per student; given `bank=5`, each student still sees a unique order.
- **Choice order** is NOT pre-persisted; it is shuffled at render time in the Livewire
  component using a session-scoped seed so the order is stable during the session
  (preventing answer-tracking confusion on re-render).

## Decision 8 — Deadline, timing, and server trust

- **Decision**: `ExamSession.deadline` is a DATETIME column recomputed by
  `DeadlineCalculator::for(ExamSession $s): Carbon` as
  `$s->started_at + duration_minutes + global_extra_minutes + student_extra_minutes`.
  It is persisted at Start and re-persisted on every `ExtendTimeAction`. The
  student's browser displays a countdown driven by Alpine reading
  `deadline` (ISO8601) from the Livewire component's public property; every
  heartbeat refreshes that property so extensions propagate within 10s.
  Finalization-on-deadline is enforced server-side by a scheduled job
  `FinalizeOverdueSessionsJob` that runs every 15 seconds (via Laravel's scheduler
  `everyFifteenSeconds()`).
- **Rationale**: Client display is cosmetic; the job guarantees finalization within
  30s of deadline (SC-006) even if the student closed the browser.

## Decision 9 — Final submission atomicity

- **Decision**: `FinalizeSessionAction` runs in a DB transaction:
  - `UPDATE student_answers SET status='final' WHERE exam_session_id = ? AND status='draft'`
  - Computes `exam_score_raw` (correct count) and normalizes to `exam_score_component`
    using the teacher's grading template exam-max.
  - Sets `exam_sessions.status='completed'`, `completed_at=now()`.
  - Upserts a `grade_entries` row for (student, module) with the exam component filled.
  - Dispatches `ResultsAvailable` notification to the queue.
- **Rationale**: Atomic writes + queue dispatch keep the web request responsive.

## Decision 10 — Grading component constraint

- **Decision**: `GradingTemplate` has columns `exam_max`, `personal_work_max`,
  `attendance_max`, `participation_max` (all unsigned SMALLINT). A model-level
  validation rule `ensureSumIsTwenty()` runs on save; failure throws
  `InvalidGradingTemplateException` returning a 422 to the Livewire form.
- **Rationale**: Simplest enforcement of FR-028/029 invariant; single transaction point.

## Decision 11 — Absence threshold middleware

- **Decision**: Middleware `EnsureAbsenceBelowThreshold` registered on the `student`
  guard's default group. On every request it checks
  `$student->absence_count >= config('exam.absence_threshold', 5)` and if so logs the
  student out and redirects to a block page. `absence_count` is a cached counter column
  on `students` incremented by the `RecordAbsenceAction`.
- **Rationale**: O(1) per request. Threshold change via admin settings picks up on next
  request.

## Decision 12 — Testing posture

- **Decision**: PHPUnit with these test categories:
  - **Unit tests** (`tests/Unit/Domain/**`): pure domain services and actions with
    in-memory fakes. Target: `QuestionAssignmentService`, `DeadlineCalculator`,
    `GradingService`.
  - **Feature tests** (`tests/Feature/**`): full HTTP + Livewire using Laravel's
    `RefreshDatabase` trait. Cover every acceptance scenario from spec.
  - **Scoping tests** (`tests/Feature/Scoping/**`): assert SC-008 zero cross-teacher
    leakage by setting up two teachers and attempting access patterns.
  - **Browser tests** (Dusk): **DEFERRED** — lockdown behaviors validated by
    Livewire component tests that assert the public methods `recordIncident`,
    `heartbeat`, etc. fire correctly. Real-browser visibility events require Dusk
    which adds setup overhead; defer until post-MVP.
- **Rationale**: Satisfies constitution + Laravel conventions without premature
  investment in browser automation.

---

**All Phase-0 research decisions resolved. No remaining NEEDS CLARIFICATION.**
