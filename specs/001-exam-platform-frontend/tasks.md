---
description: "Frontend Implementation Tasks — Blade templates & styling (28 pages)"
---

# Frontend Tasks: Electronic Exam Platform (Blade + Tailwind)

**Input**: Pages specification from `pages.md` + Backend Livewire components already built  
**Output**: Responsive blade templates styled with Tailwind CSS v4  
**Design**: Google Stitch (10 pages) → Tailwind CSS (18 pages in same design language)

**Organization**: Grouped by user story / feature to mirror backend phases  

---

## Design Strategy

### Phase Breakdown
- **Phase 1**: Auth + Layout templates (foundational)
- **Phase 2**: Student core pages (US1+, demo-ready)
- **Phase 3**: Teacher core pages (US1, dashboard + settings)
- **Phase 4**: Teacher workspace (US1, modules/groups/students)
- **Phase 5**: Question bank (US2, CRUD questions)
- **Phase 6**: Exam management (US3, create/manage exams)
- **Phase 7**: Exam session + results (US3, **MVP checkpoint** 🎯)
- **Phase 8**: Monitor + grading components (US4/US5 prep)
- **Phase 9**: Grading & notifications (US5/US6)
- **Phase 10**: Polish, accessibility, responsive

### Stitch Design Pages (10 pages)
These pages will be designed first in Google Stitch to establish the design system. The remaining 18 will follow the same palette and component library.

1. **Login** (auth entry, sets color palette + typography)
2. **Teacher Dashboard** (dashboard layout pattern)
3. **Student Dashboard** (similar pattern, different content)
4. **Student Exam Session** (locked page, most interactive & complex)
5. **Teacher Exam Monitor** (live monitoring dashboard)
6. **Teacher Questions Create** (form pattern)
7. **Teacher Exam Results** (data viz, analytics)
8. **Teacher Grades Show** (table/grid pattern)
9. **Teacher Modules Index** (CRUD list pattern)
10. **Notifications Index** (notification list pattern)

---

## Phase 1: Auth & Layout Templates (Foundational)

**Purpose**: Establish design system, color palette, component library, layout templates.

**Checkpoint**: Auth pages work, layouts are consistent, ready for content pages.

### Tasks

- [ ] **F001** Create base layout templates:
  - `resources/views/layouts/app.blade.php` (master wrapper)
  - `resources/views/layouts/guest.blade.php` (auth pages)
  - `resources/views/layouts/teacher.blade.php` (sidebar nav, top bar)
  - `resources/views/layouts/student.blade.php` (sidebar nav, top bar)
  - Include: Tailwind, Alpine.js, Livewire scripts, CSRF token

- [ ] **F002** [P] Design system (Tailwind config):
  - Color variables (primary blue, success green, error red, warning orange, neutrals)
  - Typography scale (fonts, weights, sizes)
  - Spacing scale (consistent padding/margin)
  - Component utility classes
  - Store in `resources/css/app.css` + `tailwind.config.js` override

- [ ] **F003** Create reusable blade components:
  - `resources/views/components/button.blade.php` (primary, secondary, danger, sizes)
  - `resources/views/components/input.blade.php` (text, email, password, number, with error)
  - `resources/views/components/select.blade.php` (dropdown)
  - `resources/views/components/textarea.blade.php`
  - `resources/views/components/card.blade.php`
  - `resources/views/components/alert.blade.php` (success, error, warning, info)
  - `resources/views/components/badge.blade.php` (status indicators)
  - `resources/views/components/modal.blade.php` (confirmation dialogs)
  - `resources/views/components/table.blade.php` (sortable tables)
  - `resources/views/components/nav-sidebar.blade.php` (navigation sidebar)
  - `resources/views/components/nav-top.blade.php` (top bar, user menu)

- [ ] **F004** [P] Create navigation components:
  - Teacher sidebar links: Dashboard, Modules, Groups, Questions, Exams, Grades, Settings, Notifications, Profile
  - Student sidebar links: Dashboard, Exams, Grades, Notifications, Profile
  - Top bar: Logo, breadcrumbs, notifications bell, user dropdown (profile/logout)
  - Mobile hamburger menu (responsive)

