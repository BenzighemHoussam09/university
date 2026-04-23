<?php

namespace Tests\Feature\Teacher;

use App\Livewire\Student\Profile;
use App\Livewire\Teacher\Dashboard;
use App\Livewire\Teacher\Groups\Index as GroupsIndex;
use App\Livewire\Teacher\Groups\Show as GroupsShow;
use App\Livewire\Teacher\Modules\Index as ModulesIndex;
use App\Models\Group;
use App\Models\Module;
use App\Models\ModuleCatalog;
use App\Models\Student;
use App\Models\Teacher;
use App\Notifications\StudentAccountCreated;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * US1 acceptance scenarios 1–4.
 */
class TeacherWorkspaceSetupTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Scenario 1: Teacher adds a module from catalog — visible only to them
    // ------------------------------------------------------------------

    public function test_teacher_adds_module_from_catalog_and_it_appears_only_in_their_list(): void
    {
        $teacherA = Teacher::factory()->create();
        $teacherB = Teacher::factory()->create();

        $catalog = ModuleCatalog::factory()->create(['name' => 'Algorithms']);

        $this->actingAs($teacherA, 'teacher');

        Livewire::test(ModulesIndex::class)
            ->call('addFromCatalog', $catalog->id);

        // Teacher A has the module
        $this->actingAs($teacherA, 'teacher');
        $this->assertDatabaseHas('modules', [
            'teacher_id' => $teacherA->id,
            'name' => 'Algorithms',
            'created_from_catalog_id' => $catalog->id,
        ]);

        // Teacher B sees no modules
        $this->actingAs($teacherB, 'teacher');
        $this->assertSame(0, Module::count());
    }

    // ------------------------------------------------------------------
    // Scenario 2: Teacher with a module creates a group
    // ------------------------------------------------------------------

    public function test_teacher_creates_group_linked_to_module(): void
    {
        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(GroupsIndex::class)
            ->set('name', 'G1')
            ->set('moduleId', $module->id)
            ->set('level', 'M2')
            ->call('create');

        $this->assertDatabaseHas('groups', [
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => 'M2',
            'name' => 'G1',
        ]);
    }

    // ------------------------------------------------------------------
    // Scenario 3: Teacher adds a student to a group — student gets credentials
    // ------------------------------------------------------------------

    public function test_teacher_adds_student_to_group_and_notification_queued(): void
    {
        Notification::fake();

        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
        ]);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(GroupsShow::class, ['group' => $group])
            ->set('newStudentName', 'Alice Dupont')
            ->set('newStudentEmail', 'alice@example.com')
            ->call('addStudent');

        // Student created and scoped to this teacher
        $student = Student::withoutGlobalScopes()
            ->where('email', 'alice@example.com')
            ->first();

        $this->assertNotNull($student);
        $this->assertSame($teacher->id, $student->teacher_id);

        // Student is in the group
        $this->assertDatabaseHas('group_student', [
            'group_id' => $group->id,
            'student_id' => $student->id,
        ]);

        // Notification queued
        Notification::assertSentTo($student, StudentAccountCreated::class);
    }

    // ------------------------------------------------------------------
    // Scenario 4: Student signs in and sees empty dashboard
    // ------------------------------------------------------------------

    public function test_student_logs_in_and_sees_empty_dashboard(): void
    {
        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($student, 'student');

        $response = $this->get(route('student.dashboard'));

        $response->assertOk();
        $response->assertSeeLivewire(\App\Livewire\Student\Dashboard::class);
    }

    // ------------------------------------------------------------------
    // Dashboard counts
    // ------------------------------------------------------------------

    public function test_teacher_dashboard_shows_correct_counts(): void
    {
        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        Group::factory()->count(3)->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
        ]);
        Student::factory()->count(5)->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher, 'teacher');

        // studentCount and groupCount are view variables, not public properties.
        // Verify via assertViewHas which checks the data passed to the blade template.
        Livewire::test(Dashboard::class)
            ->assertViewHas('studentCount', 5)
            ->assertViewHas('groupCount', 3);
    }

    // ------------------------------------------------------------------
    // Assign existing student
    // ------------------------------------------------------------------

    public function test_teacher_assigns_existing_student_to_group(): void
    {
        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create(['teacher_id' => $teacher->id, 'module_id' => $module->id]);
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(GroupsShow::class, ['group' => $group])
            ->call('assignExisting', $student->id);

        $this->assertDatabaseHas('group_student', [
            'group_id' => $group->id,
            'student_id' => $student->id,
        ]);
    }

    // ------------------------------------------------------------------
    // Remove student from group
    // ------------------------------------------------------------------

    public function test_teacher_removes_student_from_group(): void
    {
        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create(['teacher_id' => $teacher->id, 'module_id' => $module->id]);
        $student = Student::factory()->create(['teacher_id' => $teacher->id]);
        $group->students()->attach($student->id);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(GroupsShow::class, ['group' => $group])
            ->call('removeFromGroup', $student->id);

        $this->assertDatabaseMissing('group_student', [
            'group_id' => $group->id,
            'student_id' => $student->id,
        ]);
    }

    // ------------------------------------------------------------------
    // Student profile
    // ------------------------------------------------------------------

    public function test_student_can_update_their_name(): void
    {
        $teacher = Teacher::factory()->create();
        $student = Student::factory()->create(['teacher_id' => $teacher->id, 'name' => 'Old Name']);

        $this->actingAs($student, 'student');

        Livewire::test(Profile::class)
            ->set('name', 'New Name')
            ->call('updateProfile');

        $this->assertDatabaseHas('students', ['id' => $student->id, 'name' => 'New Name']);
    }

    // ------------------------------------------------------------------
    // Duplicate email rejection
    // ------------------------------------------------------------------

    public function test_adding_student_with_duplicate_email_fails(): void
    {
        Notification::fake();

        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);
        $group = Group::factory()->create(['teacher_id' => $teacher->id, 'module_id' => $module->id]);

        Student::factory()->create([
            'teacher_id' => $teacher->id,
            'email' => 'dup@example.com',
        ]);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(GroupsShow::class, ['group' => $group])
            ->set('newStudentName', 'Bob')
            ->set('newStudentEmail', 'dup@example.com')
            ->call('addStudent')
            ->assertHasErrors('newStudentEmail');
    }
}
