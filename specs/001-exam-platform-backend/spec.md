# Feature Specification: Electronic Exam Platform (MVP)

**Feature Branch**: `001-exam-platform-backend`
**Created**: 2026-04-14
**Status**: Draft
**Input**: User description: "implement the @docs/exam-platform-brainstorm.md, focused on Laravel + Livewire, no separate frontend"

## Clarifications

### Session 2026-04-14

- Q: How should the system respond when a student violates the exam-page lockdown (tab switch, window switch, navigate away)? → A: Log the incident in the session audit trail, show the student a visible warning with an incident counter, and surface a live flag on the teacher's monitor page (no auto-submit).
- Q: What is the cardinality between a student and the teacher's groups? → A: One student record per (teacher, student), assignable to many groups under that same teacher (many-to-many student↔group).
- Q: How is an exam started? → A: Each exam has a scheduled start datetime used for reminders and for opening the student waiting room, but only the teacher's manual "Start" action moves students from waiting to active.
- Q: What grade scale does the platform use? → A: Each grade component has its own configurable maximum (e.g., exam /10, personal work /4, etc.). The teacher configures each component's max in their settings. The sum of all component maxes MUST equal 20. The final grade is the direct sum of component values and is therefore always on a /20 scale.

## Terminology & Data Model

**Important**: The specification uses two distinct status enumerations:

- **Exam Status** (exam creation lifecycle): `draft` → `scheduled` → `active` → `ended`
  - `draft`: exam created but not yet scheduled
  - `scheduled`: exam has a scheduled start time in the future
  - `active`: teacher has clicked Start and questions are being delivered to students
  - `ended`: teacher manually ended the exam or all students have completed

- **Exam Session Status** (per-student lifecycle): `waiting` → `active` → `completed`
  - `waiting`: student waits in the waiting room before the teacher starts the exam
  - `active`: student's session is active and they are answering questions
  - `completed`: student's session is finalized (manual submit or deadline reached)

When this spec refers to "exam status," it means the Exam entity's state. When it refers to "session status," it means the ExamSession entity's state. These are independent; an Exam can be `active` while individual student ExamSessions transition from `waiting` → `active` → `completed`.

---

## User Scenarios & Testing *(mandatory)*

### User Story 1 — Teacher Workspace Setup (Priority: P1)

A teacher logs in to the platform, picks or adds the modules they teach, creates
groups under those modules (tied to an academic level such as L1…M2), and registers
students into each group. Each student receives credentials to access the platform.

**Why this priority**: Nothing else in the platform works without a teacher having
modules, groups, and students in place. This is the first slice of independently
demonstrable value — a teacher can fully organize their classes before any exam exists.

**Independent Test**: A teacher account can sign in, create one module, one group at
a chosen level, and add at least one student to that group. The student can then sign
in with the issued credentials and see an empty dashboard with no upcoming exams.

**Acceptance Scenarios**:

1. **Given** a teacher has logged in for the first time, **When** they add a module
   by picking it from the shared bank, **Then** that module appears only in their
   own modules list and not in any other teacher's view.
2. **Given** a teacher has at least one module, **When** they create a new group by
   naming it, selecting a level, and linking it to one of their modules, **Then** the
   group is saved and listed under their groups.
3. **Given** a teacher has a group, **When** they add a student with name and email,
   **Then** the student account is created, scoped to that teacher, and the student
   receives login credentials.
4. **Given** a student has received credentials, **When** they sign in, **Then** they
   land on their dashboard and see no exams, zero absences, and their profile details.

---

### User Story 2 — Question Bank Management (Priority: P1)

A teacher builds a personal bank of multiple-choice questions. Each question has four
choices, exactly one correct answer, a difficulty label (easy, medium, hard), and is
tagged with a module and an academic level. The teacher can filter, edit, and delete
questions they own.

**Why this priority**: The bank is a prerequisite for exam creation, and building it
is an ongoing activity independent of exam scheduling. A teacher can populate the bank
well before their first exam date.