- [ ] **F005** [Stitch Design] Design & build **Login page** (`resources/views/livewire/pages/auth/login.blade.php`):
  - Email input
  - Password input
  - "Forgot password?" link
  - Submit button
  - Branding (logo, app name)
  - Responsive (mobile-first)
  - This is the design reference for all other pages
  - Export color palette & typography to use in remaining pages

- [ ] **F006** [P] Build **Forgot Password** page (`resources/views/livewire/pages/auth/forgot-password.blade.php`):
  - Uses design from Login page
  - Email input
  - Submit button ("Send Reset Link")
  - Back to login link

- [ ] **F007** [P] Build **Reset Password** page (`resources/views/livewire/pages/auth/reset-password.blade.php`):
  - Email input (pre-filled)
  - New password input
  - Confirm password input
  - Submit button
  - Error message if token expired

- [ ] **F008** [P] Create shared view partials:
  - `resources/views/partials/success-message.blade.php`
  - `resources/views/partials/error-message.blade.php`
  - `resources/views/partials/validation-errors.blade.php`
  - `resources/views/partials/loading-spinner.blade.php`
  - `resources/views/partials/empty-state.blade.php`

---

## Phase 2: Student Core Pages

**Purpose**: Enable student login, dashboard, exam browsing. Independent of exams (exams are placeholders).

**Dependencies**: Phase 1 complete.  
**Checkpoint**: Student can log in, see dashboard, navigate to exams (not yet functional).

### Tasks

- [ ] **F009** [Stitch Design] Design & build **Student Dashboard** (`resources/views/livewire/student/dashboard.blade.php`):
  - Welcome message
  - Quick stats cards (absences remaining, exams completed, GPA)
  - Upcoming exams table (module, teacher, date, status)
  - Recent results table (module, teacher, date, score)
  - Responsive layout (stack on mobile)
  - Empty state for no activity

- [ ] **F010** [P] Build **Student Profile** (`resources/views/livewire/student/profile.blade.php`):
  - Name input
  - Email (read-only)
  - Password change section
  - Save button with success/error messages
  - Follows same design as Teacher Profile (builds on Stitch design)

- [ ] **F011** [P] Build **Student Exams Index** (`resources/views/livewire/student/exams/index.blade.php`):
  - Tabs: Upcoming / Completed
  - Table per tab (module, teacher, date, status, score if completed)
  - Click actions (navigate to waiting room or results)
  - Empty state per tab

- [ ] **F012** [P] Build **Student Exam Waiting** (`resources/views/livewire/student/exams/waiting.blade.php`):
  - Heading & exam info card
  - "Waiting for teacher to start..." message
  - Countdown (if before scheduled time)
  - Polling indicator (`wire:poll` updates status)
  - Simple, minimal design (student just waits)

- [ ] **F013** [P] Build **Student Exam Results** (`resources/views/livewire/student/exams/results.blade.php`):
  - Score card (prominent, X/20, %)
  - Per-question review cards:
    - Question text
    - Student's choice (green/red highlight)
    - Correct choice
    - Difficulty
  - Statistics breakdown (easy/medium/hard pass rates)
  - No edit buttons (read-only)

- [ ] **F014** [P] Build **Student Grades** (`resources/views/livewire/student/grades/index.blade.php`):
  - Summary (GPA, modules enrolled)
  - Grades table (module, exam, personal work, attendance, participation, final grade)
  - Read-only (no edits, only view)
  - Component breakdown visual or text
  - Empty state if no grades

- [ ] **F015** [P] Build **Student Notifications** (`resources/views/livewire/student/notifications/index.blade.php`):
  - Filter tabs (All / Account / Exam Reminder / Results)
  - Notification list (reverse chronological)
  - Each item: icon, title, message, date, read indicator, actions (mark read, delete)
  - Bulk action: Mark all as read
  - Empty state

---

## Phase 3: Teacher Core Pages

**Purpose**: Dashboard, profile, settings. Foundation for teacher workspace.

**Dependencies**: Phase 1 complete.  
**Checkpoint**: Teacher can log in, see dashboard, configure grading settings.

### Tasks

