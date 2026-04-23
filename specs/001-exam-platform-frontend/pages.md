---
description: "Frontend Pages Specification — All 28 pages with content requirements"
---

# Frontend Pages Specification (28 Pages)

**Purpose**: Detailed breakdown of every page in the exam platform, organized by role.
**Design Tool**: Google Stitch (up to 10 pages), Tailwind CSS (remaining 18 pages).

---

## 🔐 Shared / Auth Pages (3 pages)

### 1. Login
**Route**: `/login`  
**Guards**: Guest (unauthenticated)  
**Purpose**: Entry point for both teachers and students  
**Content**:
- Email input
- Password input
- "Forgot password?" link
- Submit button (text changes based on redirect intent)
- Language/theme toggle (optional)
- Branding: logo, app name

**UX Notes**:
- Detect if user is teacher or student and redirect accordingly
- Responsive: mobile-first, full-width on desktop
- Clear error messages for invalid credentials

---

### 2. Forgot Password
**Route**: `/forgot-password`  
**Guards**: Guest  
**Purpose**: Request password reset email  
**Content**:
- Email input
- Submit button ("Send Reset Link")
- Back to login link
- Confirmation message on success

---

### 3. Reset Password
**Route**: `/reset-password/{token}`  
**Guards**: Guest  
**Purpose**: Set new password via emailed token  
**Content**:
- Email input (pre-filled from token)
- New password input
- Confirm password input
- Submit button ("Reset Password")
- Error if token expired

---

## 👨‍🏫 Teacher Pages (17 pages)

### Layout: Teacher
**Sidebar**: Logo, nav links (Dashboard, Modules, Groups, Questions, Exams, Grades, Settings, Notifications, Profile)  
**Top Bar**: Logged-in teacher name, logout, notifications bell  
**Breadcrumbs**: Show current location  

---

### 4. Teacher Dashboard
**Route**: `/teacher/dashboard`  
**Guards**: `teacher`  
**Purpose**: Overview of teacher's workspace  
**Content**:
- Welcome message ("Welcome, [Name]")
- Quick stats cards:
  - Total students
  - Total groups
  - Total questions in bank
  - Exams this month
- Upcoming exams (next 7 days) — table with columns: module, group, date, status
- Recent results (last 5 exams) — table with columns: module, group, avg score, date
- Recent notifications (unread count)
- Empty state message if no activity

**Actions**:
- Click exam → navigate to exam details
- Click notification bell → go to notifications inbox

---

### 5. Teacher Profile
**Route**: `/teacher/profile`  
**Guards**: `teacher`  
**Purpose**: View and edit personal info  
**Content**:
- Name input (editable)
- Email display (read-only)
- Current password input
- New password input
- Confirm new password input
- Save button
- Success/error messages

---

### 6. Teacher Settings (Grading Template)
**Route**: `/teacher/settings`  
**Guards**: `teacher`  
**Purpose**: Configure grading component weights  
**Content**:
- Heading: "Grading Configuration"
- Four sliders/inputs:
  - Exam component max (default 12)
  - Personal work max (default 4)
  - Attendance max (default 2)
  - Participation max (default 2)
- Live sum indicator (must equal 20)
- Warning if sum ≠ 20
- Save button (disabled if sum invalid)
- Reset to default button

---

### 7. Teacher Modules (Index)
**Route**: `/teacher/modules`  
**Guards**: `teacher`  
**Purpose**: Manage modules this teacher teaches  
**Content**:
- Heading: "My Modules"
- Add button: "Add Module"
- Table/Grid of modules:
  - Module name
  - Source (Catalog / Custom)
  - Groups count
  - Questions count
  - Actions (edit name, delete)
- Empty state: "No modules yet. Add one to get started."
- Modal/form for adding module (select from catalog OR enter custom name)

---

### 8. Teacher Groups (Index)
**Route**: `/teacher/groups`  
**Guards**: `teacher`  
**Purpose**: Manage groups across all modules  
**Content**:
- Heading: "My Groups"
- Add button: "Create Group"
- Filter by module (dropdown)
- Table/Grid of groups:
  - Group name
  - Module
  - Level (L1/L2/L3/M1/M2)
  - Student count
  - Actions (view details, edit, delete)
