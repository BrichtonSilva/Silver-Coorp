<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible_for_guests(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('access.login');
    }

    public function test_authenticated_user_is_redirected_from_login(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->get('/login');
        $response->assertRedirect('/dashboard');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->admin()->create([
            'login' => 'testadmin',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'login' => 'testadmin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_is_case_insensitive(): void
    {
        $user = User::factory()->admin()->create([
            'login' => 'TestAdmin',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'login' => 'testadmin',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_invalid_password(): void
    {
        $user = User::factory()->admin()->create([
            'login' => 'testadmin',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'login' => 'testadmin',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->inactive()->create([
            'login' => 'inactiveuser',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'login' => 'inactiveuser',
            'password' => 'password123',
        ]);

        $response->assertSessionHas('error');
        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->admin()->create();

        $response = $this->actingAs($user)->post('/logout');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_requires_login_and_password(): void
    {
        $response = $this->post('/login', []);
        $response->assertSessionHasErrors(['login', 'password']);
    }

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_inactive_authenticated_user_is_kicked_out(): void
    {
        $user = User::factory()->inactive()->create();

        $response = $this->actingAs($user)->get('/dashboard');
        $response->assertRedirect('/login');
        $this->assertGuest();
    }
}