- [ ] **F016** [Stitch Design] Design & build **Teacher Dashboard** (`resources/views/livewire/teacher/dashboard.blade.php`):
  - Welcome message
  - Quick stats cards (students, groups, questions, exams this month)
  - Upcoming exams table (module, group, date, status)
  - Recent results table (module, group, avg score, date)
  - Recent notifications (unread count + link to inbox)
  - Responsive layout
  - Empty state for no activity
  - Establish card, table, stats patterns for reuse

- [ ] **F017** [P] Build **Teacher Profile** (`resources/views/livewire/teacher/profile.blade.php`):
  - Name input (editable)
  - Email (read-only)
  - Password change section
  - Save button with success/error messages
  - Follows Login design + Dashboard layout

- [ ] **F018** [Stitch Design] Design & build **Teacher Settings** (`resources/views/livewire/teacher/settings.blade.php`):
  - Heading: "Grading Configuration"
  - Four input fields (sliders or number inputs) for component maxes
  - Live sum display (updates as user types)
  - Warning if sum ≠ 20 (red text)
  - Save button (disabled if sum invalid)
  - Reset to default button
  - Success/error messages
  - Clean, focused design

- [ ] **F019** [P] Build **Teacher Notifications** (`resources/views/livewire/teacher/notifications/index.blade.php`):
  - Same pattern as Student Notifications (F015)
  - Filter tabs (All / Student Added / Exam Reminder / Results)
  - List of notifications
  - Bulk action: Mark all as read

---

## Phase 4: Teacher Workspace (US1 Integration)

**Purpose**: Modules, groups, students management. Teacher can build class structure.

**Dependencies**: Phase 1, Phase 3 complete.  
**Checkpoint**: Teacher can create modules, groups, add students. Students can log in and see their group memberships.

### Tasks

- [ ] **F020** [Stitch Design] Design & build **Teacher Modules Index** (`resources/views/livewire/teacher/modules/index.blade.php`):
  - Heading + Add button
  - Table/grid of modules: name, source (catalog/custom), groups count, questions count, actions
  - Empty state: "No modules yet"
  - Action buttons: edit, delete (with confirmation modal)
  - Modal for adding module (select from catalog or custom name)
  - Establish CRUD list pattern for reuse (questions, groups)

- [ ] **F021** [P] Build **Teacher Groups Index** (`resources/views/livewire/teacher/groups/index.blade.php`):
  - Heading + Add button
  - Filter by module (dropdown)
  - Table: group name, module, level, student count, actions
  - Empty state
  - Actions: view details (→ Groups Show), edit, delete
  - Modal for creating group (name, module, level)
  - Uses same pattern as Modules Index

- [ ] **F022** [P] Build **Teacher Groups Show** (`resources/views/livewire/teacher/groups/show.blade.php`):
  - Heading: "[Module] — [Group Name] ([Level])"
  - Tabs or sections:
    - **Students**: Add button, table of students (name, email, enrolled date, actions: view, remove)
    - **Grades**: Link to `/teacher/grades/{group_id}` (read-only until US5)
    - **Exams**: List of exams assigned to this group (status, avg score)
  - Modal for adding student (name, email inputs; password auto-generated)
  - Empty state if no students

- [ ] **F023** [P] Build **Teacher Students Show** (`resources/views/livewire/teacher/students/show.blade.php`):
  - Heading: "[Student Name]"
  - Tabs:
    - **Profile**: name, email, groups, enrolled date (all display)
    - **Grades**: per-module grades (read-only, placeholder until US5)
    - **Absences**: absence count, threshold, history list (placeholder for US7)
    - **Exams**: exams this student took (score, date, actions)
  - Card-based layout or tabs
  - Empty state if no data in a tab

---

## Phase 5: Question Bank (US2 Integration)

**Purpose**: CRUD questions. Teacher can build question bank.

**Dependencies**: Phase 1, Phase 4 complete.  
**Checkpoint**: Teacher can create, edit, filter, delete questions. Questions populate exam bank.

### Tasks

- [ ] **F024** [Stitch Design] Design & build **Teacher Questions Index** (`resources/views/livewire/teacher/questions/index.blade.php`):
  - Heading + Add button
  - Filter section (dropdowns: module, level, difficulty)
  - Table of questions: text (truncated), module, level, difficulty, choices (4), actions (edit, delete)
  - Empty state if no questions match filters
  - Action buttons: view details, edit, delete (with confirmation)
  - Pagination if many questions
  - Establishes filter + table pattern