- Empty state if no groups

---

### 9. Teacher Groups Show (Details)
**Route**: `/teacher/groups/{id}`  
**Guards**: `teacher`  
**Purpose**: Manage students in a specific group  
**Content**:
- Heading: "[Module] — [Group Name] ([Level])"
- Tabs or sections:
  - **Students Tab**:
    - Add button: "Add Student"
    - Table: student name, email, enrolled date, actions (view profile, remove)
    - Empty state
  - **Grades Tab** (read-only until US5):
    - Link to `/teacher/grades/{group_id}`
  - **Exams Tab** (read-only):
    - List of exams assigned to this group, status, avg score
- Modal/form for adding student (enter name, email; auto-generate password)

---

### 10. Teacher Students Show (Profile)
**Route**: `/teacher/students/{id}`  
**Guards**: `teacher`  
**Purpose**: View and manage one student's profile  
**Content**:
- Heading: "[Student Name]"
- Tabs:
  - **Profile Tab**:
    - Name (display)
    - Email (display)
    - Groups (list)
    - Enrolled date
  - **Grades Tab**:
    - Per-module grades (read-only until US5)
  - **Absences Tab**:
    - Absence count (display)
    - Absence threshold (display)
    - Add absence button (until US7)
    - Absence history (list with date, reason)
  - **Exams Tab**:
    - Exams this student took, score, date

---

### 11. Teacher Questions (Index)
**Route**: `/teacher/questions`  
**Guards**: `teacher`  
**Purpose**: Browse and manage question bank  
**Content**:
- Heading: "Question Bank"
- Add button: "Create Question"
- Filters (dropdowns):
  - Module
  - Level
  - Difficulty
- Table/Grid of questions:
  - Question text (truncated)
  - Module
  - Level
  - Difficulty
  - Choices (4)
  - Actions (view, edit, delete)
- Empty state if no questions match filters

---

### 12. Teacher Questions Create
**Route**: `/teacher/questions/create`  
**Guards**: `teacher`  
**Purpose**: Create a new MCQ question  
**Content**:
- Heading: "Create Question"
- Form fields:
  - Module (select)
  - Level (select)
  - Difficulty (select: easy/medium/hard)
  - Question text (textarea)
  - **Choices Section** (4 inputs):
    - Choice A input + radio (is correct?)
    - Choice B input + radio
    - Choice C input + radio
    - Choice D input + radio
  - Validation: must have exactly 1 correct choice
  - Save button
  - Cancel button (back to index)
- Error messages on validation fail

---

### 13. Teacher Questions Edit
**Route**: `/teacher/questions/{id}/edit`  
**Guards**: `teacher`  
**Purpose**: Edit existing question  
**Content**: Same as Create page, pre-filled with current data

---

### 14. Teacher Exams (Index)
**Route**: `/teacher/exams`  
**Guards**: `teacher`  
**Purpose**: Manage all exams  
**Content**:
- Heading: "Exams"
- Add button: "Create Exam"
- Tabs/filters by status:
  - **Scheduled** (future)
  - **Active** (currently running)
  - **Ended** (past)
- Table for each tab:
  - Exam name / Module / Group
  - Scheduled date / Status
  - Student count
  - Actions (view, start, results, monitor)
- Empty state per tab

---

### 15. Teacher Exams Create
**Route**: `/teacher/exams/create`  
**Guards**: `teacher`  
**Purpose**: Create new exam  
**Content**:
- Heading: "Create Exam"
- Form fields:
  - Module (select)
  - Group (select, filtered by module)
  - Exam name (text input)
  - Total questions (number input)
  - Questions per difficulty (grid):
    - Easy: __ / __ (e.g., 3/6)
    - Medium: __ / __ (e.g., 2/6)
    - Hard: __ / __ (e.g., 1/6)
  - Validation: must sum to total
  - Duration (minutes) (number input)
  - Scheduled date & time (datetime picker)
  - Create button
  - Cancel button
- Warnings if bank too small

---

### 16. Teacher Exams Show (Details)
**Route**: `/teacher/exams/{id}`  
**Guards**: `teacher`  
**Purpose**: View exam details and start it  
**Content**:
- Heading: "[Exam Name] — [Module] / [Group]"
- Status badge (Scheduled/Active/Ended)
- Exam info card:
  - Module, Group, Level
  - Scheduled date/time
  - Duration
  - Questions breakdown (easy/medium/hard)
