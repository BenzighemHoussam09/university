---
description: "Task list for Electronic Exam Platform (MVP)"
---

# Tasks: Electronic Exam Platform (MVP)

**Input**: Design documents from `/specs/001-exam-platform-backend/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/, quickstart.md

**Tests**: Included for critical paths only — cross-teacher isolation (SC-008),
question assignment algorithm (FR-014/015), grading math (FR-028/031), heartbeat
detection (SC-005), and full exam flow (US3). Other areas rely on Livewire
component logic being exercised via feature tests on the top user-facing flows.

**Organization**: Grouped by user story so each can be implemented, tested, and
demoed independently.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies on incomplete tasks)
- **[Story]**: Which user story the task belongs to (US1…US7)

## Path Conventions

Laravel monolith rooted at `D:/laragon/www/university`. All paths below are relative
to the repo root.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and core scaffolding.

- [ ] T001 Install Laravel Breeze with Livewire stack: `composer require laravel/breeze --dev && php artisan breeze:install livewire`. Commit Breeze output verbatim before customization.
- [ ] T002 Configure two auth guards (`teacher`, `student`) in `config/auth.php` with separate providers backed by `App\Models\Teacher` and `App\Models\Student`. Remove default `users` provider.
- [ ] T003 [P] Create `config/exam.php` with keys: `heartbeat_interval_seconds=10`, `heartbeat_window_seconds=25`, `monitor_poll_interval_seconds=5`, `reminder_lead_minutes=30`, `absence_threshold=5`, `finalize_overdue_cadence_seconds=15`.
- [ ] T004 [P] Add `.env.example` keys: `QUEUE_CONNECTION=database`, `MAIL_MAILER=log` (dev default), `DB_*` for MySQL.
- [ ] T005 [P] Configure `pint.json` with Laravel preset; confirm `php artisan pint` runs clean on the initial Breeze scaffold.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure required by ALL user stories.

**CRITICAL**: No user story work may start until this phase is complete.

- [ ] T006 Create enum classes: `app/Enums/Level.php` (L1,L2,L3,M1,M2), `app/Enums/Difficulty.php` (easy,medium,hard), `app/Enums/ExamStatus.php` (draft,scheduled,active,ended), `app/Enums/AnswerStatus.php` (draft,final), `app/Enums/IncidentKind.php` (visibility_hidden,window_blur,navigation_attempt).
- [ ] T007 Create `app/Models/Concerns/BelongsToTeacher.php` trait and `app/Models/Scopes/TeacherScope.php` global scope per `research.md Decision 4`. Trait auto-sets `teacher_id` on `creating` model event using the authenticated guard.
- [ ] T008 Create `app/Models/Teacher.php` + migration `database/migrations/xxxx_create_teachers_table.php` + `database/factories/TeacherFactory.php` per `data-model.md §teachers`. Include `grading_template_id` FK (nullable initially — set by observer in T072).
- [ ] T009 Create `app/Models/Student.php` + migration `database/migrations/xxxx_create_students_table.php` + `database/factories/StudentFactory.php` per `data-model.md §students`. Apply `BelongsToTeacher` trait. Composite unique `(teacher_id,email)`.
- [ ] T010 Create `routes/teacher.php` and `routes/student.php`; register both in `bootstrap/app.php` (Laravel 12 style) with `web` + `auth:<guard>` middleware. Move shared auth routes into `routes/web.php`.
- [ ] T011 [P] Create layout blades: `resources/views/layouts/teacher.blade.php`, `resources/views/layouts/student.blade.php`, `resources/views/layouts/guest.blade.php`. Each layout loads Livewire + Alpine + Tailwind and includes a role-appropriate nav.
- [ ] T012 [P] Create `app/Http/Middleware/EnsureAbsenceBelowThreshold.php` and register alias `absence.threshold` in `bootstrap/app.php`. Logic per `research.md Decision 11`.
- [ ] T013 [P] Create `app/Http/Middleware/EnsureExamNotCompleted.php` (redirects to student results page when the session status is `completed`).
- [ ] T014 Adapt Breeze Auth components for both guards: duplicate `resources/views/livewire/pages/auth/*` into `teacher/auth/*` and `student/auth/*`; update login components to target the correct guard based on route name.
- [ ] T015 [P] Create `database/seeders/ModuleCatalogSeeder.php` (~30 canonical Algerian university module names) and wire it into `DatabaseSeeder`.
- [ ] T016 [P] Create `database/seeders/DefaultGradingTemplateSeeder.php` — inserts `grading_templates` id=1 with `exam_max=12, personal_work_max=4, attendance_max=2, participation_max=2` (sum=20). Wire into `DatabaseSeeder`.
- [ ] T017 [P] Create `database/seeders/DemoTeacherSeeder.php` — seeds `demo.teacher@univ.dz` / `password` and assigns `grading_template_id` = cloned copy of id=1.

**Checkpoint**: Foundation ready — user story phases can now run in parallel.

---

## Phase 3: User Story 1 — Teacher Workspace Setup (P1)

**Goal**: A teacher can sign in, pick/add modules, create groups at any level, and add students who receive login credentials.

**Independent Test**: Fresh teacher logs in, creates one module from catalog, creates one M2 group, adds a student with name+email; student logs in with emailed credentials and sees an empty dashboard.

### Models & Data

- [ ] T018 [P] [US1] Create `app/Models/ModuleCatalog.php` + migration + factory (NOT teacher-scoped) per `data-model.md §modules`.
- [ ] T019 [P] [US1] Create `app/Models/Module.php` + migration + factory. Apply `BelongsToTeacher` trait. Nullable `created_from_catalog_id` FK.
- [ ] T020 [P] [US1] Create `app/Models/Group.php` + migration + factory. Apply `BelongsToTeacher`. Casts: `level => Level::class`. Belongs to Module.
- [ ] T021 [P] [US1] Create `group_student` pivot migration with timestamps and unique index. Add `belongsToMany` relations on `Student` and `Group`.

### Policies

- [ ] T022 [P] [US1] Create `app/Policies/ModulePolicy.php`, `app/Policies/GroupPolicy.php`, `app/Policies/StudentPolicy.php` that rely on the global scope — all methods return true for teacher since cross-teacher access is pre-filtered by scope; fall through to owner checks for safety.

### Livewire components

- [ ] T023 [US1] Create `app/Livewire/Teacher/Modules/Index.php` + `resources/views/livewire/teacher/modules/index.blade.php` implementing `addFromCatalog`, `addCustom`, `remove` per `contracts/livewire-components.md`. Wire route in `routes/teacher.php`.
- [ ] T024 [US1] Create `app/Livewire/Teacher/Groups/Index.php` + blade implementing group CRUD. Wire route.
- [ ] T025 [US1] Create `app/Livewire/Teacher/Groups/Show.php` + blade implementing `addStudent`, `assignExisting`, `removeFromGroup`. Auto-generate 10-char password for new students and queue a `StudentAccountCreated` notification placeholder (actual class lands in US6; use a TODO-wrapped dispatch stub here so US1 ships standalone).
- [ ] T026 [US1] Create `app/Livewire/Teacher/Students/Show.php` + blade showing profile, group memberships, absence list (read-only until US7), grades (read-only until US5).
- [ ] T027 [US1] Create `app/Livewire/Teacher/Dashboard.php` + blade showing counts (students, groups) and placeholders for upcoming exams and notifications. Wire route.
- [ ] T028 [US1] Create `app/Livewire/Student/Dashboard.php` + blade showing empty-state (no exams, absences=0, profile). Wire route.
- [ ] T029 [P] [US1] Create `app/Livewire/Teacher/Profile.php` and `app/Livewire/Student/Profile.php` + blades with name/password change.

### Tests

- [ ] T030 [P] [US1] Create `tests/Feature/Scoping/CrossTeacherIsolationTest.php` asserting teacher B cannot see teacher A's modules, groups, or students (tests the global scope end-to-end).
- [ ] T031 [P] [US1] Create `tests/Feature/Teacher/TeacherWorkspaceSetupTest.php` asserting full US1 acceptance scenarios 1–4.

**Checkpoint**: US1 is functional and independently demo-able. Students can log in.

---

## Phase 4: User Story 2 — Question Bank Management (P1)

**Goal**: A teacher can CRUD MCQ questions (4 choices, 1 correct) tagged with module, level, difficulty, filterable by those tags.

**Independent Test**: Teacher adds a question with 4 choices + 1 correct; edits it; filters by difficulty; deletes another; verifies teacher B cannot see any of it.

### Models & Data

- [ ] T032 [P] [US2] Create `app/Models/Question.php` + migration + factory per `data-model.md §questions`. Apply `BelongsToTeacher`. `hasMany` choices.
- [ ] T033 [P] [US2] Create `app/Models/QuestionChoice.php` + migration (via the same or parallel migration file). Add model event on save to enforce "exactly one correct, exactly four choices" OR validate in the form request.

### Livewire components

- [ ] T034 [US2] Create `app/Livewire/Teacher/Questions/Index.php` + blade with filter props (`moduleId`, `level`, `difficulty`). Wire route.
- [ ] T035 [US2] Create `app/Livewire/Teacher/Questions/Create.php` + blade with 4-choice input, correct-choice radio, module/level/difficulty selectors. Validates the 4/1 invariant server-side.
- [ ] T036 [US2] Create `app/Livewire/Teacher/Questions/Edit.php` + blade reusing the same form partial.

### Tests

- [ ] T037 [P] [US2] Create `tests/Feature/Teacher/QuestionBankTest.php` covering CRUD, filtering, and the 4/1 invariant rejection.
- [ ] T038 [P] [US2] Extend `CrossTeacherIsolationTest` with a case asserting questions are isolated.

**Checkpoint**: US2 is shippable — teacher can build a bank independent of exams.

---

## Phase 5: User Story 3 — End-to-End Exam Session (P1) 🎯 MVP

**Goal**: Teacher creates and starts an exam; students take it under a locked session with auto-save and server-driven timer; on submit or timeout, answers finalize, exam is auto-graded, student sees results.

**Independent Test**: Teacher creates a 6-question exam (3/2/1 distribution) for a 3-student group, starts it, each student answers, submits; teacher results page shows per-student scores + group average.

### Models & Data

- [ ] T039 [P] [US3] Create `app/Models/Exam.php` + migration + factory per `data-model.md §exams`. Apply `BelongsToTeacher`. Casts: `status => ExamStatus::class`.
- [ ] T040 [P] [US3] Create `app/Models/ExamSession.php` + migration + factory per `data-model.md §exam_sessions`. Apply `BelongsToTeacher` (via exam).
- [ ] T041 [P] [US3] Create `app/Models/ExamSessionQuestion.php` + migration per `data-model.md §exam_session_questions`. Unique `(exam_session_id, display_order)`.
- [ ] T042 [P] [US3] Create `app/Models/StudentAnswer.php` + migration per `data-model.md §student_answers`. Unique `(exam_session_id, question_id)` for upserts.
- [ ] T043 [P] [US3] Create `app/Models/StudentAnswerIncident.php` + migration per `data-model.md §student_answer_incidents`.

### Domain services

- [ ] T044 [P] [US3] Create `app/Domain/Exam/Services/DeadlineCalculator.php` as a pure service per `contracts/domain-actions.md §DeadlineCalculator`.
- [ ] T045 [P] [US3] Create `app/Domain/Exam/Services/GradingService.php` with `computeExamComponent(raw, total, examMax)` (full version extended in US5).
- [ ] T046 [US3] Create `app/Domain/Exam/Services/QuestionAssignmentService.php` implementing the algorithm from `research.md Decision 7`. Depends on T032, T040, T041.

### Domain actions

- [ ] T047 [US3] Create `app/Domain/Exam/Actions/CreateExamAction.php` per `contracts/domain-actions.md`. Includes `BankTooSmallException` handling.
- [ ] T048 [US3] Create `app/Domain/Exam/Actions/StartExamAction.php` (depends on T046, T044). Runs inside a DB transaction.
- [ ] T049 [P] [US3] Create `app/Domain/Exam/Actions/SaveDraftAnswerAction.php` — idempotent upsert. Verifies session.status=active and now()<deadline.
- [ ] T050 [US3] Create `app/Domain/Exam/Actions/FinalizeSessionAction.php` — transactional flip + grade computation + dispatch `ResultsAvailable` queued notification (stub here; full class in US6).
- [ ] T051 [P] [US3] Create `app/Domain/Exam/Actions/RecordLockdownIncidentAction.php`.

### Scheduled job

- [ ] T052 [P] [US3] Create `app/Console/Commands/FinalizeOverdueSessionsCommand.php` signature `app:finalize-overdue-sessions`; iterate overdue sessions and call `FinalizeSessionAction`. Register in `routes/console.php` with `->everyFifteenSeconds()`.

### Events

- [ ] T053 [P] [US3] Create `app/Domain/Exam/Events/ExamStarted.php`, `ExamEnded.php`, `SessionFinalized.php`.

### Policies

- [ ] T054 [P] [US3] Create `app/Policies/ExamPolicy.php`.

### Livewire — teacher side

- [ ] T055 [US3] Create `app/Livewire/Teacher/Exams/Index.php` + blade listing exams grouped by status (scheduled/active/ended). Wire route.
- [ ] T056 [US3] Create `app/Livewire/Teacher/Exams/Create.php` + blade (group selector, per-difficulty counts, duration, scheduled_at). Delegates to `CreateExamAction` (T047).
- [ ] T057 [US3] Create `app/Livewire/Teacher/Exams/Show.php` + blade with a Start button that calls `StartExamAction` (T048) and redirects to Monitor (placeholder route until T081 in US4 — in US3 Monitor is a minimal read-only view).
- [ ] T058 [US3] Create `app/Livewire/Teacher/Exams/Results.php` + blade showing per-student score, group average, top-10 most-missed questions. Read-only until grades land in US5.

### Livewire — student side

- [ ] T059 [US3] Create `app/Livewire/Student/Exams/Index.php` + blade — upcoming/ended lists. Wire route.
- [ ] T060 [US3] Create `app/Livewire/Student/Exams/Waiting.php` + blade with `wire:poll.5s` — polls until session.status transitions to `active`, then redirects to `student.exams.session`. Blocks entry before `scheduled_at`.
- [ ] T061 [US3] **THE LOCKED PAGE** — Create `app/Livewire/Student/Exams/Session.php` + blade per `contracts/livewire-components.md §Student\Exams\Session`. Public methods: `heartbeat`, `saveDraft`, `recordIncident`, `submitFinal`. `wire:poll.10s="heartbeat"`. Protect route with `EnsureExamNotCompleted` middleware (T013).
- [ ] T062 [US3] Create `app/Livewire/Student/Exams/Results.php` + blade — per-question review with correctness indicator, total score.

### Alpine

- [ ] T063 [US3] Create `resources/js/alpine/examSession.js` implementing: `visibilitychange` + `window.blur` + `beforeunload` listeners calling `@this.call('recordIncident', kind)`; localStorage buffer at key `examSession:{id}:pending`; retry loop on reconnect (navigator.onLine + fetch probe); countdown driven by `$wire.deadlineIso`. Register in `resources/js/app.js`.

### Tests

- [ ] T064 [P] [US3] Create `tests/Unit/Domain/DeadlineCalculatorTest.php`.
- [ ] T065 [P] [US3] Create `tests/Unit/Domain/QuestionAssignmentServiceTest.php` — property tests: no within-session duplicates when bank sufficient; display_order unique; per-difficulty counts correct; order differs on forced reuse.
- [ ] T066 [P] [US3] Create `tests/Unit/Domain/GradingServiceTest.php` — exam component normalization.
- [ ] T067 [P] [US3] Create `tests/Feature/Exam/FullExamFlowTest.php` — teacher creates 6-question exam, 3 students take it, finalize, results computed correctly.
- [ ] T068 [P] [US3] Create `tests/Feature/Exam/FinalizeOnDeadlineTest.php` — freezes time past deadline, runs `FinalizeOverdueSessionsCommand`, asserts drafts → final and session completed.
- [ ] T069 [P] [US3] Create `tests/Feature/Exam/DraftAnswerPersistenceTest.php` — verifies every saveDraft call writes immediately and is idempotent.

**Checkpoint**: MVP complete — platform can run auto-graded exams end-to-end.

---

## Phase 6: User Story 4 — Live Monitoring & Time Extension (P2)

**Goal**: Teacher sees live student status (last question, remaining time, connected?, incidents) and can extend time globally or individually.

**Independent Test**: During an active US3 exam, open Monitor; verify all students visible with live status; kill a student's network → within 25s the monitor flips them to disconnected and plays the alert; extend time globally +2min → all deadlines update; extend one student +1min → only that student's deadline changes.

### Domain

- [ ] T070 [P] [US4] Create `app/Domain/Exam/Actions/ExtendTimeAction.php` with methods `global(Exam, int $minutes)` and `student(ExamSession, int $minutes)`. Recomputes `deadline` via `DeadlineCalculator` (T044).
- [ ] T071 [P] [US4] Create `app/Domain/Exam/Actions/EndExamAction.php` (FR-041) — atomically calls `FinalizeSessionAction` for each active session in the exam, then sets `exam.status='ended'`. Wrapped in a DB transaction to ensure all-or-nothing semantics.
- [ ] T072 [P] [US4] Create `app/Domain/Exam/Services/HeartbeatMonitor.php` with `isConnected(ExamSession): bool` comparing `last_heartbeat_at` to `now() - heartbeat_window_seconds`.

### Livewire

- [ ] T073 [US4] Create `app/Livewire/Teacher/Exams/Monitor.php` + blade per `contracts/livewire-components.md §Teacher\Exams\Monitor`. `wire:poll.5s="refresh"`. Exposes `extendGlobal` (T070), `extendStudent` (T070), `endExam` (T071, FR-041) with confirmation dialog. Dispatches browser event `student-disconnected` on state transitions.
- [ ] T074 [US4] Replace the placeholder monitor link from T057 with the real Monitor route.

### Alpine

- [ ] T075 [US4] Create `resources/js/alpine/monitor.js` — listens for `student-disconnected` browser event and plays an audio alert (`public/sounds/alert.mp3`). Register in `resources/js/app.js`.

### Tests

- [ ] T076 [P] [US4] Create `tests/Feature/Monitor/HeartbeatDetectionTest.php` — sets `last_heartbeat_at` > window ago, refreshes monitor, asserts disconnected state.
- [ ] T077 [P] [US4] Create `tests/Feature/Monitor/TimeExtensionTest.php` — global and per-student; asserts `deadline` recomputed correctly and only targeted sessions affected.

**Checkpoint**: US4 live monitoring works on top of the MVP exam flow.

---

## Phase 7: User Story 5 — Grading Components & Final Grade (P2)

**Goal**: Teacher configures per-component maxes (sum=20) in settings, enters manual values per student per module; final grade is the direct sum on /20.

**Independent Test**: Open settings, change maxes to (10,4,3,3); save. Open `/teacher/grades/{group}`; enter personal_work=4, attendance=2, participation=3 for a student whose exam_component is 8.0; assert final_grade=17.0. Student sees the same breakdown.

### Models

- [ ] T078 [P] [US5] Create `app/Models/GradingTemplate.php` + migration + factory per `data-model.md §grading_templates`. Add `scopeEnsureSumIsTwenty()` enforcement on save (throw `InvalidGradingTemplateException`).
- [ ] T079 [P] [US5] Create `app/Models/GradeEntry.php` + migration + factory. Unique `(student_id, module_id)`. Apply `BelongsToTeacher`.
- [ ] T080 [P] [US5] Create `app/Observers/TeacherObserver.php` — on teacher `created`, clone `grading_templates` id=1 (seeded by T016, which MUST run first) into a new row with `teacher_id = $teacher->id` and set `$teacher->grading_template_id = $cloned->id`. Register in `AppServiceProvider`.

### Domain

- [ ] T081 [P] [US5] Extend `app/Domain/Exam/Services/GradingService.php` (from T045) with `validateComponentValue(string $component, float $value, GradingTemplate)` and `computeFinalGrade(GradeEntry): float`.
- [ ] T082 [P] [US5] Extend `FinalizeSessionAction` (T050) to: (1) compute the normalized exam component score using GradingService (raw_correct / total_assigned × exam_max per FR-031), (2) upsert `grade_entries` with this normalized `exam_component` value, (3) recompute `final_grade` as the sum of all four components.

### Livewire

- [ ] T083 [US5] Create `app/Livewire/Teacher/Settings.php` + blade — four inputs with live sum indicator; save rejects if sum != 20.
- [ ] T084 [US5] Create `app/Livewire/Teacher/Grades/Show.php` + blade (`teacher.grades.show` route, param `{group}`) — grid of students × components with inline validation against template maxes; final_grade column computed live.
- [ ] T085 [US5] Create `app/Livewire/Student/Grades/Index.php` + blade — consolidated /20 report across modules with per-component breakdown.

### Tests

- [ ] T086 [P] [US5] Create `tests/Feature/Grading/GradingTemplateValidationTest.php` — rejects saves where components don't sum to 20.
- [ ] T087 [P] [US5] Create `tests/Feature/Grading/FinalGradeComputationTest.php` — covers US5 acceptance scenarios 1–4 + the "existing values flagged when max lowered" edge case.

**Checkpoint**: Full gradebook flow complete.

---

## Phase 8: User Story 6 — Notifications (P3)

**Goal**: Async email + in-platform notifications for: student account creation, exam reminder, results available.

**Independent Test**: Add a student → check `storage/logs/laravel.log` (mail driver=log) for the credentials email and `in_platform_notifications` row; schedule an exam for +30min → wait for scheduler to fire → notification dispatched; finalize a student's exam → results-available notification fires.

### Models

- [ ] T088 [P] [US6] Create `app/Models/InPlatformNotification.php` + migration per `data-model.md §in_platform_notifications`.

### Notification classes

- [ ] T089 [P] [US6] Create `app/Notifications/StudentAccountCreated.php` with `mail` + custom `database`-like channel (or simply insert an `in_platform_notifications` row in `toArray`). Implements `ShouldQueue`.
- [ ] T090 [P] [US6] Create `app/Notifications/ExamReminder.php` (ShouldQueue).
- [ ] T091 [P] [US6] Create `app/Notifications/ResultsAvailable.php` (ShouldQueue).

### Scheduler

- [ ] T092 [P] [US6] Create `app/Console/Commands/DispatchExamRemindersCommand.php` signature `app:dispatch-exam-reminders` per `research.md Decision 2`. Register in `routes/console.php` with `->everyMinute()`.

### Wire-in points

- [ ] T093 [P] [US6] Update T025 (`Teacher\Groups\Show::addStudent`) to dispatch `StudentAccountCreated`.
- [ ] T094 [P] [US6] Update T050 (`FinalizeSessionAction`) to dispatch `ResultsAvailable`.

### Livewire — inbox pages

- [ ] T095 [US6] Create `app/Livewire/Teacher/Notifications/Index.php` + blade. Wire route.
- [ ] T096 [US6] Create `app/Livewire/Student/Notifications/Index.php` + blade. Wire route.

### Tests

- [ ] T097 [P] [US6] Create `tests/Feature/Notifications/NotificationDispatchTest.php` — uses `Notification::fake()` to assert each event fires the correct class.
- [ ] T098 [P] [US6] Create `tests/Feature/Notifications/ReminderIdempotencyTest.php` — runs reminder command twice, asserts only one dispatch per exam via `reminders_sent_at` guard.

**Checkpoint**: Notifications operational across all three events.

---

## Phase 9: User Story 7 — Absence Tracking & Threshold Block (P3)

**Goal**: Teacher records absences; students exceeding threshold are blocked from signing in.

**Independent Test**: Record 4 absences for a student → student can still log in, dashboard shows "1 remaining". Record the 5th → student cannot log in, sees block message even on direct exam URLs.

### Model

- [ ] T099 [P] [US7] Create `app/Models/Absence.php` + migration + factory per `data-model.md §absences`. Apply `BelongsToTeacher`.

### Observer

- [ ] T100 [P] [US7] Create `app/Observers/AbsenceObserver.php` — on `created`, increments `students.absence_count` and sets `blocked_at` when count ≥ `config('exam.absence_threshold')`; on `deleted`, reverses. Register in `AppServiceProvider`.

### Action

- [ ] T101 [P] [US7] Create `app/Domain/Students/Actions/RecordAbsenceAction.php`.

### UI wire-in

- [ ] T102 [US7] Extend `Teacher\Students\Show` (T026) with an "Add absence" form and the absence history list.
- [ ] T103 [US7] Extend `Student\Dashboard` (T028) to show `remaining = threshold - absence_count`.

### Tests

- [ ] T104 [P] [US7] Create `tests/Feature/Absences/AbsenceThresholdBlockTest.php` — records absences up to threshold, asserts login refused and direct exam URL access refused.
- [ ] T105 [P] [US7] Create `tests/Feature/Absences/AbsenceCounterSyncTest.php` — add/delete absences; assert `absence_count` remains consistent.

**Checkpoint**: All seven user stories complete.

---

## Phase 10: Polish & Cross-Cutting Concerns

- [ ] T106 [P] Run `php artisan pint` across the whole codebase and commit style fixes.
- [ ] T107 [P] Add `composer.json` script `"ci": "pint --test && phpunit"` and document in README/quickstart.
- [ ] T108 [P] Validate SC-003 (≤2s answer persist) and SC-005 (≤15s disconnect detection) with a timed feature test in `tests/Feature/Performance/SlaTest.php`.
- [ ] T109 [P] Security sweep: confirm every route group has the correct guard; audit `fillable`/`guarded` on all Teacher-scoped models to prevent mass-assignment bypassing `teacher_id`.
- [ ] T110 Update `quickstart.md` with any deltas discovered during implementation (routes that changed, seeder tweaks).
- [ ] T111 Run `composer run test` and ensure green end-to-end.

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (T001–T005)**: No dependencies — start immediately.
- **Foundational (T006–T017)**: Depends on Setup. **BLOCKS** all user stories.
- **US1 (T018–T031)**: Depends on Foundational. Independent of other stories.
- **US2 (T032–T038)**: Depends on Foundational and on Module from US1 (T019). If US1 not shipped, US2 can still run if T019 is lifted into Foundational.
- **US3 (T039–T069)**: Depends on Foundational, US1 models (Group, Student — T020, T009), and US2 models (Question, QuestionChoice — T032, T033). MVP finishes here.
- **US4 (T070–T077)**: Depends on US3 (needs `ExamSession`, `FinalizeSessionAction`). Does not block US5.
- **US5 (T078–T087)**: Depends on US3 (needs `FinalizeSessionAction` to feed `grade_entries`). Does not block US4.
- **US6 (T088–T098)**: Depends on US1 (student creation hook), US3 (finalize hook), US5 optional but not required.
- **US7 (T099–T105)**: Depends on Foundational (`EnsureAbsenceBelowThreshold` in T012). Independent of other stories.
- **Polish (T106–T111)**: Runs last.

### Within Each User Story

- Migrations/models → services → actions → Livewire components → tests.
- Tests marked [P] can run alongside implementation tasks but must be runnable in isolation.

### Parallel Opportunities

- Setup: T003, T004, T005 in parallel.
- Foundational: T011, T012, T013, T015, T016, T017 in parallel after T006–T010.
- US1 models T018–T021 in parallel; then Livewire components sequentially (share routing file) or in parallel if touching different files; tests T030, T031 in parallel.
- US3 models T039–T043 in parallel; domain services T044, T045 in parallel (T046 sequential after T045); actions T049, T051 in parallel; tests T064–T069 all parallel.
- US4, US5, US7 can all proceed in parallel by different developers once US3 ships.
- US6 classes T089, T090, T091 in parallel; scheduler T092 in parallel with them.

---

## Parallel Example: US3 MVP kickoff

```bash
# After Foundational completes, launch US3 model layer in parallel:
Task: "T039 [US3] Exam model + migration + factory in app/Models/Exam.php"
Task: "T040 [US3] ExamSession model + migration + factory in app/Models/ExamSession.php"
Task: "T041 [US3] ExamSessionQuestion model + migration in app/Models/ExamSessionQuestion.php"
Task: "T042 [US3] StudentAnswer model + migration in app/Models/StudentAnswer.php"
Task: "T043 [US3] StudentAnswerIncident model + migration in app/Models/StudentAnswerIncident.php"

# Then pure services in parallel:
Task: "T044 [US3] DeadlineCalculator service in app/Domain/Exam/Services/DeadlineCalculator.php"
Task: "T045 [US3] GradingService stub in app/Domain/Exam/Services/GradingService.php"
```

---

## Implementation Strategy

### MVP First — Ship after Phase 5

1. Phase 1 Setup.
2. Phase 2 Foundational.
3. Phase 3 US1 (teacher workspace).
4. Phase 4 US2 (question bank).
5. Phase 5 US3 (end-to-end exam).
6. **STOP. Validate.** Run `FullExamFlowTest`, run the quickstart smoke test manually. Demo to stakeholders.

### Incremental delivery

After MVP:

- Layer US4 (monitoring) — highest immediate classroom value.
- Layer US5 (grading) — required before replacing paper sheets.
- Layer US6 (notifications) — usability improvement.
- Layer US7 (absence block) — compliance feature.

### Parallel team split (if >1 developer)

Once Foundational is done:

- Dev A: US1 + US2 → US3.
- Dev B (after US3 merges): US4 monitoring.
- Dev C (after US3 merges): US5 grading.
- Dev D (any time): US7 absences (independent).
- Dev E (after US1 + US3): US6 notifications.

---

## Notes

- All Livewire components live under `app/Livewire/{Teacher,Student}/...`; their blades mirror the path under `resources/views/livewire/...`.
- Every teacher-owned model gets the `BelongsToTeacher` trait — no exceptions.
- Domain actions are the single write path for their aggregate; Livewire never writes to teacher-owned tables directly.
- Tests live under `tests/Feature/` (HTTP/Livewire) and `tests/Unit/Domain/` (pure PHP services).
- Commit after each task or logical group. Run `php artisan pint` before each commit.
- Stop at any checkpoint to demo the increment independently.