- [ ] **F025** [Stitch Design] Design & build **Teacher Questions Create** (`resources/views/livewire/teacher/questions/create.blade.php`):
  - Heading: "Create Question"
  - Form fields:
    - Module (select dropdown)
    - Level (select dropdown)
    - Difficulty (select: easy/medium/hard)
    - Question text (textarea, large)
    - **Choices section** (4 repeatable inputs):
      - Choice A input + radio button (is correct?)
      - Choice B input + radio button
      - Choice C input + radio button
      - Choice D input + radio button
    - Validation indicator: "Exactly 1 correct choice required"
  - Save & Create Another button (or Save & Close)
  - Cancel button
  - Error messages on validation fail (red border + message)
  - Establishes form design pattern

- [ ] **F026** [P] Build **Teacher Questions Edit** (`resources/views/livewire/teacher/questions/edit.blade.php`):
  - Same as Create page (F025)
  - Pre-filled with current question data
  - Save button replaces Create
  - Add Delete button (with confirmation modal)

---

## Phase 6: Exam Management (US3a)

**Purpose**: Create, manage, start exams. Teacher can schedule exams for groups.

**Dependencies**: Phase 1, Phase 5 complete.  
**Checkpoint**: Teacher can create exams, see exam list, start exam (redirects to results or monitor). Students can see upcoming exams.

### Tasks

- [ ] **F027** [P] Build **Teacher Exams Index** (`resources/views/livewire/teacher/exams/index.blade.php`):
  - Heading + Add button
  - Tabs: Scheduled / Active / Ended
  - Table per tab: module/group, name, date, status, student count, actions
  - Actions: view (→ Show), start (if scheduled), results (if ended), monitor (if active)
  - Empty state per tab
  - Uses pattern from Modules/Questions index

- [ ] **F028** [Stitch Design] Design & build **Teacher Exams Create** (`resources/views/livewire/teacher/exams/create.blade.php`):
  - Heading: "Create Exam"
  - Form fields:
    - Module (select)
    - Group (select, filtered by module)
    - Exam name (text input)
    - Total questions (number input)
    - **Difficulty breakdown** (grid or inputs):
      - Easy: [input] / [total] (e.g., 3/6)
      - Medium: [input] / [total] (e.g., 2/6)
      - Hard: [input] / [total] (e.g., 1/6)
      - Live validation: sum must equal total, with warning
    - Duration (minutes) — number input
    - Scheduled date & time — datetime picker
  - Create button (disabled if validation fails)
  - Cancel button
  - Warning if question bank too small
  - Establishes complex form pattern

- [ ] **F029** [P] Build **Teacher Exams Show** (`resources/views/livewire/teacher/exams/show.blade.php`):
  - Heading: "[Exam Name] — [Module] / [Group]"
  - Status badge (Scheduled/Active/Ended)
  - Info card: module, group, level, date, duration, questions breakdown
  - Action buttons (conditional on status):
    - Start (if scheduled) — calls backend action, redirects to monitor
    - Monitor (if active) → `/teacher/exams/{id}/monitor`
    - Results (if ended) → `/teacher/exams/{id}/results`
  - Student roster (count, names)
  - Card-based layout

---

## Phase 7: Exam Session & Results (US3, **MVP Checkpoint**)

**Purpose**: The locked exam page & results. Most critical, most complex.

**Dependencies**: Phase 1, Phase 6, all backend domain logic complete.  
**Checkpoint**: Full exam flow works end-to-end. This is the MVP.

### Tasks