- Start button (if status=scheduled)
- Monitor button (if status=active) → redirects to /exams/{id}/monitor
- Results button (if status=ended) → redirects to /exams/{id}/results
- Students enrolled (count)

---

### 17. Teacher Exams Monitor (Live)
**Route**: `/teacher/exams/{id}/monitor`  
**Guards**: `teacher`  
**Purpose**: Real-time monitoring during active exam  
**Content**:
- Heading: "[Exam Name] — Live Monitor"
- Exam countdown timer (server-synced)
- Action buttons (top-right):
  - Extend time (global) → modal: "+X minutes for all"
  - End exam → confirmation modal
- Student status table:
  - Student name
  - Current question number / total
  - Time remaining (per-student)
  - Connection status (🟢 Connected / 🔴 Disconnected)
  - Last heartbeat (relative time, e.g., "30s ago")
  - Actions:
    - Extend time (individual) → modal: "+X minutes for [Student]"
- **Visual alerts**:
  - Row highlights red if student disconnects
  - Audio alert plays (can be muted)
- Empty state if no students

---

### 18. Teacher Exams Results
**Route**: `/teacher/exams/{id}/results`  
**Guards**: `teacher`  
**Purpose**: View exam results and statistics  
**Content**:
- Heading: "[Exam Name] — Results"
- **Summary stats**:
  - Total students: X
  - Average score: X/20
  - Highest: X
  - Lowest: X
- **Results table**:
  - Student name
  - Score (X/20)
  - % correct
  - Actions: view detailed answers
- **Analytics**:
  - Most-missed questions (top 10)
  - Difficulty breakdown (pie chart or bars)
  - Question-by-question pass rates
- Modal/detail view for per-student answers (shows their choices vs correct)

---

### 19. Teacher Grades Show (Grid)
**Route**: `/teacher/grades/{group_id}`  
**Guards**: `teacher`  
**Purpose**: Enter and manage grades per student per module  
**Content**:
- Heading: "[Group Name] — Grades"
- **Grade component grid**:
  - Rows: students
  - Columns: Exam Component (auto) | Personal Work | Attendance | Participation | Final Grade
- **Each cell** is editable (inline edit on click):
  - Exam component: read-only (auto-calculated from exam sessions)
  - Other 3: inputs with validation against template maxes
  - Final grade: read-only (calculated sum)
- **Validation**:
  - Personal work ≤ template.personal_work_max
  - etc.
  - Live error messages
- Save button (auto-save on blur optional)
- Empty state if no students

---

### 20. Teacher Notifications (Inbox)
**Route**: `/teacher/notifications`  
**Guards**: `teacher`  
**Purpose**: View notification history  
**Content**:
- Heading: "Notifications"
- Filter by type (All / Student Added / Exam Reminder / Results Available)
- List of notifications (reverse chronological):
  - Notification type icon
  - Title / message
  - Date/time
  - Read/unread indicator
  - Actions: mark as read, delete
- Empty state: "No notifications"
- Bulk action: Mark all as read

---

## 👨‍🎓 Student Pages (8 pages)

### Layout: Student
**Sidebar** (or mobile menu): Logo, nav (Dashboard, Exams, Grades, Notifications, Profile)  
**Top Bar**: Student name, logout, notifications bell  

---

### 21. Student Dashboard
**Route**: `/student/dashboard`  
**Guards**: `student`  
**Purpose**: Home page overview  
**Content**:
- Welcome message: "Welcome, [Student Name]"
- Quick stats:
  - Absences remaining (threshold - absence_count)
  - Exams completed this month
  - Current GPA / average score
- **Upcoming exams** (next 7 days):
  - Table: module, teacher, scheduled date, status (scheduled/waiting/active)
  - Actions: view exam details
  - "No upcoming exams" empty state
- **Recent results** (last 3 exams):
  - Table: module, teacher, date, score
  - Actions: view detailed results
- Absence count display (red if near threshold)

---

