# Route Contract

Three route files mounted separately. No REST API is exposed for MVP — all UX flows
through server-rendered Livewire components. Route names use dot-prefixed namespaces
(`teacher.*`, `student.*`).

---

## `routes/web.php` (shared, no guard)

| Method | URI | Name | Component / Handler |
|---|---|---|---|
| GET | `/` | `home` | redirect to `teacher.login` or `student.login` based on referer, else generic landing |
| GET | `/login` | (chooser page) | component: `Auth\RoleChooser` |

### Auth (scaffolded by Breeze, adapted per guard)

| Method | URI | Name | Guard |
|---|---|---|---|
| GET  | `/teacher/login` | `teacher.login` | — (guest only) |
| POST | `/teacher/login` | — | — |
| POST | `/teacher/logout` | `teacher.logout` | `teacher` |
| GET  | `/student/login` | `student.login` | — |
| POST | `/student/login` | — | — |
| POST | `/student/logout` | `student.logout` | `student` |
| GET  | `/forgot-password` | `password.request` | — |
| POST | `/forgot-password` | `password.email` | — |
| GET  | `/reset-password/{token}` | `password.reset` | — |
| POST | `/reset-password` | `password.update` | — |

---

## `routes/teacher.php` (middleware: `['web','auth:teacher']`)

| Method | URI | Name | Livewire component |
|---|---|---|---|
| GET | `/teacher/dashboard` | `teacher.dashboard` | `Teacher\Dashboard` |
| GET | `/teacher/profile` | `teacher.profile` | `Teacher\Profile` |
| GET | `/teacher/settings` | `teacher.settings` | `Teacher\Settings` |
| GET | `/teacher/notifications` | `teacher.notifications` | `Teacher\Notifications\Index` |
| GET | `/teacher/modules` | `teacher.modules` | `Teacher\Modules\Index` |
| GET | `/teacher/groups` | `teacher.groups.index` | `Teacher\Groups\Index` |
| GET | `/teacher/groups/{group}` | `teacher.groups.show` | `Teacher\Groups\Show` |
| GET | `/teacher/students/{student}` | `teacher.students.show` | `Teacher\Students\Show` |
| GET | `/teacher/questions` | `teacher.questions.index` | `Teacher\Questions\Index` |
| GET | `/teacher/questions/create` | `teacher.questions.create` | `Teacher\Questions\Create` |
| GET | `/teacher/questions/{question}/edit` | `teacher.questions.edit` | `Teacher\Questions\Edit` |
| GET | `/teacher/exams` | `teacher.exams.index` | `Teacher\Exams\Index` |
| GET | `/teacher/exams/create` | `teacher.exams.create` | `Teacher\Exams\Create` |
| GET | `/teacher/exams/{exam}` | `teacher.exams.show` | `Teacher\Exams\Show` |
| GET | `/teacher/exams/{exam}/monitor` | `teacher.exams.monitor` | `Teacher\Exams\Monitor` |
| GET | `/teacher/exams/{exam}/results` | `teacher.exams.results` | `Teacher\Exams\Results` |
| GET | `/teacher/grades/{group}` | `teacher.grades.show` | `Teacher\Grades\Show` |

### Route model binding

All `{exam}`, `{group}`, `{student}`, `{question}` bindings resolve through Eloquent
with the `BelongsToTeacher` global scope active — a teacher hitting another teacher's
URL deterministically receives a 404 (Principle I).

---

## `routes/student.php` (middleware: `['web','auth:student','absence.threshold']`)

| Method | URI | Name | Livewire component |
|---|---|---|---|
| GET | `/student/dashboard` | `student.dashboard` | `Student\Dashboard` |
| GET | `/student/profile` | `student.profile` | `Student\Profile` |
| GET | `/student/notifications` | `student.notifications` | `Student\Notifications\Index` |
| GET | `/student/exams` | `student.exams.index` | `Student\Exams\Index` |
| GET | `/student/exams/{exam}/waiting` | `student.exams.waiting` | `Student\Exams\Waiting` |
| GET | `/student/exams/{exam}/session` | `student.exams.session` | `Student\Exams\Session` (fully locked) |
| GET | `/student/exams/{exam}/results` | `student.exams.results` | `Student\Exams\Results` |
| GET | `/student/grades` | `student.grades` | `Student\Grades\Index` |

The `absence.threshold` middleware (alias for `EnsureAbsenceBelowThreshold`) short-
circuits the whole student guard if `absence_count >= threshold`.

The session route has an additional implicit guard: `EnsureExamNotCompleted` —
redirects to `student.exams.results` if the student's session has status `completed`
(blocks back-navigation, FR-023).