- [ ] **F030** [Stitch Design] Design & build **Student Exam Session** (`resources/views/livewire/student/exams/session.blade.php`):
  - **Top sticky bar**:
    - Exam name / Module
    - Timer (server-synced): MM:SS in large font, red when < 5 min
    - Status: "You are taking an exam" (with warning icon if lockdown triggered)
  - **Main scrollable area**:
    - All questions visible at once (scrollable, NOT paginated)
    - For each question (card layout):
      - Question # / total (e.g., "1/6")
      - Question text
      - 4 radio button choices (shuffled)
      - Selected choice highlighted / active state
      - Auto-save indicator: "Saving..." → "Saved" / "Failed"
  - **Side navigation** (or highlight):
    - Question navigator: show all question numbers, highlight current
    - Can click to jump to any question
  - **Bottom sticky bar**:
    - Submit button: "Submit & End Exam" (with confirmation modal)
    - Auto-save status ("All changes saved" / "Saving...")
  - **Lockdown visual feedback** (via Alpine.js):
    - Blur warning if window loses focus (red banner: "Focus on exam!")
    - Tab-switch warning (red banner: "Stay on this window!")
  - Design should feel secure, focused, minimal distraction
  - **This is the most complex page — allocate time**

- [ ] **F031** [P] Integrate **Alpine.js lockdown logic** for Session page (`resources/js/alpine/examSession.js`):
  - `visibilitychange` listener → record incident if leaves tab
  - `blur` listener → record incident if window loses focus
  - `beforeunload` listener → confirm before leaving page
  - `localStorage` buffer: queue answers while offline, sync on reconnect
  - `navigator.onLine` + fetch probe → detect reconnection
  - Countdown timer driven by `$wire.deadlineIso` (server time)
  - Retry loop for failed saves
  - Register in `resources/js/app.js`

- [ ] **F032** [Stitch Design] Design & build **Teacher Exam Results** (`resources/views/livewire/teacher/exams/results.blade.php`):
  - Heading: "[Exam Name] — Results"
  - **Summary stats** (cards or highlighted):
    - Total students: X
    - Average score: X/20
    - Highest: X / Lowest: X
    - Pass rate: X%
  - **Results table**:
    - Student name, score (X/20), % correct, actions (view detailed answers)
  - **Analytics section**:
    - Most-missed questions (list or chart): question #, pass rate
    - Difficulty breakdown (pie chart or bars): % correct per difficulty
    - Question-by-question pass rates (table)
  - Modal for detailed student answers (scrollable, shows all choices)
  - Data visualization will be key — use Tailwind-compatible chart library (Chart.js or similar)

---

## Phase 8: Monitor & Time Extension (US4 Prep)

**Purpose**: Live monitoring during exam, time extension controls, disconnection detection.

**Dependencies**: Phase 1, Phase 7 complete.  
**Checkpoint**: Teacher can see live student status, extend time, end exam. Students see timer update when extended.

### Tasks

- [ ] **F033** [Stitch Design] Design & build **Teacher Exam Monitor** (`resources/views/livewire/teacher/exams/monitor.blade.php`):
  - Heading: "[Exam Name] — Live Monitor"
  - **Top bar** (sticky):
    - Exam countdown timer (server-synced)
    - Action buttons:
      - "Extend Time (All)" → modal: "+X minutes for all students"
      - "End Exam" → confirmation modal ("End exam for all students?")
  - **Student status table** (polling `wire:poll.5s`):
    - Columns: Student name, Current question #/total, Time remaining, Connection status, Last heartbeat
    - **Connection status indicator**:
      - 🟢 Connected (green) → last heartbeat < 25s ago
      - 🔴 Disconnected (red) → last heartbeat > 25s ago
    - **Row styling**:
      - Normal background if connected
      - Red/orange highlight if disconnected
    - **Actions column**:
      - "Extend +X min" button → modal for individual extension
      - "Force finalize" button (emergency, with confirmation)
  - **Alerts**:
    - Audio alert when student disconnects (play `public/sounds/alert.mp3`)
    - Visual alert: row flashes red, sound plays (can be muted)
    - Toast notification: "Student [Name] disconnected"
  - **Empty state** if no students enrolled
  - Design should be scannable, real-time feel, urgent visual feedback

- [ ] **F034** [P] Integrate **Alpine.js monitor alerts** (`resources/js/alpine/monitor.js`):
  - Listen for Livewire events: `student-disconnected`, `student-reconnected`
  - Play audio alert on disconnect: `public/sounds/alert.mp3`
  - Show toast notification
  - Can mute audio (toggle button)
  - Register in `resources/js/app.js`

- [ ] **F035** [P] Add **audio asset**:
  - `public/sounds/alert.mp3` — short, clear alert sound (1-2 sec)
  - Or use Web Audio API to generate tone