### 22. Student Profile
**Route**: `/student/profile`  
**Guards**: `student`  
**Purpose**: View and edit personal info  
**Content**:
- Name (editable)
- Email (read-only)
- Current password input
- New password input
- Confirm new password input
- Save button

---

### 23. Student Exams (Index)
**Route**: `/student/exams`  
**Guards**: `student`  
**Purpose**: View all exams (upcoming and past)  
**Content**:
- Heading: "Exams"
- Tabs:
  - **Upcoming**: scheduled exams (can click to go to waiting room once scheduled_at is reached)
  - **Completed**: past exams (can click to view results)
- Table for each:
  - Module / Teacher
  - Scheduled date
  - Status (Scheduled/Waiting/Active/Completed)
  - Score (if completed)
  - Actions: view
- Empty state per tab

---

### 24. Student Exams Waiting (Room)
**Route**: `/student/exams/{id}/waiting`  
**Guards**: `student`  
**Purpose**: Wait for teacher to start exam  
**Content**:
- Heading: "[Module] — Waiting for exam to start"
- Exam info card:
  - Module / Teacher
  - Scheduled start time
  - Duration
  - Questions count (easy/medium/hard breakdown)
- **Status indicator** (polling):
  - "Waiting for teacher to start..."
  - Countdown to scheduled time (if before)
  - Auto-redirect to session page once status becomes `active`
- Instructions: "Please wait. Your teacher will start the exam shortly."
- Cannot leave this page before exam starts

---

### 25. Student Exams Session (🔒 Locked Page)
**Route**: `/student/exams/{id}/session`  
**Guards**: `student`, `EnsureExamNotCompleted`  
**Purpose**: Take the exam (most critical, most complex)  
**Content**:
- **Top bar** (sticky):
  - Exam name / Module
  - Timer (server-synced) — shows minutes:seconds, turns red as deadline approaches
  - Status: "You are taking an exam"
- **Main question area**:
  - All questions visible (scrollable, not paginated)
  - For each question (cards):
    - Question number / total (e.g., "1/6")
    - Question text
    - 4 choices (radio buttons, shuffled)
    - Selected choice is highlighted
    - Auto-save indicator ("Saved" / "Saving..." / "Failed to save")
- **Navigation**:
  - Scroll through questions
  - Can jump to any question (highlight current one)
  - No back button after final submission
- **Bottom bar** (sticky):
  - Submit button: "Submit & End Exam" (confirmation modal)
  - Auto-save status
- **Lockdown** (Alpine.js):
  - ❌ Cannot tab-switch (shows warning)
  - ❌ Cannot alt-tab (shows warning)
  - ❌ Cannot refresh or close (confirmation)
  - ❌ Window blur triggers incident record
  - localStorage buffer for offline saves

---

### 26. Student Exams Results
**Route**: `/student/exams/{id}/results`  
**Guards**: `student`  
**Purpose**: View exam results and answers  
**Content**:
- Heading: "[Module] — Your Results"
- **Score card** (prominent):
  - Your score: X / 20
  - Pass/Fail indicator
  - Percentage correct
- **Per-question review**:
  - For each question (cards):
    - Question text
    - Your choice (highlighted green if correct, red if wrong)
    - Correct choice (always shown)
    - Difficulty indicator
- **Statistics**:
  - Easy questions: X/Y correct
  - Medium: X/Y correct
  - Hard: X/Y correct
  - Time taken vs. duration

---

### 27. Student Grades (Consolidated Report)
**Route**: `/student/grades`  
**Guards**: `student`  
**Purpose**: View all grades across modules  
**Content**:
- Heading: "Your Grades"
- **Summary**:
  - Current GPA / average
  - Total modules enrolled
- **Grades table** (by module):
  - Module name / Teacher
  - Exam component (auto)
  - Personal work (if entered)
  - Attendance (if entered)
  - Participation (if entered)
  - Final grade (calculated sum)
- **Component breakdown** (table or visual):
  - Shows max values for each component
- Empty state: "No grades yet"

---

### 28. Student Notifications (Inbox)
**Route**: `/student/notifications`  
**Guards**: `student`  
**Purpose**: View notification history  
**Content**:
- Heading: "Notifications"
- Filter by type (All / Account Created / Exam Reminder / Results Available)
- List of notifications (reverse chronological):
  - Icon / title / message
  - Date/time
  - Read/unread indicator
  - Actions: mark as read, delete
