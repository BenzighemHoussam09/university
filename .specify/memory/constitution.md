<!--
SYNC IMPACT REPORT
==================
Version change: [TEMPLATE] → 1.0.0
Modified principles: N/A (initial instantiation from template)
Added sections:
  - I. Teacher-Scoped Data Isolation
  - II. Server-Side Authority
  - III. Three-Level Exam Randomization
  - IV. Exam Session Integrity
  - V. Real-Time Observability
  - Technology Constraints
  - Development Workflow
  - Governance
Removed sections: None (template placeholders replaced)
Templates requiring updates:
  ✅ .specify/templates/plan-template.md — Constitution Check gate references these principles; no structural change needed
  ✅ .specify/templates/spec-template.md — No principle-driven mandatory sections added; no change needed
  ✅ .specify/templates/tasks-template.md — Task types (security, data-scoping, session-integrity) align with principles; no change needed
Deferred TODOs: None
-->

# University Exam Platform Constitution

## Core Principles

### I. Teacher-Scoped Data Isolation

Every model that belongs to a teacher MUST carry a `teacher_id` foreign key column.
The `BelongsToTeacher` global scope trait MUST be applied to all such models so that
every query is automatically filtered to the authenticated teacher's data.
Cross-teacher data access is strictly forbidden — no raw queries, joins, or eager-loads
may bypass this scope. Student records are always accessed through their owning teacher's
scope context.

**Rationale**: The platform is multi-teacher SaaS on a single database. Column-level
isolation via `teacher_id` is the sole tenancy boundary; a breach would expose one
teacher's questions, grades, and student PII to another.

### II. Server-Side Authority

The server MUST be the single source of truth for all exam state. This covers:
- `deadline` computation: `started_at + duration + global_extra + student_extra`
- Question assignment and ordering (drawn at session start, not at exam creation)
- Grade calculation: `final_grade = (exam_score × exam_weight) + (pw × pw_weight) + (att × att_weight) + (part × part_weight)`

Client-side code (Alpine.js timers, `localStorage`) MUST be treated as display buffers
only. `localStorage` MAY queue draft answer writes during disconnection but MUST sync
silently on reconnect; it MUST NOT be trusted as a canonical answer store.
No client-computed value may alter exam outcomes.

**Rationale**: Anti-cheat integrity. Any clock or answer manipulation must be impossible
without server collusion.

### III. Three-Level Exam Randomization

Question assignment MUST apply three independent randomization layers per student:

1. **Which questions** — drawn from the bank by difficulty tier; the bank is exhausted
   without repeats before cycling; on each new cycle the order is reshuffled.
2. **Question order** — the assigned questions are shuffled uniquely per student session.
3. **Choice order** — MCQ choices within every question MUST be shuffled at display time,
   every time, per student.

The algorithm MUST guarantee that when the bank has enough questions, no two students
receive the same question set. When the bank is insufficient, question reuse is permitted
but ordering MUST still differ.

**Rationale**: Maximum fairness and anti-cheating across all three axes simultaneously.

### IV. Exam Session Integrity

The student exam session page (`exams/{id}/session`) MUST enforce the following:

- Full page lockdown via Alpine.js `visibilitychange` and `blur` event listeners — tab
  switching and app switching MUST be detected and handled.
- All questions MUST be visible simultaneously (scroll-based, not paginated).
- Every answer selection MUST trigger an immediate DB write with status `draft`.
- Final submission (manual confirm button OR timer expiry) MUST atomically flip all
  `draft` answers to `final` and redirect to the results page.
- Back-navigation MUST be blocked after final submission.

**Rationale**: Prevents partial-answer loss, ensures auditability, and closes the window
for tab-switch cheating.

### V. Real-Time Observability

The teacher monitor page (`exams/{id}/monitor`) MUST use Livewire polling to maintain
a live view of every student's status, including: last answered question, remaining time,
and connection state.

Students MUST send a heartbeat every N seconds (via Livewire polling or Alpine fetch).
A missed heartbeat MUST surface as a disconnected indicator with an immediate visual
AND audio alert on the teacher's monitor page.

The teacher MUST be able to extend time globally (all students in the group) or
individually (single student). Both operations MUST update `ExamSession.global_extra` or
`student_extra` and recalculate `deadline` server-side in real time.

**Rationale**: The teacher is the real-time exam proctor; they need accurate, actionable
information to manage disconnections and special-case extensions fairly.

## Technology Constraints

- **Stack**: Laravel 12, Livewire 4, Alpine.js, Tailwind CSS v4, MySQL (single database)
- **Auth**: Laravel Breeze — email + password only; no OAuth or SSO in scope
- **Guards**: Two guards (`teacher`, `student`), each with its own middleware group,
  routes file, and dashboard; guards MUST NOT share session namespaces
- **Notifications**: Queue driver MUST be used for all email and in-platform notifications;
  synchronous notification dispatch is forbidden in web request cycles
- **Admin**: No admin UI in the initial build; admin creates teacher accounts directly
  in the database or via a seeder; admin management is explicitly out of scope
- **Levels**: Fixed enum — L1, L2, L3, M1, M2; adding new levels requires a migration
  and an enum update, not a free-text field
- **Questions**: MCQ only (4 choices, exactly 1 correct); other question types are out
  of scope for the initial build

## Development Workflow

- `composer run dev` — starts `php artisan serve`, `queue:listen`, `pail`, and `vite`
  concurrently; use this for all local development
- `composer run test` — clears config cache then runs PHPUnit; MUST pass before any PR merge
- `composer run setup` — first-time install (composer, `.env`, key, migrate, npm, build)
- `php artisan pint` — code style fixer; CI enforces this; MUST be clean before merge
- `php artisan test --filter=ClassName::methodName` — single test execution

Every new model scoped to a teacher MUST:
1. Add `teacher_id` column in its migration
2. Apply the `BelongsToTeacher` global scope trait
3. Include a factory that sets `teacher_id` for test isolation

Schema changes MUST be delivered as new migration files; existing migrations MUST NOT
be edited after they have been run in any environment.

## Governance

This constitution supersedes all other engineering practices for this project.
Amendments require a documented rationale, a version bump per the policy below, and
an update to the Sync Impact Report comment at the top of this file.

**Versioning policy**:
- MAJOR — backward-incompatible principle removal or redefinition (e.g., removing
  teacher-scoping requirement or changing the MCQ-only constraint)
- MINOR — new principle or new mandatory section added
- PATCH — clarifications, wording improvements, typo fixes, non-semantic refinements

All implementation plans and feature specs MUST reference the constitution version they
comply with. Compliance MUST be verified during PR review via the Constitution Check
gate in the plan template.

**Version**: 1.0.0 | **Ratified**: 2026-04-14 | **Last Amended**: 2026-04-14