**Independent Test**: A teacher can create a question with four choices and one correct
answer, save it, reopen it for edit, change the correct choice, and delete another
question. The list can be filtered by module, level, and difficulty.

**Acceptance Scenarios**:

1. **Given** a teacher has at least one module, **When** they add a new question with
   text, four choices, one correct choice marked, a difficulty, a module, and a level,
   **Then** the question is stored and appears in that teacher's question list.
2. **Given** a teacher is viewing their question list, **When** they filter by module,
   level, and difficulty, **Then** only matching questions are shown.
3. **Given** a question exists, **When** the teacher edits the question text or changes
   the correct choice, **Then** the update is saved and reflected in the list.
4. **Given** no other teacher has access, **When** Teacher B queries the bank, **Then**
   they do not see any of Teacher A's questions.

---

### User Story 3 — End-to-End Exam Session (Priority: P1) 🎯 MVP

A teacher creates an exam for one of their groups, specifying how many easy, medium,
and hard questions to draw and the total duration in minutes. At exam time the teacher
starts the session; each student is then shown a locked exam page with a server-driven
countdown. Every answer choice is saved immediately as a draft. When the student
confirms submission or the timer expires, all drafts become final, the exam is
auto-graded, and the student is redirected to their personal results page showing
each question, their answer, and the correct answer.

**Why this priority**: This is the core product. Without it the platform has no reason
to exist. It is also the most complex slice, but it can ship on its own without live
monitoring or manual grading components — it simply delivers an auto-graded exam.

**Independent Test**: A teacher creates a 10-question exam (for example 4 easy,
4 medium, 2 hard) for a group of three students and starts it. Each student enters a
locked exam page, answers questions, and submits. Each student sees their auto-computed
score and per-question feedback. The teacher sees the aggregate scores on the results
page.

**Acceptance Scenarios**:

1. **Given** the teacher's question bank holds enough questions per difficulty for the
   configured distribution, **When** the teacher starts the exam, **Then** each
   participating student receives a question set where (a) questions are drawn from the
   bank without repeats across students until the bank is exhausted, (b) the question
   order is unique per student, and (c) the choice order inside each question is unique
   per student.
2. **Given** the bank has fewer questions than students × distribution, **When** the
   teacher starts the exam, **Then** question reuse is permitted but question order
   and choice order still differ between students.
3. **Given** a student is in the exam page, **When** they click any choice, **Then**
   their selection is persisted as a draft answer before they move to the next question.
4. **Given** a student has the exam page open, **When** they attempt to switch tabs,
   switch windows, or leave the page, **Then** the attempt is recorded in the audit
   trail, the student sees a warning with an incident counter, and the incident is
   flagged live on the teacher's monitor — the exam is NOT auto-submitted.
5. **Given** a student's network connection drops, **When** they select answers while
   offline, **Then** those answers are buffered locally, the page remains locked, and
   on reconnection the buffered answers sync silently to the server without data loss.
6. **Given** the server-computed deadline is reached, **When** the timer expires,
   **Then** every draft answer is automatically finalized and the student is redirected
   to their results page — even if they had not clicked "submit".
7. **Given** a student confirms final submission manually, **When** the confirmation is
   processed, **Then** all drafts are finalized, results are computed, the student is
   redirected to the results page, and back-navigation to the exam is blocked.
8. **Given** a student is on their results page, **When** they view it, **Then** they
   see their total score, each question they received with their selected answer, the
   correct answer, and a clear indicator of whether each answer was right or wrong.

---

### User Story 4 — Live Exam Monitoring & Time Extension (Priority: P2)

While an exam is running, the teacher opens a monitor page that shows every student
in the group with their current status: last answered question, remaining time, and
connection state (connected or disconnected). If a student's connection drops, the
teacher is alerted visually and audibly. The teacher can extend the exam duration
either globally (for all students in the group) or individually (for a single student).

**Why this priority**: Monitoring makes the platform usable under real classroom
conditions (unreliable networks, special-case students). It is not required for the
exam itself to function correctly — an exam can run without the teacher watching — so
it ships as a second increment on top of the MVP.