---

## Phase 9: Grading & Notifications (US5/US6)

**Purpose**: Grade entry, grading template config, notification inboxes.

**Dependencies**: Phase 1, Phase 3, Phase 7 complete.  
**Checkpoint**: Teacher can enter grades, students can view grades and notifications. Full gradebook functional.

### Tasks

- [ ] **F036** [Stitch Design] Design & build **Teacher Grades Show** (`resources/views/livewire/teacher/grades/show.blade.php`):
  - Heading: "[Group Name] — Grades"
  - **Grade component grid**:
    - Rows: student names (left)
    - Columns: Exam (auto, read-only) | Personal Work | Attendance | Participation | Final Grade
    - Each cell is:
      - Read-only if Exam component (gray background)
      - Editable if manual component (white, focus highlight on click)
      - Green text if valid, red if exceeds max
    - Inline edit (click cell → input → blur/Enter to save)
    - Live validation feedback
    - Final Grade auto-updates as you edit
  - **Template info** (top or sidebar):
    - Show max values per component (e.g., "Personal Work max: 4")
    - Link to Settings to change maxes
  - Save button (auto-save on blur, or manual save)
  - Empty state if no students
  - Grid/table should be responsive-friendly (scroll horizontally on mobile if needed)
  - Design establishes table editing pattern

---

## Phase 10: Polish & Accessibility

**Purpose**: Responsive design, accessibility, performance, final tweaks.

**Dependencies**: Phases 1–9 complete.

### Tasks

- [ ] **F037** [P] Responsive design audit:
  - Test all pages on mobile (375px), tablet (768px), desktop (1920px)
  - Adjust layouts:
    - Sidebars → collapse to hamburger on mobile
    - Tables → horizontal scroll or card layout on mobile
    - Grids → single column on mobile
    - Forms → full-width inputs on mobile
  - Fix spacing, padding, font sizes for each breakpoint
  - Use Tailwind breakpoints: `sm:`, `md:`, `lg:`, `xl:`

- [ ] **F038** [P] Accessibility (WCAG AA):
  - Keyboard navigation: tab through all interactive elements
  - Focus indicators: visible `:focus` rings on all buttons, inputs, links
  - Color contrast: test with WebAIM contrast checker
  - Form labels: all inputs have associated `<label>` tags
  - Alt text: images have meaningful alt text
  - ARIA labels: modals, alerts, loading states have `aria-label` or `aria-labelledby`
  - Screen reader test: navigate with NVDA or JAWS
  - Error messages: associated with form fields via `aria-describedby`

- [ ] **F039** [P] Performance:
  - Minify CSS/JS
  - Lazy-load images
  - Defer non-critical JavaScript
  - Monitor Core Web Vitals (Lighthouse)
  - Test exam session page load time < 2s, Largest Contentful Paint < 1.5s

- [ ] **F040** [P] Consistency audit:
  - All pages use consistent button styles (primary, secondary, danger)
  - All form inputs have consistent styling and error states
  - All modals follow same layout and animations
  - All tables have consistent column alignment and sorting
  - Navigation breadcrumbs present and consistent

- [ ] **F041** [P] Dark mode support (optional):
  - Add Tailwind dark mode classes to all components
  - Toggle in user menu (if time permits)
  - Or defer to later version

- [ ] **F042** [P] Browser testing:
  - Chrome (latest)
  - Firefox (latest)
  - Safari (latest)
  - Edge (latest)
  - Mobile browsers: Chrome Mobile, Safari iOS

- [ ] **F043** [P] Documentation:
  - Update `quickstart.md` with frontend setup (Tailwind, Vite, npm run dev)
  - Document component usage (where to find blade components, how to use)
  - Document Stitch design files location (if exported/shared)
  - Document color palette and typography scale

- [ ] **F044** Run full test suite:
  - `npm run build` → verify CSS/JS bundle
  - `composer run dev` → test local dev server
  - Manual smoke test: login, navigate all pages, submit exam, view results
  - Performance: Lighthouse audit on each page

