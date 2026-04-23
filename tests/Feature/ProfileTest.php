<?php

namespace Tests\Feature;

use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $teacher = Teacher::factory()->create();

        $response = $this->actingAs($teacher, 'teacher')->get('/profile');

        $response
            ->assertOk()
            ->assertSeeVolt('profile.update-profile-information-form')
            ->assertSeeVolt('profile.update-password-form')
            ->assertSeeVolt('profile.delete-user-form');
    }

    public function test_profile_information_can_be_updated(): void
    {
        $teacher = Teacher::factory()->create();

        $this->actingAs($teacher, 'teacher');

        $component = Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test Teacher')
            ->set('email', 'test@example.com')
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $teacher->refresh();

        $this->assertSame('Test Teacher', $teacher->name);
        $this->assertSame('test@example.com', $teacher->email);
        $this->assertNull($teacher->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $teacher = Teacher::factory()->create();

        $this->actingAs($teacher, 'teacher');

        $component = Volt::test('profile.update-profile-information-form')
            ->set('name', 'Test Teacher')
            ->set('email', $teacher->email)
            ->call('updateProfileInformation');

        $component
            ->assertHasNoErrors()
            ->assertNoRedirect();

        $this->assertNotNull($teacher->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $teacher = Teacher::factory()->create();

        $this->actingAs($teacher, 'teacher');

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'password')
            ->call('deleteUser');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest('teacher');
        $this->assertNull($teacher->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $teacher = Teacher::factory()->create();

        $this->actingAs($teacher, 'teacher');

        $component = Volt::test('profile.delete-user-form')
            ->set('password', 'wrong-password')
            ->call('deleteUser');

        $component
            ->assertHasErrors('password')
            ->assertNoRedirect();

        $this->assertNotNull($teacher->fresh());
    }
}