**Independent Test**: While students from User Story 3 are taking their exam, the
teacher opens the monitor page and sees each student's live progress. One student
disconnects from the network; within the configured heartbeat window the teacher sees
that student turn "disconnected" with a visible and audible alert. The teacher adds
2 minutes globally; every student's remaining time increases by 2 minutes. The teacher
then adds 1 extra minute to a single late-arriving student; only that student's
deadline shifts.

**Acceptance Scenarios**:

1. **Given** an exam is in progress, **When** the teacher opens the monitor page,
   **Then** each participating student is listed with name, last answered question,
   remaining time, and connection status.
2. **Given** a student stops sending heartbeats for longer than the configured window,
   **When** the teacher's monitor page next refreshes, **Then** that student is shown
   as disconnected and a visible + audible alert is triggered.
3. **Given** an exam is in progress, **When** the teacher adds N minutes globally to
   the group, **Then** every student's deadline is extended by exactly N minutes
   server-side and their timers reflect the new deadline on next refresh.
4. **Given** an exam is in progress, **When** the teacher adds N extra minutes to a
   single student, **Then** only that student's deadline shifts by N minutes and all
   other students are unaffected.
5. **Given** the teacher presses "end exam" on the monitor page, **When** the action
   is confirmed, **Then** every still-active student is immediately moved to final
   submission with their current drafts as final answers.

---

### User Story 5 — Grading Components & Final Grade (Priority: P2)

Each teacher starts with a copy of a system-wide grading template that assigns a
maximum value to each of four components: exam score, personal work,
attendance/behaviour, and participation. The four maxes always sum to 20, and the
teacher can redistribute them in their own settings (e.g., exam /10, personal work /4,
attendance /3, participation /3) as long as the total stays at 20. For every student
in a group, the teacher enters the three manual components (each capped at its
configured max); the final grade is the direct sum of the four components and is
therefore always on a /20 scale.

**Why this priority**: The platform's deliverable to the university is not just an
exam score but an administrative grade sheet that combines auto-graded and manually
assessed components. It is not required for the exam itself but is required before
the platform can replace paper grade sheets.

**Independent Test**: A teacher opens the grades page for one group, enters personal
work, attendance, and participation values for each student, and sees the final grade
computed live using the weights defined in settings. The student sees the same final
grade on their grades page broken down by component.

**Acceptance Scenarios**:

1. **Given** a new teacher account, **When** the account is created, **Then** the
   teacher automatically receives a personal editable copy of the system grading
   template with default weights.
2. **Given** a teacher opens their settings, **When** they change the maximum of any
   component such that the four maxes still sum to 20, **Then** the change is saved
   and applied to all future grade calculations for that teacher only; if the four
   maxes no longer sum to 20, the save is rejected with a clear error.
3. **Given** a group has graded exams, **When** the teacher opens the group's grade
   sheet and enters manual values for a student, **Then** each value is validated
   against its component's configured maximum and the final grade is computed as the
   direct sum of exam + personal work + attendance + participation on a /20 scale.
4. **Given** a final grade exists for a student, **When** that student opens their own
   grades page, **Then** they see the per-module final grade with the per-component
   breakdown.

---

### User Story 6 — Notifications (Priority: P3)

The platform sends notifications through two channels (email and in-platform) for
key events: a student account is created (with login credentials), an upcoming exam
is scheduled, and exam results have been finalized.

**Why this priority**: Notifications improve usability and reduce the teacher's manual
communication load, but the platform functions end-to-end without them. They ship
after the core exam and grading flows.

**Independent Test**: Creating a new student triggers an email containing login
credentials and an in-platform notification visible on the student's notifications
page after first login. Starting a scheduled exam triggers a reminder. Finalizing a
result triggers a results-available notification.

**Acceptance Scenarios**:

1. **Given** a teacher adds a new student, **When** the creation is saved, **Then**
   the student receives an email with login details and an in-platform notification is
   queued for their first login.
2. **Given** an exam has a scheduled start time, **When** the start time approaches
   per the configured reminder window, **Then** each participating student receives a
   reminder through both channels.
