# Phase 1 Data Model — Electronic Exam Platform (MVP)

All teacher-owned tables carry `teacher_id` (nullable FALSE, FK to `teachers.id`,
`ON DELETE CASCADE`). The `BelongsToTeacher` trait applies `TeacherScope` at the
Eloquent layer.

---

## Table inventory

| # | Table | Purpose | Teacher-scoped |
|---|---|---|---|
| 1 | `teachers` | Teacher accounts (guard: `teacher`) | — |
| 2 | `students` | Student accounts (guard: `student`) | YES (`teacher_id`) |
| 3 | `modules` | Shared module catalog + teacher-owned rows | YES |
| 4 | `groups` | Teacher's classes (module + level) | YES |
| 5 | `group_student` | Many-to-many student ↔ group pivot | YES (derived via group) |
| 6 | `questions` | MCQ bank | YES |
| 7 | `question_choices` | Four choices per question | YES (via question) |
| 8 | `exams` | Scheduled assessments | YES |
| 9 | `exam_sessions` | Per-student-per-exam runtime state | YES (via exam) |
| 10 | `exam_session_questions` | Per-session assigned question set + order | YES (via session) |
| 11 | `student_answers` | Per-question draft/final answer | YES (via session) |
| 12 | `student_answer_incidents` | Lockdown-violation audit log | YES (via session) |
| 13 | `grading_templates` | Per-teacher component maxes (system default id=1) | YES (nullable for system default) |
| 14 | `grade_entries` | Per-student-per-module manual + computed grades | YES |
| 15 | `absences` | Per-student dated absence records | YES |
| 16 | `in_platform_notifications` | In-app notification inbox | YES (recipient-scoped) |
| 17 | `password_reset_tokens` | Laravel default | — |
| 18 | `sessions` | Laravel default (if session driver = database) | — |
| 19 | `jobs`, `failed_jobs` | Laravel default queue | — |

---

## Entity details

### `teachers`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| name | string(120) | |
| email | string(190) | unique |
| password | string(255) | bcrypt |
| grading_template_id | bigint fk grading_templates.id | set on account creation |
| email_verified_at, remember_token, timestamps | | |

### `students`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| teacher_id | bigint fk teachers.id | NOT NULL, cascade |
| name | string(120) | |
| email | string(190) | unique **per teacher** (composite unique on `teacher_id,email`) |
| password | string(255) | bcrypt; initial password auto-generated + mailed |
| absence_count | unsigned smallint | cached counter; default 0 |
| blocked_at | datetime null | set when absence_count reaches threshold |
| email_verified_at, remember_token, timestamps | | |

### `modules`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| teacher_id | bigint fk teachers.id | NOT NULL (each teacher owns their module row — the "shared catalog" is materialized as a list of canonical names teachers can pick from at creation; no cross-teacher row sharing to preserve scoping) |
| name | string(160) | |
| created_from_catalog_id | bigint null | FK to `module_catalog.id` (optional seed reference) |
| timestamps | | |

> A companion seed-only table `module_catalog` (NOT teacher-scoped) holds canonical
> names. Teachers add modules by picking a catalog name OR entering a free-text name;
> either way a `modules` row is created under their `teacher_id`. This preserves
> Principle I.

### `groups`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| teacher_id | bigint fk | |
| module_id | bigint fk modules.id | same teacher_id |
| level | enum('L1','L2','L3','M1','M2') | |
| name | string(80) | e.g., "D4" |
| timestamps | | |

### `group_student` (pivot)

| Column | Type | Notes |
|---|---|---|
| group_id | bigint fk | |
| student_id | bigint fk | |
| timestamps | | |
| UNIQUE(group_id, student_id) | | |

Relationship: `Student::belongsToMany(Group::class)`; `Group::belongsToMany(Student::class)`.

### `questions`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| teacher_id | bigint fk | |
| module_id | bigint fk | |
| level | enum L1..M2 | |
| difficulty | enum('easy','medium','hard') | |
| text | text | |
| timestamps | | |

### `question_choices`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| question_id | bigint fk questions.id | cascade |
| text | string(500) | |
| is_correct | boolean | exactly one TRUE per question (enforced in model save) |
| position | unsigned tinyint | original position 1..4 (used only for authoring) |

### `exams`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| teacher_id | bigint fk | |
| group_id | bigint fk | |
| title | string(160) | |
| easy_count, medium_count, hard_count | unsigned smallint | distribution |
| duration_minutes | unsigned smallint | |
| scheduled_at | datetime | reminders + waiting-room open gate |
| status | enum('draft','scheduled','active','ended') | default 'scheduled' |
| started_at | datetime null | set when teacher clicks Start |
| ended_at | datetime null | set on manual end OR when all sessions completed |
| global_extra_minutes | integer | default 0 |
| reminders_sent_at | datetime null | idempotency guard |
| timestamps | | |

State transitions:

