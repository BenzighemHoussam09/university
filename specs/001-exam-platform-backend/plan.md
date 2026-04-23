# Implementation Plan: Electronic Exam Platform (MVP)

**Branch**: `001-exam-platform-backend` | **Date**: 2026-04-14 | **Spec**: [spec.md](./spec.md)
**Input**: Feature specification from `/specs/001-exam-platform-backend/spec.md`

## Summary

Deliver the university exam platform MVP as a Laravel 12 + Livewire 4 server-rendered
application. Two role-specific auth guards (`teacher`, `student`) gate separate
dashboards. Every teacher-owned record carries `teacher_id` and is filtered by a global
scope trait. Core exam flow is: teacher creates exam + schedules it → student enters a
locked waiting room → teacher clicks Start → questions are drawn and assigned per
student with three-level randomization → Livewire page holds all questions with Alpine
handling lockdown, heartbeat, and offline buffering → each choice is persisted as a
draft row → final submission (manual or timer) atomically flips drafts to final, grades
auto-compute, and the student lands on results. Teacher's monitor page uses Livewire
polling for heartbeat + incident tracking and exposes global/individual time extension.
Final grades combine the auto-graded exam with three manually entered components where
each component has its own configurable max and the four maxes always sum to 20.

## Technical Context

**Language/Version**: PHP 8.3 (Laravel 12 baseline is PHP ^8.2)
**Primary Dependencies**: Laravel 12, Livewire 4, Alpine.js (shipped with Livewire),
Tailwind CSS v4, Laravel Breeze (auth scaffolding), Laravel Queue (database driver
acceptable for MVP), Laravel Mail, `spatie/laravel-permission` NOT used (two hard-coded
guards instead).
**Storage**: MySQL 8 (single schema); InnoDB; UTF8MB4. Redis optional for queue/cache
in production but DB driver sufficient for MVP.
**Testing**: PHPUnit via `composer run test`; feature tests under `tests/Feature`, unit
tests under `tests/Unit`; Livewire component tests using `Livewire::test()`; Laravel
Pint for code style (CI-enforced).
**Target Platform**: Linux or Windows + PHP-FPM web server (Laragon on dev); one
`queue:work` worker for notifications; `php artisan schedule:run` invoked via cron for
reminder dispatch.
**Project Type**: Web application (Laravel server-rendered with Livewire — no separate
SPA/mobile client). Single Laravel codebase.
**Performance Goals**: Answer-choice persistence ≤ 2s round-trip (SC-003); heartbeat-
stop surfaced to teacher ≤ 15s end-to-end (SC-005); timer-expiry finalization for all
still-active students ≤ 30s (SC-006); email delivery 95% within 2 min (SC-010).
**Constraints**: Two separate auth guards with distinct session cookies and middleware
groups; every teacher-owned query MUST pass through the `BelongsToTeacher` global scope;
deadline is ALWAYS server-computed; `localStorage` is client-side buffer only and never
a source of truth; student exam session page is fully locked via Alpine
`visibilitychange` + `blur`; all notifications dispatched to the queue.
**Scale/Scope**: Tens to low-hundreds of students per exam session; per-teacher question
banks in the hundreds to low-thousands; single-institution tenancy enforced by
`teacher_id` scoping; no multi-region, no horizontal sharding needed for MVP.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

Constitution version referenced: **1.0.0** (ratified 2026-04-14).

| Principle | Status | Evidence |
|---|---|---|
| I. Teacher-Scoped Data Isolation | **PASS** | All teacher-owned models (`Module`, `Group`, `Student`, `Question`, `Exam`, `ExamSession`, `StudentAnswer`, `GradingTemplate`, `Absence`, `Notification`) carry `teacher_id` and apply the `BelongsToTeacher` global scope trait. No raw queries bypass the scope. Contract tests will assert cross-teacher isolation. |
| II. Server-Side Authority | **PASS** | `deadline` stored on `ExamSession`, computed by `DeadlineCalculator` at every mutation (start, global_extra, student_extra). Answer persistence goes through `StoreAnswerAction` writing draft rows server-side. `localStorage` is used only as an offline buffer by `alpine/examSession.js` and syncs to the same action endpoint. Grade totals computed by `GradingService`, never client. |
| III. Three-Level Exam Randomization | **PASS** | `QuestionAssignmentService` invoked at `Exam::start()` exhausts bank per difficulty without repeats across students, reshuffles on cycle, then stores a per-session question list with unique per-student order. Choice order is re-shuffled on every render in the Livewire component. Property-based tests will verify independence of the three layers. |
| IV. Exam Session Integrity | **PASS** | `ExamSessionPage` Livewire component + Alpine directives enforce `visibilitychange`/`blur` detection; every choice triggers `saveDraft()` Livewire action writing the `StudentAnswer.status=draft` row; `FinalizeSessionAction` is atomic (DB transaction) flipping drafts → final and redirecting to results. Back-navigation blocked via `completed` status guard middleware. |
| V. Real-Time Observability | **PASS** | `Student\ExamSession` emits heartbeats via Livewire polling (default 10s). `Teacher\MonitorPage` polls every 5s to render live state plus incident counts. Missed heartbeat detection compares `last_heartbeat_at` to `now() - HEARTBEAT_WINDOW`; alert sound plays via Alpine on state transition. Global/individual time extensions flow through `ExtendTimeAction` updating `ExamSession.global_extra`/`student_extra` and recalculating `deadline`. |

**Initial gate: PASS. No violations to justify.**

Post-design re-check (after Phase 1): **PASS — still no violations.** Design introduces
no new patterns that conflict with principles.