- Empty state
- Bulk action: Mark all as read

---

## Design Considerations

### Color Palette
- **Primary**: Blue (trust, education)
- **Success**: Green (correct answers, passed)
- **Error**: Red (wrong answers, disconnected, threshold exceeded)
- **Warning**: Orange (near deadline, low score)
- **Neutral**: Gray (disabled, inactive)
- **Background**: Light (white / off-white for accessibility)

### Typography
- **Headings**: Bold, clear hierarchy (H1, H2, H3)
- **Body**: Readable, consistent line-height
- **Mono**: Code snippets if needed

### Components (to be designed in Stitch)
- Buttons (primary, secondary, danger)
- Form inputs (text, email, password, number, select, textarea)
- Radio buttons & checkboxes
- Modals & confirmations
- Tables & grids
- Cards
- Alerts & notifications
- Badges & status indicators
- Breadcrumbs
- Navigation (sidebar, top bar)
- Timers & countdowns
- Progress bars

### Accessibility
- WCAG AA compliance
- Keyboard navigation
- Color contrast ratios
- Screen reader friendly
- Focus indicators

---

## 📐 Design Brief: 10 Stitch Design Pages

**⚠️ CRITICAL: Design System Consistency**

**DO THIS IN ORDER:**
1. **Design ONLY the Login page first** (Prompt 1)
2. **Document the design system**: Save the colors, typography, spacing, button styles, card styles, and component library from the Login page
3. **For ALL other 9 pages**: Use the EXACT same colors, typography, spacing, and component patterns as Login
4. **Consistency is key**: Every page should feel like it belongs to the same platform — same primary color, same font family, same button styles, same card design, same spacing rhythms

**Design System Continuity**:
- Primary color palette: Established in Login, reused in all 9 pages
- Typography: Same font families and sizes across all pages
- Component library: Buttons, inputs, cards, tables, modals — all designed once, reused identically
- Spacing: Same padding, margins, gaps consistent across all pages
- Icons, badges, status indicators: Single style language

Do NOT let each page diverge into its own design. The platform must feel cohesive and unified.

### Design System Requirements
- **Language**: Full Arabic (RTL) support — CONSISTENT across all 10 pages
- **Context**: Electronic exam platform for Algerian universities
- **Users**: Teachers and students
- **Tone**: Professional, trustworthy, educational, accessible — UNIFIED tone
- **Accessibility**: WCAG AA compliance, clear focus states, sufficient color contrast — STANDARD across all pages
- **Responsiveness**: Mobile-first, tablet-friendly, desktop-optimized — SAME breakpoints everywhere
- **Visual Hierarchy**: Consistent heading sizes, emphasis levels, visual weight

---

### Stitch Prompt 1: Login Page (Entry Point)
```
Design a professional login page for an educational exam platform used in Algerian universities.
The page serves both teachers and students. Include:
- Centered layout with app logo/branding at the top
- Email input field with clear label
- Password input field
- "Forgot password?" link below password
- Large, prominent submit button
- Optional: language/theme toggle in top-right
- Design should feel secure, trustworthy, and welcoming
- Should work on mobile, tablet, and desktop
- Support full RTL (Arabic) layout
- Ensure high color contrast and clear focus states for accessibility
Let the design system define the primary color, typography scale, and component library.
```

---

### Stitch Prompt 2: Teacher Dashboard (Hub Page)
```
Design a comprehensive dashboard for teachers using the EXACT design system established in the Login page.
THIS PAGE MUST USE:
- Same primary and secondary colors from Login
- Same typography (font family, heading sizes, body text size)
- Same spacing/padding rules and grid system
- Same button styles (primary, secondary, danger)
- Same card design and shadows from Login
- Same input/form component styles

Include these sections:
- Welcome greeting area (personalized by teacher name) using the same typography as Login
- 4 quick stat cards using the same card style as Login, with same spacing and colors
- "Upcoming exams" section: table/list with consistent column spacing
- "Recent results" section: table/list using same typography and spacing
- Recent notifications preview
- Clear visual hierarchy (use same heading sizes as Login)
- Design for mobile responsiveness (stack sections vertically on small screens)
- RTL-ready layout (use same RTL approach as Login)

CRITICAL: This page should look like it's part of the same platform as the Login page. Same colors, same fonts, same component styles. Consistency over uniqueness.
```

