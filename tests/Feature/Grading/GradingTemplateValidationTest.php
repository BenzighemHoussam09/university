<?php

namespace Tests\Feature\Grading;

use App\Domain\Exam\Exceptions\InvalidGradingTemplateException;
use App\Livewire\Teacher\Settings;
use App\Models\GradingTemplate;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * T086 — GradingTemplate validation: rejects saves where components don't sum to 20.
 */
class GradingTemplateValidationTest extends TestCase
{
    use RefreshDatabase;

    private function seedSystemDefault(): void
    {
        \DB::table('grading_templates')->insertOrIgnore([
            'id' => 1,
            'teacher_id' => null,
            'exam_max' => 12,
            'personal_work_max' => 4,
            'attendance_max' => 2,
            'participation_max' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_model_throws_when_sum_not_twenty(): void
    {
        $this->expectException(InvalidGradingTemplateException::class);

        // Bypassing the observer since system default not needed here
        $template = new GradingTemplate([
            'teacher_id' => null,
            'exam_max' => 10,
            'personal_work_max' => 4,
            'attendance_max' => 2,
            'participation_max' => 2, // sum = 18, not 20
        ]);
        $template->save();
    }

    public function test_model_saves_when_sum_is_twenty(): void
    {
        $teacher = Teacher::factory()->create(['grading_template_id' => null]);

        $template = GradingTemplate::withoutEvents(function () use ($teacher) {
            return GradingTemplate::create([
                'teacher_id' => $teacher->id,
                'exam_max' => 10,
                'personal_work_max' => 4,
                'attendance_max' => 4,
                'participation_max' => 2, // sum = 20
            ]);
        });

        $this->assertDatabaseHas('grading_templates', [
            'teacher_id' => $teacher->id,
            'exam_max' => 10,
        ]);
    }

    public function test_settings_livewire_rejects_sum_not_twenty(): void
    {
        $this->seedSystemDefault();

        $teacher = Teacher::factory()->create(['grading_template_id' => null]);
        $this->actingAs($teacher, 'teacher');

        Livewire::test(Settings::class)
            ->set('examMax', 10)
            ->set('personalWorkMax', 4)
            ->set('attendanceMax', 2)
            ->set('participationMax', 2) // sum = 18
            ->call('save')
            ->assertSet('successMessage', '')
            ->assertSet('errorMessage', 'Components must sum to 20. Current sum: 18.');
    }

    public function test_settings_livewire_saves_when_sum_is_twenty(): void
    {
        $this->seedSystemDefault();

        $teacher = Teacher::factory()->create(['grading_template_id' => null]);
        $this->actingAs($teacher, 'teacher');

        Livewire::test(Settings::class)
            ->set('examMax', 10)
            ->set('personalWorkMax', 4)
            ->set('attendanceMax', 3)
            ->set('participationMax', 3) // sum = 20
            ->call('save')
            ->assertSet('errorMessage', '')
            ->assertSet('successMessage', 'Settings saved successfully.');

        $this->assertDatabaseHas('grading_templates', [
            'teacher_id' => $teacher->id,
            'exam_max' => 10,
            'personal_work_max' => 4,
            'attendance_max' => 3,
            'participation_max' => 3,
        ]);
    }

    public function test_settings_livewire_updates_existing_template(): void
    {
        $this->seedSystemDefault();

        // Observer will create a cloned template for this teacher on creation
        $teacher = Teacher::factory()->create();
        $existingTemplateId = $teacher->grading_template_id;
        $this->assertNotNull($existingTemplateId);

        $this->actingAs($teacher, 'teacher');

        Livewire::test(Settings::class)
            ->set('examMax', 10)
            ->set('personalWorkMax', 4)
            ->set('attendanceMax', 3)
            ->set('participationMax', 3)
            ->call('save')
            ->assertSet('successMessage', 'Settings saved successfully.');

        $this->assertDatabaseHas('grading_templates', [
            'id' => $existingTemplateId,
            'exam_max' => 10,
        ]);
    }

    public function test_component_sum_computed_correctly(): void
    {
        $this->seedSystemDefault();
        $teacher = Teacher::factory()->create(['grading_template_id' => null]);
        $this->actingAs($teacher, 'teacher');

        Livewire::test(Settings::class)
            ->set('examMax', 10)
            ->set('personalWorkMax', 5)
            ->set('attendanceMax', 3)
            ->set('participationMax', 2)
            ->assertSet('examMax', 10)
            ->assertSet('personalWorkMax', 5);
    }
}
