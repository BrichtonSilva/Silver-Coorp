<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_users_index(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/users');
        $response->assertStatus(200);
        $response->assertViewIs('access.users.index');
    }

    public function test_non_admin_cannot_access_users_index(): void
    {
        $charge = User::factory()->charge()->create();
        $consultant = User::factory()->consultant()->create();

        $this->actingAs($charge)->get('/users')->assertForbidden();
        $this->actingAs($consultant)->get('/users')->assertForbidden();
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Nouvel Utilisateur',
            'login' => 'nouvel.user',
            'email' => 'nouvel@eventia.fr',
            'role' => User::ROLE_CHARGE,
            'password' => 'password123',
            'is_active' => true,
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'login' => 'nouvel.user',
            'name' => 'Nouvel Utilisateur',
            'role' => User::ROLE_CHARGE,
        ]);
    }

    public function test_admin_can_edit_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->consultant()->create();

        $response = $this->actingAs($admin)->put("/users/{$user->id}", [
            'name' => 'Nom Modifié',
            'login' => $user->login,
            'email' => $user->email,
            'role' => User::ROLE_CHARGE,
            'is_active' => true,
        ]);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nom Modifié',
            'role' => User::ROLE_CHARGE,
        ]);
    }

    public function test_admin_cannot_self_delete(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete("/users/{$admin->id}");
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_login_must_be_unique_case_insensitive(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['login' => 'ExistingUser']);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Test',
            'login' => 'existinguser',
            'email' => 'test@test.fr',
            'role' => User::ROLE_CONSULTANT,
            'password' => 'password123',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors();
    }

    public function test_password_is_required_on_create(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Test',
            'login' => 'testnopass',
            'email' => null,
            'role' => User::ROLE_CONSULTANT,
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_password_min_8_chars(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Test',
            'login' => 'testshort',
            'email' => null,
            'role' => User::ROLE_CONSULTANT,
            'password' => '123',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