---

### Stitch Prompt 3: Student Dashboard (Personal Hub)
```
Design a welcoming dashboard for students using the EXACT design system from the Login page.
THIS PAGE MUST USE:
- Same primary and secondary colors from Login
- Same typography (font family, heading sizes, body sizes)
- Same spacing, padding, and grid system
- Same button styles
- Same card design and styles
- Same input components and form styling

Include:
- Welcome message area (personalized by student name) using Login typography
- Quick stats cards using the same card design as Login (exam results, absences, GPA)
- "Upcoming exams" section: table/list with consistent spacing from Login
- "Recent results" section: table/list with same typography and colors
- Absence count display (with visual warning using the same warning color as Login)
- Mobile-responsive layout (same breakpoints as Login)
- RTL support (same approach as Login)

CRITICAL: Use the exact same design language as Login and Teacher Dashboard. Same colors, same fonts, same components. No variations. The platform must feel unified.
```

---

### Stitch Prompt 4: Student Exam Session (Locked Page - CRITICAL)
```
Design the most important page: the exam-taking interface using the exact design system from Login.
THIS PAGE MUST USE:
- Same primary color and secondary colors from Login
- Same typography, font family, heading and body sizes
- Same button styles (primary for Submit, secondary for optional actions)
- Same input/radio button components as Login
- Same spacing and grid system
- Same error/warning/success color palette from Login
- Same card design for question cards

Include:
- Sticky top bar with: exam name, module, countdown timer (MM:SS format in same typography as Login), status message
- Main scrollable area showing all questions (not paginated)
- Question cards using the same card style as Login, with same padding and spacing
- 4 radio button choices using the same input components as Login
- Visual indicator of selected answer using same color palette
- Auto-save status indicator using same success/error colors from Login
- Sticky bottom bar with: prominent "Submit & End Exam" button (same style as Login primary button), auto-save status
- Question navigator with same spacing and typography as Login
- Lockdown warning messages using same warning color as Login
- Timer changes to warning color (from Login palette) as deadline approaches
- Support Arabic fully (RTL, same approach as Login)
- Mobile-friendly but optimized for desktop

CRITICAL: This is the most important page. Use the exact same design, colors, fonts, and components as every other page. Consistency creates trust.
```

---

### Stitch Prompt 5: Teacher Exam Monitor (Real-Time Dashboard)
```
Design a live monitoring dashboard for teachers using the exact design system from Login.
THIS PAGE MUST USE:
- Same primary color, secondary colors, and success/error/warning color palette from Login
- Same typography throughout
- Same button styles (primary for main actions, secondary for less important)
- Same table/grid component styling (if created in Login)
- Same spacing and padding system
- Same status indicator colors: green for connected (success color from Login), red for disconnected (error color from Login)
- Same font family and heading sizes

Include:
- Top bar with: exam name, exam countdown timer (same typography and color as Login), action buttons (same style as Login)
- Main table showing each student with columns using same spacing as Login:
  * Student name
  * Current question number / total
  * Time remaining (same countdown style as exam session page)
  * Connection status indicator (use green from Login for "connected", red from Login for "disconnected")
  * Last heartbeat timing
  * Individual action button (same button style as Login primary buttons)
- Row highlighting: subtle for connected (normal background), red/alert color (from Login error palette) for disconnected
- Visual alerts: row highlights using same warning color as Login
- Desktop-optimized (teachers use laptops)
- RTL support (same approach as Login)

CRITICAL: Reuse the table and status indicator styles from the Login design system. No new colors, no new button styles. Consistency.
```

---