```text
draft → scheduled (on save with scheduled_at)
scheduled → active (StartExamAction: sets started_at, creates exam_sessions, assigns questions)
active → ended (EndExamAction OR all sessions reached 'completed')
```

### `exam_sessions`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| exam_id | bigint fk | |
| student_id | bigint fk | |
| status | enum('waiting','active','completed') | |
| started_at | datetime null | inherits exam.started_at on assignment |
| deadline | datetime null | server-computed; recomputed on every extension |
| student_extra_minutes | integer | default 0 |
| last_heartbeat_at | datetime null | |
| completed_at | datetime null | |
| exam_score_raw | unsigned smallint null | correct count |
| exam_score_component | decimal(4,2) null | normalized to template exam_max |
| UNIQUE(exam_id, student_id) | | |

State transitions: `waiting → active (on Start) → completed (on FinalizeSessionAction)`.

### `exam_session_questions`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| exam_session_id | bigint fk | cascade |
| question_id | bigint fk | |
| display_order | unsigned smallint | unique within session |
| UNIQUE(exam_session_id, display_order) | | |

### `student_answers`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| exam_session_id | bigint fk | |
| question_id | bigint fk | |
| selected_choice_id | bigint fk question_choices.id null | null = unanswered |
| status | enum('draft','final') | default 'draft' |
| updated_at, created_at | | updated_at reflects last change |
| UNIQUE(exam_session_id, question_id) | | upsert target |

### `student_answer_incidents`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| exam_session_id | bigint fk | |
| kind | enum('visibility_hidden','window_blur','navigation_attempt') | |
| occurred_at | datetime | |

Used by Monitor page to show cumulative count per student.

### `grading_templates`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| teacher_id | bigint fk null | null = system default (seed id=1) |
| exam_max, personal_work_max, attendance_max, participation_max | unsigned smallint | invariant: sum = 20 |
| timestamps | | |

### `grade_entries`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| teacher_id | bigint fk | |
| student_id | bigint fk | |
| module_id | bigint fk | |
| exam_component | decimal(4,2) | normalized from latest exam's exam_score_component |
| personal_work | decimal(4,2) | |
| attendance | decimal(4,2) | |
| participation | decimal(4,2) | |
| final_grade | decimal(4,2) | computed column (MySQL) or app-computed (sum of four) |
| UNIQUE(student_id, module_id) | | |

### `absences`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| teacher_id | bigint fk | |
| student_id | bigint fk | |
| occurred_on | date | |
| created_at | datetime | |

Counter `students.absence_count` is updated by a model observer on `Absence` create/delete.

### `in_platform_notifications`

| Column | Type | Notes |
|---|---|---|
| id | bigint pk | |
| teacher_id | bigint fk null | recipient's scope |
| recipient_type | enum('teacher','student') | |
| recipient_id | bigint | polymorphic-by-type |
| kind | enum('student_account_created','exam_reminder','results_available') | |
| payload | json | e.g., exam_id, results URL |
| read_at | datetime null | |
| created_at | datetime | |

---

## Relationships (Eloquent)

```text
Teacher ─┬──┬─< Student ─< GroupStudent >── Group
         │  │                                │
         │  ├─< Module ────────────────────── │
         │  ├─< Group ─────────────────────── │
         │  ├─< Question ─< QuestionChoice
         │  ├─< Exam ─< ExamSession ─< ExamSessionQuestion
         │  │                         ─< StudentAnswer ── QuestionChoice
         │  │                         ─< StudentAnswerIncident
         │  ├─< GradingTemplate (1:1 via Teacher.grading_template_id)
         │  ├─< GradeEntry
         │  └─< Absence
         └── InPlatformNotification (recipient_type+recipient_id)
```

---

## Validation rules (enforced in form requests + model events)

- `questions.choices`: exactly 4 rows, exactly 1 with `is_correct=true`.
- `exams.{easy,medium,hard}_count`: non-negative; at least one > 0; duration_minutes
  between 1 and 600; `scheduled_at` must be in the future on create.
- `grading_templates`: `exam_max + personal_work_max + attendance_max +
  participation_max === 20`.
- `grade_entries.{personal_work,attendance,participation}`: must be in
  `[0, template.<component>_max]`.
- `student_answers.selected_choice_id`: must belong to `question_id` AND
  `question_choices.question_id` must match an `exam_session_questions` row for the
  same session.
- `exam_session_questions.display_order`: unique within session.

---

## Indexes (beyond FKs and UNIQUEs above)

- `students (teacher_id, email)` UNIQUE
- `questions (teacher_id, module_id, level, difficulty)` — filter on bank page
- `exams (teacher_id, scheduled_at)` — dashboard upcoming list + reminder query
- `exam_sessions (exam_id, status)` — monitor page load
- `exam_sessions (deadline, status)` — overdue-finalizer query
- `student_answers (exam_session_id, status)` — finalize transaction
- `in_platform_notifications (recipient_type, recipient_id, read_at)` — inbox query
