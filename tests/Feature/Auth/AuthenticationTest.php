<?php

namespace Tests\Feature\Auth;

use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/teacher/login');

        $response
            ->assertOk()
            ->assertSeeVolt('teacher.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $teacher = Teacher::factory()->create();

        $component = Volt::test('teacher.auth.login')
            ->set('form.email', $teacher->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('teacher.dashboard', absolute: false));

        $this->assertAuthenticated('teacher');
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $teacher = Teacher::factory()->create();

        $component = Volt::test('teacher.auth.login')
            ->set('form.email', $teacher->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest('teacher');
    }

    public function test_users_can_logout(): void
    {
        $teacher = Teacher::factory()->create();

        $this->actingAs($teacher, 'teacher');

        $response = $this->post(route('teacher.logout'));

        $response->assertRedirect(route('teacher.login'));

        $this->assertGuest('teacher');
    }
}
