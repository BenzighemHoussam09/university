# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Electronic exam platform (SaaS) for Algerian universities. Two roles: **Teacher** (created by admin) and **Student** (created by teacher). All data is scoped per teacher via `teacher_id` on every relevant table — there is no multi-tenancy beyond this column-level isolation.

## Stack

- Laravel 12, Livewire 4, Alpine.js, Tailwind CSS v4, MySQL (single DB)
- Auth via Laravel Breeze (email + password only)
- Queue driver used for notifications (email + in-platform)

## Commands

```bash
composer run dev      # starts php artisan serve + queue:listen + pail + vite concurrently
composer run test     # clears config cache then runs phpunit
composer run setup    # first-time install (composer, .env, key, migrate, npm, build)
php artisan pint      # code style fixer (CI enforced)
php artisan test --filter=ClassName::methodName   # single test
```

## Architecture

### Roles & Guards
Two guards: `teacher` and `student`. Each has its own middleware group, routes file, and dashboard. Admin management is out of scope for the initial build (admin creates teacher accounts directly in DB or via seeder).

### Data Scoping
Every model that belongs to a teacher must have `teacher_id`. Use a global scope trait (`BelongsToTeacher`) applied to all such models so queries are automatically filtered. Never query cross-teacher data.

### Core Domain Models
- `Teacher` / `Student`
- `Module` — shared bank; teachers pick from it or add new ones
- `Level` — fixed enum: L1/L2/L3/M1/M2
- `Group` — belongs to teacher + module + level
- `Question` — belongs to teacher + module + level; difficulty: easy/medium/hard; MCQ only (4 choices, 1 correct); choices always shuffled at display time
- `Exam` — belongs to teacher + group; stores question count per difficulty, duration in minutes
- `ExamSession` — one per student per exam; stores `started_at`, `deadline` (= started_at + duration + global_extra + student_extra), status (waiting/active/completed)
- `StudentAnswer` — one per question per session; status: draft → final
- `GradingTemplate` — one system-wide template; each teacher gets a copy on account creation; stores weights for: exam, personal_work, attendance, participation
- `Absence` — per student; system-wide threshold (default 5) stored in settings; student blocked when count ≥ threshold

### Exam Flow
1. Teacher creates exam → system draws questions per difficulty distribution at session start (not at creation)
2. Question assignment algorithm: exhaust bank without repeats first; when bank runs out, cycle again with reshuffled order
3. Three randomization levels: which questions, question order, choice order — all per-student
4. `deadline` is always server-computed; client timer is display-only (synced from server)
5. Every answer choice triggers an immediate DB write as `draft`
6. `localStorage` is a client-side buffer only — used to queue writes during disconnection, synced silently on reconnect
7. Final submission (manual confirm or timer expiry) flips all drafts → final and redirects to results

### Real-time (Livewire + Heartbeat)
- Student page sends a heartbeat every N seconds via Livewire polling or Alpine fetch
- Teacher monitor page (`exams/{id}/monitor`) uses Livewire polling to refresh student status
- Visual + audio alert on teacher side when heartbeat stops
- Teacher can extend time (global or per-student) from monitor page; this updates `ExamSession.global_extra` or `student_extra` and recalculates `deadline`

### Grading
`final_grade = (exam_score × exam_weight) + (personal_work × pw_weight) + (attendance × att_weight) + (participation × part_weight)`
Exam score is auto-calculated; other components are manually entered by teacher per student.

### Pages
- Shared: `login`, `forgot-password`, `reset-password`
- Teacher: `dashboard`, `profile`, `settings` (grading weights), `notifications`, `modules`, `groups`, `groups/{id}`, `students/{id}`, `questions`, `questions/create`, `questions/{id}/edit`, `exams`, `exams/create`, `exams/{id}`, `exams/{id}/monitor`, `exams/{id}/results`, `grades/{group_id}`
- Student: `dashboard`, `profile`, `notifications`, `exams`, `exams/{id}/waiting`, `exams/{id}/session` (fully locked page), `exams/{id}/results`, `grades`

### Student Exam Session Page Constraints
- Page is fully locked: no tab switching, no app switching (use `visibilitychange` + `blur` events via Alpine.js)
- All questions visible at once (scroll, not paginated)
- No back-navigation after final submission