3. **Given** a student has finalized an exam, **When** the results are computed,
   **Then** the student receives a "results available" notification through both
   channels.
4. **Given** any of the above events occur, **When** the user opens their notifications
   page, **Then** they can see all past notifications ordered newest-first with an
   unread indicator.

---

### User Story 7 — Absence Tracking & Threshold Block (Priority: P3)

The platform tracks per-student absences. A system-wide threshold (default 5) is
configured administratively. When a student's absence count reaches the threshold,
they are blocked from signing in until the block is lifted administratively.

**Why this priority**: This is a compliance requirement imposed by the university, not
a teacher decision, and it does not affect the correctness of exams or grades already
taken. It ships after the core flows.

**Independent Test**: A teacher records absences for a student, one at a time, on the
student's profile page. The student's dashboard shows the remaining absences until the
block. When the count reaches the threshold, the student can no longer sign in and
sees a block message instead; their profile still shows the absence history.

**Acceptance Scenarios**:

1. **Given** a student profile is open, **When** the teacher records an absence,
   **Then** the absence is added to the student's record with a date and is visible on
   the student's own profile.
2. **Given** a student's absence count is below the threshold, **When** they sign in,
   **Then** their dashboard shows the number of absences remaining before a block
   would occur.
3. **Given** a student's absence count equals or exceeds the threshold, **When** they
   attempt to sign in, **Then** authentication is refused with a clear block message
   and no dashboard is shown.
4. **Given** the threshold is changed in system settings, **When** the change is saved,
   **Then** the new threshold applies immediately to all future sign-ins.

---

### Edge Cases

- **Bank too small for distribution**: If the bank has fewer easy/medium/hard questions
  than the exam requires, question reuse across students is permitted but ordering is
  still randomized per student.
- **Student joins after exam started**: A student who opens the exam page after the
  teacher has started the session enters with a shorter effective duration (deadline is
  absolute, not per-student start time) unless the teacher grants them an individual
  extension.
- **Student loses connection and never returns before deadline**: Their drafts as of
  the last successful sync are finalized at the server-computed deadline; the results
  page becomes available to them on reconnection.
- **Teacher ends exam manually before deadline**: All active students are moved to
  final submission immediately with their current drafts as final answers.
- **Two teachers share a module**: Each teacher's groups, questions, exams, and
  grades are isolated; picking the same shared module does not expose any data across
  teachers.
- **Student attempts to navigate back to a finalized exam**: Navigation is blocked;
  they are redirected to their results page.
- **Teacher changes component maxes mid-semester**: Provided the new maxes still sum
  to 20, the change applies to grade calculations going forward for that teacher only;
  prior recorded final grades are recalculated on next view using the new maxes (no
  historical freeze for MVP). Existing manual grade entries (personal work, attendance,
  participation) that now exceed a lowered max MUST be flagged in the UI for the teacher
  to review and revise; the system MUST NOT silently clamp values or allow invalid states
  (per FR-030).
- **Blocked student tries to open the platform**: Even deep links to exam pages are
  refused; the block message is the only response.

## Requirements *(mandatory)*

### Functional Requirements

**Accounts & Scoping**

- **FR-001**: System MUST support two roles with separate authentication contexts:
  Teacher and Student. Teachers are provisioned out-of-band by an administrator;
  students are provisioned by the teacher who owns them.
- **FR-002**: System MUST scope every teacher-owned record (modules, groups, students,
  questions, exams, exam sessions, grades, absences) by teacher identity so that no
  teacher can read or modify another teacher's data under any circumstance.
- **FR-003**: System MUST authenticate users by email and password and MUST support
  "forgot password" and "reset password" flows via email link.

**Academic Structure**

- **FR-004**: System MUST offer a fixed set of academic levels (L1, L2, L3, M1, M2)
  selectable when creating a group.
- **FR-005**: System MUST maintain a shared module catalog. Teachers MUST be able to
  pick existing modules or add new ones when the needed module is not yet in the
  catalog.