## Project Structure

### Documentation (this feature)

```text
specs/001-exam-platform-backend/
├── plan.md              # This file (/speckit.plan output)
├── research.md          # Phase 0 output (/speckit.plan output)
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
│   ├── routes.md
│   ├── livewire-components.md
│   └── domain-actions.md
├── checklists/
│   └── requirements.md
└── tasks.md             # Created by /speckit.tasks (NOT here)
```

### Source Code (repository root)

Laravel 12 monolithic web app. No separate frontend project. Livewire components live
under `app/Livewire` and their blades under `resources/views/livewire`. Guards split
teacher and student namespaces both in code and in routes.

```text
app/
├── Models/
│   ├── Teacher.php
│   ├── Student.php
│   ├── Module.php
│   ├── Group.php
│   ├── Question.php
│   ├── QuestionChoice.php
│   ├── Exam.php
│   ├── ExamSession.php
│   ├── ExamSessionQuestion.php     # per-student assignment + order
│   ├── StudentAnswer.php
│   ├── StudentAnswerIncident.php   # lockdown-violation audit
│   ├── GradingTemplate.php
│   ├── GradeEntry.php              # per-student per-module manual grades
│   ├── Absence.php
│   └── InPlatformNotification.php
├── Models/Concerns/
│   └── BelongsToTeacher.php        # global scope trait
├── Enums/
│   ├── Level.php                   # L1..M2
│   ├── Difficulty.php              # easy/medium/hard
│   ├── ExamStatus.php              # draft/scheduled/active/ended
│   └── AnswerStatus.php            # draft/final
├── Livewire/
│   ├── Teacher/
│   │   ├── Dashboard.php
│   │   ├── Modules/Index.php
│   │   ├── Groups/Index.php
│   │   ├── Groups/Show.php
│   │   ├── Students/Show.php
│   │   ├── Questions/{Index,Create,Edit}.php
│   │   ├── Exams/{Index,Create,Show,Monitor,Results}.php
│   │   ├── Grades/Show.php         # grades/{group_id}
│   │   ├── Settings.php
│   │   └── Notifications/Index.php
│   └── Student/
│       ├── Dashboard.php
│       ├── Exams/Index.php
│       ├── Exams/Waiting.php
│       ├── Exams/Session.php       # THE locked exam page
│       ├── Exams/Results.php
│       ├── Grades/Index.php
│       └── Notifications/Index.php
├── Domain/Exam/
│   ├── Actions/
│   │   ├── CreateExamAction.php
│   │   ├── StartExamAction.php
│   │   ├── AssignQuestionsAction.php
│   │   ├── SaveDraftAnswerAction.php
│   │   ├── FinalizeSessionAction.php
│   │   ├── ExtendTimeAction.php
│   │   ├── EndExamAction.php
│   │   └── RecordLockdownIncidentAction.php
│   ├── Services/
│   │   ├── QuestionAssignmentService.php
│   │   ├── DeadlineCalculator.php
│   │   ├── GradingService.php
│   │   └── HeartbeatMonitor.php
│   └── Events/
│       ├── ExamStarted.php
│       ├── ExamEnded.php
│       └── SessionFinalized.php
├── Http/
│   ├── Middleware/
│   │   ├── EnsureAbsenceBelowThreshold.php
│   │   └── EnsureExamNotCompleted.php
│   └── Controllers/Auth/           # Breeze-scaffolded, per guard
├── Notifications/
│   ├── StudentAccountCreated.php
│   ├── ExamReminder.php
│   └── ResultsAvailable.php
├── Policies/
│   ├── ExamPolicy.php
│   ├── GroupPolicy.php
│   └── StudentPolicy.php
└── Providers/
    └── AuthServiceProvider.php     # registers two guards

config/
├── auth.php                        # guards: teacher, student
└── exam.php                        # HEARTBEAT_INTERVAL, HEARTBEAT_WINDOW, REMINDER_LEAD_MINUTES, ABSENCE_THRESHOLD defaults

database/
├── migrations/
│   └── (see data-model.md for full list)
├── seeders/
│   ├── DefaultGradingTemplateSeeder.php
│   ├── ModuleCatalogSeeder.php
│   └── DemoTeacherSeeder.php       # creates a sample teacher (admin out-of-scope)
└── factories/                      # one per model

resources/
├── views/
│   ├── layouts/{teacher,student,guest}.blade.php
│   └── livewire/                   # mirrors app/Livewire tree
├── js/
│   ├── app.js
│   └── alpine/
│       ├── examSession.js          # lockdown + heartbeat + offline buffer
│       └── monitor.js              # audio alert trigger
└── css/app.css                     # Tailwind v4

routes/
├── web.php                         # shared (login, forgot-password, reset-password)
├── teacher.php                     # guard: teacher
└── student.php                     # guard: student

tests/
├── Feature/
│   ├── Teacher/                    # one dir per feature area
│   ├── Student/
│   └── Scoping/                    # cross-teacher isolation tests (SC-008)
└── Unit/
    ├── Domain/                     # actions, services, calculators
    └── Models/
```

**Structure Decision**: Laravel monolith, single project. Domain logic is extracted
into `app/Domain/Exam/Actions` + `Services` (plain PHP classes) so it is unit-testable
without HTTP/Livewire overhead. Livewire components are thin orchestrators calling
actions. Two guards (`teacher`, `student`) each have a dedicated routes file mounted
under their own middleware group; they share no session namespace.

## Complexity Tracking

> Fill ONLY if Constitution Check has violations that must be justified.

*(none — all gates PASS)*