- [ ] **F045** Final Polish:
  - Fix any remaining layout bugs
  - Improve animations/transitions (hover states, loading, modals)
  - Ensure consistency in copy/wording across all pages
  - Proofread all text
  - Test all form validations

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1** (Auth + Layout): No dependencies. Start immediately.
- **Phase 2** (Student Core): Depends on Phase 1. Independent of other student features.
- **Phase 3** (Teacher Core): Depends on Phase 1. Independent of other teacher features.
- **Phase 4** (Workspace): Depends on Phase 1, Phase 3.
- **Phase 5** (Questions): Depends on Phase 1, Phase 4.
- **Phase 6** (Exam Mgmt): Depends on Phase 1, Phase 5.
- **Phase 7** (Exam Session): Depends on Phase 1, Phase 6. **CRITICAL for MVP.**
- **Phase 8** (Monitor): Depends on Phase 1, Phase 7.
- **Phase 9** (Grading/Notifications): Depends on Phase 1, Phase 3, Phase 7.
- **Phase 10** (Polish): Runs last, depends on Phases 1–9.

### Parallel Opportunities

- **Phase 1**: F002, F003, F004, F008 can run in parallel.
- **Phase 2**: F010, F011, F012, F013, F014, F015 can run in parallel (after F009).
- **Phase 3**: F017, F018, F019 can run in parallel (after F016).
- **Phase 4**: F021, F022, F023 can run in parallel (after F020).
- **Phase 5**: F025, F026 can run in parallel (after F024).
- **Phase 6**: F027, F028, F029 can run in parallel.
- **Phase 7**: F030, F031 in sequence (F031 depends on F030 design). F032 can be parallel.
- **Phase 8**: F033, F034, F035 can overlap.
- **Phase 9**: F036 standalone.
- **Phase 10**: F037–F045 can mostly run in parallel, then converge for final testing.

### MVP Checkpoint (after Phase 7)

Stop and validate before proceeding:
1. Run `npm run build` — verify no CSS/JS errors
2. Manual smoke test: login as teacher, create exam, login as student, take exam, view results
3. Teacher monitor: teacher sees live student status, can extend time
4. All 7 pages in Phase 7 are pixel-perfect to Stitch design

If all pass, MVP is shippable. Phases 8–10 add features and polish.

---

## Stitch Export & Handoff

### For Stitch Designs (10 pages)

1. **Export design system**:
   - Color palette (hex values, Tailwind names)
   - Typography (font family, sizes, weights)
   - Component library (button, input, card, table, modal, etc.)
   - Spacing/grid system
   - Save to `specs/001-exam-platform-frontend/design-tokens.md`

2. **Export per-page designs**:
   - F005 (Login) → detailed component mockups, states (default, focus, error, loading)
   - F009 (Student Dashboard) → card layouts, table layouts, empty states
   - F010 (Teacher Dashboard) → same
   - F024 (Questions Index) → table with filters, actions
   - F025 (Questions Create) → form with validation states
   - F028 (Exams Create) → complex form with live validation
   - F030 (Exam Session) → full-page lockdown UX, timer, choice states, auto-save feedback
   - F032 (Results) → stats cards, tables, chart placeholders
   - F033 (Monitor) → live table, connection status indicators, alert styling
   - F036 (Grades Grid) → editable grid, validation feedback

3. **Document design decisions**:
   - Why certain colors (trust for blue, urgency for red)
   - Typography hierarchy and usage
   - Component states and transitions
   - Mobile breakpoints and adaptations

### For remaining 18 pages

- Follow Stitch palette and component library
- Use same typography scale
- Reuse component styles (buttons, inputs, cards, tables, modals)
- Adapt layouts for different content, but maintain visual consistency
- Document any deviations and add to design system

---

## Notes

- **Blade component structure**: Keep `resources/views/components/` organized. Prefix with `forms/`, `layouts/`, `ui/` as needed.
- **Tailwind best practices**: 
  - Use utility-first approach
  - Create custom components for complex reusable patterns
  - Avoid inline styles
  - Use CSS custom properties for theme colors
- **Livewire integration**: Blades are views for already-built Livewire components. Keep blade logic minimal (just template).
- **Testing**: Manual testing after each page. Automated E2E tests can follow (Dusk or Cypress).
- **Git workflow**: Commit per phase or per 2–3 related tasks. Run `php artisan pint` before committing.

