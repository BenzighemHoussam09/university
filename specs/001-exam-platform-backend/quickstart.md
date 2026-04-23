# Quickstart — Electronic Exam Platform (MVP)

Smoke-test path that exercises the MVP user stories (US1, US2, US3) end-to-end
locally. Time-to-first-exam ≈ 10 minutes.

## Prerequisites

- Laragon running on Windows with PHP 8.3, MySQL 8
- Node 20+
- Repo cloned at `D:/laragon/www/university`

## First-time setup

```bash
composer run setup
```

Equivalent manual steps if `setup` fails:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed      # seeds: DefaultGradingTemplate + ModuleCatalog + DemoTeacher
npm install && npm run build
```

Seeders create:
- `grading_templates` id=1 (system default: exam=12, personal_work=4, attendance=2, participation=2, sum=20)
- `module_catalog` with ~30 common Algerian university modules
- One demo teacher: `demo.teacher@univ.dz` / `password`

## Run the app

```bash
composer run dev
```

This starts `php artisan serve` + `queue:listen` + `pail` + `vite` concurrently.
Open http://127.0.0.1:8000.

---

## Smoke test — full teacher → student loop

### 1. Teacher workspace setup (US1)
1. Open `/teacher/login`; log in as `demo.teacher@univ.dz` / `password`.
2. Go to `/teacher/modules`; pick "History" from the catalog. It appears in your
   modules list.
3. Go to `/teacher/groups`; create group `D4`, level `M2`, module `History`.
4. Open the group; click "Add student"; enter name + email. A password is
   auto-generated and emailed via the queue (check `storage/logs/laravel.log` if
   mail driver is `log`). Note the credentials printed in dev.

### 2. Question bank (US2)
5. Go to `/teacher/questions/create`. Add at least 10 questions for
   History / M2 across difficulties (say 5 easy, 3 medium, 2 hard). Each question:
   4 choices, exactly one correct.

### 3. Create and start an exam (US3)
6. Go to `/teacher/exams/create`. Group = D4, easy=3, medium=2, hard=1,
   duration=15 min, scheduled_at = 2 minutes from now. Save.
7. Open the exam detail page; wait until the scheduled time passes; click "Start".
   This triggers `StartExamAction`; question assignment runs; students are
   redirected from waiting to session on their next poll.

### 4. Student takes exam
8. In a separate browser profile, log in as the student you created.
9. Dashboard shows the exam. Open it; you land on `waiting` then auto-redirect
   to `session` after the teacher starts it.
10. Answer questions. Verify:
    - Each click persists immediately (check `student_answers` table — new
      row with `status='draft'`).
    - Switching tabs shows a warning + counter.
    - The teacher's `/teacher/exams/{id}/monitor` shows your progress, remaining
      time, and incident count within 5 seconds.
11. Click "Submit final". Confirm. You land on the results page; back navigation
    is blocked.

### 5. Verify monitoring and time extension (US4 smoke)
12. Start a second exam with another student. Use the monitor's "Extend +1 min"
    buttons; verify `deadline` in `exam_sessions` increases by 60s.
13. Force-disconnect the student (kill network). Within 25 seconds the monitor
    shows "disconnected" and plays the alert sound.

### 6. Grading (US5 smoke)
14. Go to `/teacher/settings`. Change component maxes to exam=10, personal_work=4,
    attendance=3, participation=3 (sum=20). Save.
15. Go to `/teacher/grades/{group_id}`. Enter personal_work=4, attendance=2,
    participation=3 for a student. Verify final_grade = exam_component + 4 + 2 + 3.
16. Log in as that student; go to `/student/grades`; verify the same breakdown.

---

## Running tests

```bash
composer run test                               # full suite
composer run ci                                 # linting + tests (CI pipeline)
php artisan test --filter=QuestionAssignment   # targeted
php artisan test --filter=SlaTest              # SLA performance validation
./vendor/bin/pint                               # style fix (with --test to verify)
```

Key test suites:
- `tests/Feature/Scoping/CrossTeacherIsolationTest.php` — Principle I / SC-008
- `tests/Feature/Exam/FullExamFlowTest.php` — end-to-end US3
- `tests/Unit/Domain/QuestionAssignmentServiceTest.php` — three-layer randomization
- `tests/Unit/Domain/GradingServiceTest.php` — component max + final-grade math
- `tests/Feature/Monitor/HeartbeatDetectionTest.php` — SC-005 (≤15s disconnect detection)
- `tests/Feature/Performance/SlaTest.php` — SC-003 (≤2s answer persist), SC-005

---

## Security (Phase 10)

- **Route Guards**: All teacher routes require `auth:teacher`; student routes require `auth:student` + `absence.threshold` middleware
- **Teacher Scoping**: The `BelongsToTeacher` trait's `bootBelongsToTeacher()` method always overrides `teacher_id` when a teacher is authenticated, preventing cross-teacher data leakage via mass assignment
- **Cross-teacher Isolation** (SC-008): Verified by `CrossTeacherIsolationTest` — no teacher can access another teacher's modules, groups, students, or questions

## Troubleshooting

| Symptom | Fix |
|---|---|
| Student can log in but exam page 403s | Check `EnsureAbsenceBelowThreshold` — absence_count may be at threshold |
| Draft answers not saving | Open browser devtools → network; should see Livewire requests to `/livewire/update`; if offline, localStorage buffer should show pending items |
| Monitor not updating | Confirm `wire:poll.5s` is active; check browser console for Livewire errors |
| Timer wrong on client | Ignore — server `deadline` is the source of truth; client drift is cosmetic |
| Finalization missed at deadline | Ensure `php artisan schedule:run` is invoked every minute (cron/Task Scheduler) so `FinalizeOverdueSessionsJob` fires |
