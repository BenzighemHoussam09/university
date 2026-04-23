<?php

namespace Tests\Feature\Teacher;

use App\Enums\Difficulty;
use App\Enums\Level;
use App\Livewire\Teacher\Questions\Create;
use App\Livewire\Teacher\Questions\Edit;
use App\Livewire\Teacher\Questions\Index;
use App\Models\Module;
use App\Models\Question;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * US2 — Question Bank Management.
 *
 * Covers: CRUD, filtering by module/level/difficulty, and the 4-choices/1-correct
 * invariant enforcement (FR-011).
 */
class QuestionBankTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function makeTeacherWithModule(): array
    {
        $teacher = Teacher::factory()->create();
        $module = Module::factory()->create(['teacher_id' => $teacher->id]);

        return [$teacher, $module];
    }

    private function validChoices(): array
    {
        return ['Choice A', 'Choice B', 'Choice C', 'Choice D'];
    }

    // ------------------------------------------------------------------
    // Index — renders without error
    // ------------------------------------------------------------------

    public function test_questions_index_renders_for_authenticated_teacher(): void
    {
        [$teacher] = $this->makeTeacherWithModule();

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Index::class)->assertOk();
    }

    // ------------------------------------------------------------------
    // Create — happy path
    // ------------------------------------------------------------------

    public function test_teacher_can_create_question_with_four_choices(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Create::class)
            ->set('text', 'What is 2 + 2?')
            ->set('moduleId', $module->id)
            ->set('level', Level::L1->value)
            ->set('difficulty', Difficulty::Easy->value)
            ->set('choices', ['1', '2', '4', '8'])
            ->set('correctIndex', 2)
            ->call('save');

        $question = Question::withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->where('text', 'What is 2 + 2?')
            ->first();

        $this->assertNotNull($question);
        $this->assertSame($module->id, $question->module_id);
        $this->assertSame(Level::L1, $question->level);
        $this->assertSame(Difficulty::Easy, $question->difficulty);

        // Exactly 4 choices stored
        $this->assertSame(4, $question->choices()->count());

        // Exactly 1 correct
        $this->assertSame(1, $question->choices()->where('is_correct', true)->count());

        // The correct choice is index 2 (position 3, text '4')
        $correct = $question->choices()->where('is_correct', true)->first();
        $this->assertSame('4', $correct->text);
    }

    // ------------------------------------------------------------------
    // Create — teacher_id auto-scoped
    // ------------------------------------------------------------------

    public function test_created_question_is_scoped_to_authenticated_teacher(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Create::class)
            ->set('text', 'Scoped question?')
            ->set('moduleId', $module->id)
            ->set('level', Level::M1->value)
            ->set('difficulty', Difficulty::Hard->value)
            ->set('choices', $this->validChoices())
            ->set('correctIndex', 0)
            ->call('save');

        $this->assertDatabaseHas('questions', [
            'teacher_id' => $teacher->id,
            'text' => 'Scoped question?',
        ]);
    }

    // ------------------------------------------------------------------
    // Create — 4/1 invariant: rejects fewer than 4 choices
    // ------------------------------------------------------------------

    public function test_create_rejects_fewer_than_four_choices(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Create::class)
            ->set('text', 'Question?')
            ->set('moduleId', $module->id)
            ->set('level', Level::L2->value)
            ->set('difficulty', Difficulty::Medium->value)
            ->set('choices', ['Only one choice', '', '', ''])  // 3 empty
            ->set('correctIndex', 0)
            ->call('save')
            ->assertHasErrors(['choices.1', 'choices.2', 'choices.3']);

        $this->assertDatabaseMissing('questions', ['text' => 'Question?']);
    }

    // ------------------------------------------------------------------
    // Create — rejects missing question text
    // ------------------------------------------------------------------

    public function test_create_rejects_missing_question_text(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Create::class)
            ->set('text', '')
            ->set('moduleId', $module->id)
            ->set('level', Level::L1->value)
            ->set('difficulty', Difficulty::Easy->value)
            ->set('choices', $this->validChoices())
            ->set('correctIndex', 0)
            ->call('save')
            ->assertHasErrors(['text']);
    }

    // ------------------------------------------------------------------
    // Create — rejects missing module
    // ------------------------------------------------------------------

    public function test_create_rejects_missing_module(): void
    {
        [$teacher] = $this->makeTeacherWithModule();

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Create::class)
            ->set('text', 'Question?')
            ->set('moduleId', null)
            ->set('level', Level::L1->value)
            ->set('difficulty', Difficulty::Easy->value)
            ->set('choices', $this->validChoices())
            ->set('correctIndex', 0)
            ->call('save')
            ->assertHasErrors(['moduleId']);
    }

    // ------------------------------------------------------------------
    // Edit — loads existing data
    // ------------------------------------------------------------------

    public function test_edit_loads_existing_question_data(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $question = Question::factory()
            ->withChoices()
            ->create([
                'teacher_id' => $teacher->id,
                'module_id' => $module->id,
                'level' => Level::M2,
                'difficulty' => Difficulty::Hard,
                'text' => 'Original text?',
            ]);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Edit::class, ['question' => $question])
            ->assertSet('text', 'Original text?')
            ->assertSet('moduleId', $module->id)
            ->assertSet('level', Level::M2->value)
            ->assertSet('difficulty', Difficulty::Hard->value);
    }

    // ------------------------------------------------------------------
    // Edit — happy path update
    // ------------------------------------------------------------------

    public function test_teacher_can_edit_question(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $question = Question::factory()
            ->withChoices()
            ->create([
                'teacher_id' => $teacher->id,
                'module_id' => $module->id,
                'text' => 'Old text?',
                'level' => Level::L1,
                'difficulty' => Difficulty::Easy,
            ]);

        $this->actingAs($teacher, 'teacher');

        $newChoices = ['New A', 'New B', 'New C', 'New D'];

        Livewire::test(Edit::class, ['question' => $question])
            ->set('text', 'Updated text?')
            ->set('choices', $newChoices)
            ->set('correctIndex', 3)
            ->call('save');

        $this->assertDatabaseHas('questions', [
            'id' => $question->id,
            'text' => 'Updated text?',
        ]);

        $correct = $question->fresh()->choices()->where('is_correct', true)->first();
        $this->assertSame('New D', $correct->text);
    }

    // ------------------------------------------------------------------
    // Edit — rejects saving with empty choice text
    // ------------------------------------------------------------------

    public function test_edit_rejects_empty_choice_text(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $question = Question::factory()
            ->withChoices()
            ->create(['teacher_id' => $teacher->id, 'module_id' => $module->id]);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Edit::class, ['question' => $question])
            ->set('text', 'Valid text?')
            ->set('choices', ['A', '', 'C', 'D'])  // index 1 is empty
            ->set('correctIndex', 0)
            ->call('save')
            ->assertHasErrors(['choices.1']);
    }

    // ------------------------------------------------------------------
    // Delete
    // ------------------------------------------------------------------

    public function test_teacher_can_delete_question(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $question = Question::factory()
            ->withChoices()
            ->create(['teacher_id' => $teacher->id, 'module_id' => $module->id]);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Index::class)
            ->call('delete', $question->id);

        $this->assertDatabaseMissing('questions', ['id' => $question->id]);
        // Choices cascade-deleted
        $this->assertDatabaseMissing('question_choices', ['question_id' => $question->id]);
    }

    // ------------------------------------------------------------------
    // Filtering — by module
    // ------------------------------------------------------------------

    public function test_index_filters_by_module(): void
    {
        [$teacher, $moduleA] = $this->makeTeacherWithModule();
        $moduleB = Module::factory()->create(['teacher_id' => $teacher->id]);

        $qA = Question::factory()->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $moduleA->id,
        ]);
        $qB = Question::factory()->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $moduleB->id,
        ]);

        $this->actingAs($teacher, 'teacher');

        $component = Livewire::test(Index::class)
            ->set('moduleId', $moduleA->id);

        $ids = $component->viewData('questions')->pluck('id');

        $this->assertTrue($ids->contains($qA->id));
        $this->assertFalse($ids->contains($qB->id));
    }

    // ------------------------------------------------------------------
    // Filtering — by level
    // ------------------------------------------------------------------

    public function test_index_filters_by_level(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $qL1 = Question::factory()->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::L1,
        ]);
        $qM2 = Question::factory()->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M2,
        ]);

        $this->actingAs($teacher, 'teacher');

        $component = Livewire::test(Index::class)
            ->set('level', Level::L1->value);

        $ids = $component->viewData('questions')->pluck('id');

        $this->assertTrue($ids->contains($qL1->id));
        $this->assertFalse($ids->contains($qM2->id));
    }

    // ------------------------------------------------------------------
    // Filtering — by difficulty
    // ------------------------------------------------------------------

    public function test_index_filters_by_difficulty(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $easy = Question::factory()->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'difficulty' => Difficulty::Easy,
        ]);
        $hard = Question::factory()->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'difficulty' => Difficulty::Hard,
        ]);

        $this->actingAs($teacher, 'teacher');

        $component = Livewire::test(Index::class)
            ->set('difficulty', Difficulty::Easy->value);

        $ids = $component->viewData('questions')->pluck('id');

        $this->assertTrue($ids->contains($easy->id));
        $this->assertFalse($ids->contains($hard->id));
    }

    // ------------------------------------------------------------------
    // Filtering — combined filters
    // ------------------------------------------------------------------

    public function test_index_filters_by_module_level_and_difficulty_combined(): void
    {
        [$teacher, $module] = $this->makeTeacherWithModule();

        $target = Question::factory()->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::M1,
            'difficulty' => Difficulty::Medium,
        ]);
        $other = Question::factory()->withChoices()->create([
            'teacher_id' => $teacher->id,
            'module_id' => $module->id,
            'level' => Level::L3,
            'difficulty' => Difficulty::Hard,
        ]);

        $this->actingAs($teacher, 'teacher');

        $component = Livewire::test(Index::class)
            ->set('moduleId', $module->id)
            ->set('level', Level::M1->value)
            ->set('difficulty', Difficulty::Medium->value);

        $ids = $component->viewData('questions')->pluck('id');

        $this->assertTrue($ids->contains($target->id));
        $this->assertFalse($ids->contains($other->id));
    }
}