- **FR-006**: Teachers MUST be able to create groups identified by name, linked to
  exactly one module and one level.
- **FR-007**: Teachers MUST be able to create student accounts once (scoped to
  themselves) and assign each student to one or more of their groups. A single student
  account under a given teacher MAY belong to multiple groups simultaneously (for
  example across different modules taught by that teacher). Teachers MUST be able to
  edit a student's basic profile and manage their group memberships independently.

**Question Bank**

- **FR-008**: Questions MUST be multiple-choice only with exactly four choices, exactly
  one of which is marked correct.
- **FR-009**: Every question MUST carry a difficulty label (easy, medium, or hard), a
  module tag, and a level tag.
- **FR-010**: Teachers MUST be able to create, read, update, and delete their own
  questions, filtered by module, level, and difficulty.
- **FR-011**: Choice order inside a question MUST be randomized per (student, exam
  session) and MUST remain stable across all renders within that same session so a
  student never sees choices reshuffle mid-exam. A different student in the same
  exam, and the same student in a later exam, MUST see an independently shuffled
  order.

**Exam Configuration**

- **FR-012**: Teachers MUST be able to create an exam tied to one of their groups
  specifying: the number of easy, medium, and hard questions to draw, the total duration
  in minutes, and a scheduled start datetime.
- **FR-012a**: The scheduled start datetime MUST drive: (a) the timing of upcoming-exam
  reminder notifications (sent at a system-wide configurable lead time, default 30
  minutes before scheduled start; teachers cannot override per-exam), and (b) the opening
  of the student waiting room (`exams/{id}/waiting`). Before the scheduled time, students
  MUST NOT be able to enter the waiting room.
- **FR-012b**: Moving students from the waiting room into an active session MUST require
  an explicit manual "Start" action from the owning teacher. The exam MUST NOT
  auto-start at the scheduled time. The server-computed `started_at` is the moment of
  the teacher's Start action, not the scheduled datetime.
- **FR-013**: System MUST NOT assign questions to students at exam creation time. The
  assignment MUST happen when the teacher starts the exam session.

**Exam Session — Question Assignment**

- **FR-014**: When the exam session starts, System MUST assign each student a question
  set that applies three independent randomization layers per student: (a) which
  questions are drawn, (b) question order, (c) choice order.
- **FR-015**: System MUST exhaust the question bank without repeats across students
  before permitting reuse. When reuse is forced by bank size, order MUST still differ
  per student.

**Exam Session — Timing**

- **FR-016**: System MUST compute every student's deadline server-side as
  `started_at + duration + global_extra + student_extra`. The client timer is a display
  reflection only and MUST NOT be trusted.
- **FR-017**: Teachers MUST be able to extend the exam globally (adds minutes for every
  student in the group) or individually (adds minutes for one student), and the change
  MUST update the server-computed deadline in real time.

**Exam Session — Answering & Persistence**

- **FR-018**: Every answer selection MUST be persisted immediately server-side as a
  draft answer, including overwrites when a student changes their selection.
- **FR-019**: System MUST provide a client-side buffer for answers made while the
  network is unavailable and MUST silently sync buffered answers to the server on
  reconnection without requiring any user action.
- **FR-020**: The student exam page MUST remain locked during the session. Tab
  switching, window switching, or navigating away MUST be detected and MUST trigger
  three responses together: (a) the incident is recorded in the session audit trail
  with a timestamp, (b) the student is shown a visible warning with a running incident
  counter, (c) the incident is surfaced live on the teacher's monitor page. The exam
  is NOT auto-submitted on violation.
- **FR-021**: All assigned questions MUST be visible at once on the exam page
  (single-page scroll), not paginated.

**Exam Session — Submission & Grading**

- **FR-022**: When the deadline is reached, System MUST automatically finalize every
  draft answer and redirect the student to the results page, even with no user action.
- **FR-023**: When the student confirms final submission, System MUST atomically flip
  all drafts to final, compute the exam score, redirect to the results page, and block
  back-navigation to the exam.
