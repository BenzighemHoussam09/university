<?php

namespace Tests\Feature\Scoping;

use App\Livewire\Teacher\Modules\Index;
use App\Models\Group;
use App\Models\Module;
use App\Models\Question;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * SC-008 — Zero cross-teacher leakage.
 *
 * Sets up Teacher A with their own modules, groups, and students, then
 * authenticates as Teacher B and verifies that none of Teacher A's data
 * is visible.
 */
class CrossTeacherIsolationTest extends TestCase
{
    use RefreshDatabase;

    private Teacher $teacherA;

    private Teacher $teacherB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacherA = Teacher::factory()->create();
        $this->teacherB = Teacher::factory()->create();

        // Give Teacher A some data
        $moduleA = Module::factory()->create(['teacher_id' => $this->teacherA->id]);
        $groupA = Group::factory()->create([
            'teacher_id' => $this->teacherA->id,
            'module_id' => $moduleA->id,
        ]);
        Student::factory()->create(['teacher_id' => $this->teacherA->id]);
        Question::factory()->withChoices()->create([
            'teacher_id' => $this->teacherA->id,
            'module_id' => $moduleA->id,
        ]);
    }

    // ------------------------------------------------------------------
    // Module isolation
    // ------------------------------------------------------------------

    public function test_teacher_b_cannot_see_teacher_a_modules_via_eloquent(): void
    {
        $this->actingAs($this->teacherB, 'teacher');

        $modules = Module::all();

        $this->assertEmpty($modules);
    }

    public function test_teacher_b_cannot_find_teacher_a_module_by_id(): void
    {
        $moduleA = Module::withoutGlobalScopes()
            ->where('teacher_id', $this->teacherA->id)
            ->first();

        $this->actingAs($this->teacherB, 'teacher');

        $found = Module::find($moduleA->id);

        $this->assertNull($found);
    }

    public function test_teacher_b_cannot_view_teacher_a_modules_page(): void
    {
        $this->actingAs($this->teacherB, 'teacher');

        // Teacher B has no modules — global scope ensures an empty list is rendered.
        $this->assertSame(0, Module::count());

        Livewire::test(Index::class)
            ->assertOk();
    }

    // ------------------------------------------------------------------
    // Group isolation
    // ------------------------------------------------------------------

    public function test_teacher_b_cannot_see_teacher_a_groups_via_eloquent(): void
    {
        $this->actingAs($this->teacherB, 'teacher');

        $groups = Group::all();

        $this->assertEmpty($groups);
    }

    public function test_teacher_b_cannot_view_teacher_a_group_show_page(): void
    {
        $groupA = Group::withoutGlobalScopes()
            ->where('teacher_id', $this->teacherA->id)
            ->first();

        $this->actingAs($this->teacherB, 'teacher');

        // The BelongsToTeacher scope makes Teacher A's group unfindable for Teacher B,
        // so Laravel model binding returns 404 before any policy check.
        // 404 is the correct secure response — it does not reveal the resource exists.
        $response = $this->get(route('teacher.groups.show', $groupA->id));

        $response->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Student isolation
    // ------------------------------------------------------------------

    public function test_teacher_b_cannot_see_teacher_a_students_via_eloquent(): void
    {
        $this->actingAs($this->teacherB, 'teacher');

        $students = Student::all();

        $this->assertEmpty($students);
    }

    public function test_teacher_b_cannot_view_teacher_a_student_show_page(): void
    {
        $studentA = Student::withoutGlobalScopes()
            ->where('teacher_id', $this->teacherA->id)
            ->first();

        $this->actingAs($this->teacherB, 'teacher');

        // The BelongsToTeacher scope makes Teacher A's student unfindable for Teacher B.
        // Laravel model binding returns 404 — the resource effectively does not exist
        // from Teacher B's perspective, which is the correct secure behavior.
        $response = $this->get(route('teacher.students.show', $studentA->id));

        $response->assertNotFound();
    }

    // ------------------------------------------------------------------
    // Student (guard: student) isolation
    // ------------------------------------------------------------------

    public function test_student_can_only_see_own_teacher_data_via_scope(): void
    {
        $moduleA = Module::withoutGlobalScopes()
            ->where('teacher_id', $this->teacherA->id)
            ->first();

        $studentA = Student::withoutGlobalScopes()
            ->where('teacher_id', $this->teacherA->id)
            ->first();

        $moduleB = Module::factory()->create(['teacher_id' => $this->teacherB->id]);

        $this->actingAs($studentA, 'student');

        // Student A can see Teacher A's module (same teacher_id)
        $visible = Module::all();
        $this->assertTrue($visible->contains($moduleA));

        // Student A cannot see Teacher B's module
        $this->assertFalse($visible->contains($moduleB));
    }

    // ------------------------------------------------------------------
    // Question isolation
    // ------------------------------------------------------------------

    public function test_teacher_b_cannot_see_teacher_a_questions_via_eloquent(): void
    {
        $this->actingAs($this->teacherB, 'teacher');

        $questions = Question::all();

        $this->assertEmpty($questions);
    }

    public function test_teacher_b_cannot_find_teacher_a_question_by_id(): void
    {
        $questionA = Question::withoutGlobalScopes()
            ->where('teacher_id', $this->teacherA->id)
            ->first();

        $this->actingAs($this->teacherB, 'teacher');

        $found = Question::find($questionA->id);

        $this->assertNull($found);
    }

    public function test_teacher_b_cannot_edit_teacher_a_question_via_route(): void
    {
        $questionA = Question::withoutGlobalScopes()
            ->where('teacher_id', $this->teacherA->id)
            ->first();

        $this->actingAs($this->teacherB, 'teacher');

        // The BelongsToTeacher scope makes Teacher A's question unfindable for Teacher B.
        // Route model binding returns 404.
        $response = $this->get(route('teacher.questions.edit', $questionA->id));

        $response->assertNotFound();
    }
}