### Stitch Prompt 6: Teacher Questions Create (Form Pattern)
```
Design a professional form for creating exam questions using the exact design system from Login.
THIS PAGE MUST USE:
- Same form input styles from Login (text, dropdown, textarea)
- Same primary color for submit button
- Same typography for labels, placeholders, and error messages
- Same spacing and padding as Login forms
- Same error/validation color (red from Login for errors, green from Login for validation)
- Same button styles (primary for Save, secondary for Cancel)
- Same focus states and input styling as Login

Include:
- Page heading using same typography as Login
- Form sections with labels:
  * Module dropdown (same style as Login dropdowns)
  * Level dropdown (same style)
  * Difficulty dropdown (same style)
  * Question text area (same styling as Login textareas)
  * Choices section with 4 identical inputs:
    - Choice A input + radio button (same radio style as Login) + "is this the correct answer?"
    - Choice B (same styling)
    - Choice C (same styling)
    - Choice D (same styling)
  * Validation message using same error color from Login: "Exactly 1 correct choice required"
- Buttons: Save (primary button from Login style), Cancel (secondary button from Login style)
- Error messages using same red color and spacing as Login
- Validation states: red border when invalid (same error color as Login), green checkmark when valid (same success color as Login)
- Mobile-responsive (single column on small screens, same breakpoints as Login)
- RTL support (same approach as Login)

CRITICAL: Every form input, dropdown, button, error message must match the Login page exactly. Reuse the form component library.
```

---

### Stitch Prompt 7: Teacher Exam Results (Analytics Page)
```
Design an analytics-focused results page using the exact design system from Login.
THIS PAGE MUST USE:
- Same card design from Login for stat cards
- Same typography throughout (headings, labels, data)
- Same primary color for important metrics, same secondary colors for supporting info
- Same table styling as other pages
- Same spacing and padding system
- Same button styles if any actions are needed
- Same colors for charts (use primary, secondary, success, warning, error colors from Login)

Include:
- Page heading using Login typography: "Exam Results — [Exam Name]"
- Summary stats section with 4 cards using the SAME card design as Login: Total students, Average score, Highest score, Lowest score
- Results table using same table styling as Exam Monitor page:
  * Student name
  * Score (X/20) in same typography as Login
  * % Correct
  * Actions button (same style as Login)
- Analytics visualizations using Login color palette:
  * "Most-missed questions" list: top 10 (same list styling as other pages)
  * "Difficulty breakdown": pie chart or bar chart (use primary, secondary colors from Login)
  * Question-by-question pass rates: table using same styling as Results table
- Click action: click on student row (same interaction pattern as other tables)
- Design should be data-focused, using same visual language as rest of platform
- Use appropriate chart styles that work in Arabic/RTL (same approach as Login)
- Mobile-responsive (same breakpoints as Login)

CRITICAL: Reuse card styles, table styles, typography, and colors from Login. No new visual elements.
```

---

### Stitch Prompt 8: Teacher Grades (Editable Grid)
```
Design an inline-editable grade entry grid using the exact design system from Login.
THIS PAGE MUST USE:
- Same table styling from other pages
- Same input component styling from Login (for editable cells)
- Same typography and colors throughout
- Same button styles for Save
- Same success color (green from Login) for valid entries
- Same error color (red from Login) for invalid/exceeding entries
- Same spacing and padding system
- Same font sizes for readability

Include:
- Page heading using Login typography: "[Group Name] — Grades"
- Info box showing max values (same box style as cards in Login): "Exam max: 12, Personal Work max: 4..."
- Grid/table with consistent styling:
  * Rows: student names (left column, same typography as Login)
  * Columns: Exam Component (read-only, gray background), Personal Work (editable), Attendance (editable), Participation (editable), Final Grade (read-only, gray background)
  * Editable cells: click to edit, shows input field (same input style as Login), blur/Enter to save
  * Non-editable cells: gray background with same gray tone as Login disabled state
  * Live validation feedback using same green (valid) and red (exceeds max) colors from Login
  * Final grade auto-updates as teacher enters values
- Save button (same primary button style as Login)
- Clear visual distinction between editable and read-only cells
- Mobile: horizontal scroll (same responsive approach as other tables)
- RTL support (same approach as Login)
- Inline validation errors using same red color and spacing as Login

CRITICAL: This grid reuses table, input, and button styles from Login. No new designs. Consistency is essential for grade management accuracy.
```

---