- **FR-024**: System MUST auto-compute the exam score as the number of correct
  finalized answers out of the total assigned questions for that student.

**Live Monitoring**

- **FR-025**: Each student exam page MUST emit a heartbeat signal at a configured
  interval while the exam is active.
- **FR-026**: The monitor page MUST refresh automatically and display each student's
  last answered question, remaining time, connection status, and a lockdown-violation
  incident count (per FR-020).
- **FR-027**: When a student's heartbeat is absent beyond the configured window, the
  monitor page MUST surface a visual AND audible alert.

**Exam Lifecycle Management**

- **FR-041**: Teachers MUST be able to manually end an exam session from the monitor
  page. When "end exam" is confirmed, all active student sessions MUST be immediately
  finalized with their current draft answers as final answers; the Exam status MUST
  transition to `ended`; and all students MUST be redirected to their results pages.

**Grading Template & Final Grade**

- **FR-028**: System MUST maintain one administratively defined system-wide grading
  template that assigns a **maximum value** to each of the four components: exam score,
  personal work, attendance, and participation. The sum of the four component maxes
  MUST equal exactly 20.
- **FR-029**: On teacher account creation, System MUST provision a private editable
  copy of the grading template for that teacher. Teachers MUST be able to change any
  component's maximum in their own settings, subject to the rule that the four maxes
  still sum to 20; System MUST reject any save that violates this invariant.
- **FR-030**: Teachers MUST be able to input personal work, attendance, and
  participation values per student per module. Each entered value MUST be validated
  against that component's configured maximum in the teacher's template and rejected
  if it exceeds that maximum or is negative. When a teacher lowers a component's
  maximum in settings, any existing manual grade entries for that component that now
  exceed the new maximum MUST be flagged in the UI for the teacher to review and
  revise; the system MUST NOT silently clamp or allow invalid states.
- **FR-031**: The exam score component for a student MUST be derived from their
  auto-graded raw correct-count by normalizing to the teacher's configured exam max
  (raw_correct / total_assigned × exam_max). The final grade MUST be computed as the
  direct sum of the four component values (exam + personal work + attendance +
  participation) and therefore MUST always lie on a /20 scale.

**Results**

- **FR-032**: Each student MUST be able to view, after final submission, their
  per-question feedback showing their answer, the correct answer, and a correctness
  indicator, plus their total exam score.
- **FR-033**: Teachers MUST be able to view aggregate exam results for a group
  including per-student score, group average, and the questions most frequently
  answered incorrectly.
- **FR-034**: Students MUST be able to view their consolidated grade report across all
  modules with the per-component breakdown.

**Absence Tracking**

- **FR-035**: System MUST maintain an administratively configured absence threshold
  (default 5).
- **FR-036**: Teachers MUST be able to record per-student absences with a date.
- **FR-037**: System MUST refuse authentication for any student whose absence count
  meets or exceeds the threshold and MUST display a clear block message.

**Notifications**

- **FR-038**: System MUST send notifications through email and an in-platform inbox
  for these events: student account creation (with credentials), upcoming exam
  reminder, exam results finalized.
- **FR-039**: All notifications MUST be dispatched asynchronously so they never block
  the web request that triggered them.

### Key Entities

- **Teacher**: A user who owns a scoped workspace (modules, groups, questions, exams,
  grades) and proctors exams.
- **Student**: A user who belongs to exactly one teacher and is assigned to one or
  more of that teacher's groups (many-to-many with Group). Takes exams and views their
  own grades.
- **Module**: A subject taught at the university; items in a shared catalog that
  teachers pick from or extend.
- **Level**: Fixed academic level — L1, L2, L3, M1, M2.
- **Group**: A set of students taught by one teacher in one module at one level.
- **Question**: An MCQ belonging to a teacher, tagged with module, level, and
  difficulty. Four choices, exactly one correct.
- **Exam**: A scheduled assessment for one group with a difficulty distribution, a
  duration in minutes, and a scheduled start datetime. Becomes active only when the
  teacher manually starts it.