### Stitch Prompt 9: Teacher Modules Index (CRUD List Pattern)
```
Design a reusable list management page using the exact design system from Login.
THIS PAGE MUST USE:
- Same table styling from other pages (Exam Monitor, Results)
- Same button styles (primary for "Add", secondary/danger for Delete)
- Same typography and color palette
- Same input styling for search/filter (same as Login form inputs)
- Same modal styling for add/edit/delete confirmation
- Same spacing and padding system
- Same typography for headings, labels, and data

Include:
- Page heading using Login typography: "My Modules" (or similar)
- "Add [Item]" button (primary button style from Login, prominent position)
- Optional: search/filter bar (same input styling as Login)
- List/table reusing the SAME table structure as other pages:
  * Item name (main content, same font as other tables)
  * Secondary info (e.g., source for modules, student count for groups)
  * Tertiary info (e.g., question count)
  * Action buttons: Edit (secondary style from Login), Delete (danger style from Login)
- Empty state: friendly message with "Get started" button (same button style as primary "Add" button)
- Modal for adding/editing (same modal style used throughout)
- Confirmation dialog for delete (same confirmation modal style as all other destructive actions)
- Mobile-responsive: cards on mobile, table on desktop (same responsive breakpoints as Login)
- RTL support (same approach as Login)
- Hover/focus states using same visual language as Login buttons

CRITICAL: This pattern will be reused for Groups and Questions pages. Design it once, reuse it exactly. No variations.
```

---

### Stitch Prompt 10: Notifications Inbox (List Pattern)
```
Design a clean notification inbox using the exact design system from Login.
THIS PAGE MUST USE:
- Same card design from Login for notification items
- Same typography for titles, messages, and timestamps
- Same primary color for unread indicators
- Same button styles for actions (Mark as read, Delete)
- Same color palette for notification type badges
- Same spacing, padding, and grid system
- Same tab/filter styling as other filtered pages (if used)

Include:
- Page heading using Login typography: "Notifications"
- Filter tabs (if needed): "All", "Account", "Exam Reminder", "Results Available" (same tab style as other filtered pages)
- Notification list (reverse chronological, newest first) using cards:
  * Each notification card uses the SAME card design as Login dashboards:
    - Icon or colored badge (for notification type, use Login color palette)
    - Notification title/subject (same typography as Login)
    - Brief message/preview text (same body text size as Login)
    - Timestamp (relative, e.g., "2 hours ago", same gray color as other timestamps)
    - Read/unread indicator (bold/bright for unread using primary color from Login, normal for read)
    - Actions buttons: Mark as read (secondary style from Login), Delete (danger style from Login)
- "Mark all as read" button (same primary button style as Login)
- Empty state: "No notifications" message (same empty state styling as other pages)
- Unread notifications stand out subtly using the primary color from Login (not harsh or jarring)
- Mobile-responsive: full-width cards on mobile (same responsive approach as other list pages)
- RTL support (same approach as Login)
- Smooth interactions (hover states using same hover effects as Login)

CRITICAL: This card pattern will be reused. Design one notification card, reuse it consistently. Same spacing, same typography, same colors throughout.
```

---

### Notes for Stitch Designs

1. **Design System Freedom**: Let Stitch choose the color palette, typography, spacing, and component styles. We'll extract and apply them to the remaining 18 pages.

2. **Arabic/RTL Considerations**:
   - All text should be positioned correctly for RTL reading direction
   - Buttons and icons on the left (in RTL context) should mirror desktop defaults
   - Numbers and dates should be formatted appropriately
   - Test with actual Arabic placeholder text

3. **Responsive Design**:
   - Design for mobile (375px), tablet (768px), and desktop (1920px)
   - Ensure touch targets are at least 44px for mobile accessibility
   - Test that content reflows naturally

4. **Accessibility**:
   - Sufficient color contrast (WCAG AA minimum 4.5:1 for text)
   - Clear focus states (outline, highlight, or underline)
   - Meaningful use of color (not just for differentiation)
   - Support for keyboard navigation

5. **Exam-Specific Considerations**:
   - The exam session page (Prompt 4) is the most critical and complex — allocate time for iterations
   - The monitor page (Prompt 5) requires real-time urgency in design
   - Forms (Prompt 6) should minimize errors and friction

6. **Design Handoff**:
   - Export the design system (colors, typography, spacing rules, component library) as a reference
   - Document any design decisions or deviations from standards
   - Provide component specs for developers to match exactly