- **Exam Session**: One per student per exam, carrying the student's assigned question
  set, start time, deadline, and status (waiting, active, completed).
- **Student Answer**: One per assigned question per session, transitioning from draft
  to final at submission or timer expiry.
- **Grading Template**: The per-component maxima used to compute the final grade — one
  max each for exam, personal work, attendance, and participation, with the invariant
  that the four maxes sum to exactly 20. A system-wide default exists; each teacher has
  a private editable copy.
- **Grade Entry**: One record per student per module, storing the manually entered
  values for personal work, attendance, and participation, plus the auto-computed exam
  component value. The final grade for that student-module combination is the direct
  sum of these four values.
- **Absence**: A dated record that a specific student missed class; counted against
  the system threshold.
- **Notification**: An asynchronous message delivered via email and in-platform inbox
  for a configured event.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: A teacher can bring a fresh account to a fully prepared exam (modules,
  groups, students, question bank, exam) in under 30 minutes without assistance.
- **SC-002**: In an exam with sufficient bank size (i.e., total available questions per
  difficulty tier ≥ total students × questions drawn from that tier), no two students
  in the same session ever receive the same question order, AND 100% of student sessions
  receive fully non-overlapping question sets (no question repeats across students until
  the bank is exhausted and must cycle). When the bank is insufficient, reuse is
  permitted but question order and choice order still differ per student, and >99% of
  students receive unique question orderings despite repeats.
- **SC-003**: After a student selects any answer, the choice is persisted and recoverable
  after a forced reload within 2 seconds of selection under normal network conditions.
- **SC-004**: A student who loses network connectivity for up to 5 minutes during an
  exam loses zero previously selected answers on reconnection. For disconnections
  exceeding 5 minutes, the client-side `localStorage` buffer persists for up to 24
  hours; answers sync on reconnection unless the exam deadline has already passed
  server-side.
- **SC-005**: When a student's heartbeat stops, the teacher's monitor page shows the
  disconnected state within one heartbeat interval (target: ≤ 15 seconds end-to-end).
- **SC-006**: Timer-expiry finalization completes for 100% of still-active students
  within 30 seconds of the deadline, and no student retains the ability to answer after
  their deadline.
- **SC-007**: Computed exam scores match a hand-verified answer key for 100% of
  auto-graded exams.
- **SC-008**: Cross-teacher data leakage is zero — no endpoint, filter, or query
  returns any record owned by another teacher under any input.
- **SC-009**: Blocked students (absences ≥ threshold) are refused sign-in 100% of the
  time, including via direct links to exam pages.
- **SC-010**: 95% of sent notifications are delivered to email within 2 minutes of the
  triggering event under normal queue conditions.

## Assumptions

- Grade entries are scoped per student per module: a student enrolled in multiple
  modules taught by the same teacher has independent manual grades (personal work,
  attendance, participation) recorded for each module. The exam component is auto-
  computed per exam, and the final grade is the sum of all four components for that
  specific student-module pair.
- Administrators provision teacher accounts directly (via database seeding or a script)
  for the initial build; there is no self-service teacher signup and no admin UI in
  scope.
- All users interact with the platform through a web browser on a device with a modern
  browser; native mobile apps are out of scope.
- The application runs on a single database; column-level scoping by teacher identity
  is the only tenancy isolation in use.
- Email delivery infrastructure (SMTP or transactional provider) is available and
  configured; the platform dispatches via a queue but does not itself run a mail
  server.
- Students are expected to sit exams on devices with internet access at least at the
  start and end of the session; transient network loss during the session is supported,
  permanent offline exams are not.
- Changes to grading component maxes apply going forward; the MVP does not freeze
  historical final grades at the maxes in effect when they were computed.
- The "shared module catalog" is seeded initially by teachers freely adding modules.
  Administrative curation of the catalog is deferred beyond the MVP.
- Statistics shown to teachers are limited to group average, pass/fail counts, and the
  most-failed questions. Deeper analytics are out of scope.
- Printable administrative grade sheets are mentioned in the brainstorm but deferred
  beyond the MVP.
