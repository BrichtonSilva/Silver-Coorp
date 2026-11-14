<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/account')->assertRedirect('/login');
        $this->put('/account/password', [])->assertRedirect('/login');
    }

    public function test_every_role_can_open_the_account_page(): void
    {
        foreach ([User::factory()->admin()->create(), User::factory()->charge()->create(), User::factory()->consultant()->create()] as $user) {
            $this->actingAs($user)->get('/account')
                ->assertOk()
                ->assertViewIs('access.account');
        }
    }

    public function test_user_can_change_own_password(): void
    {
        $user = User::factory()->admin()->create(['password' => 'ancien-mdp-123']);

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'ancien-mdp-123',
            'password' => 'nouveau-mdp-456',
            'password_confirmation' => 'nouveau-mdp-456',
        ])->assertRedirect('/account')->assertSessionHas('success');

        $this->assertTrue(Hash::check('nouveau-mdp-456', $user->fresh()->password));
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $user = User::factory()->consultant()->create(['password' => 'ancien-mdp-123']);

        $this->actingAs($user)->from('/account')->put('/account/password', [
            'current_password' => 'mauvais',
            'password' => 'nouveau-mdp-456',
            'password_confirmation' => 'nouveau-mdp-456',
        ])->assertRedirect('/account')->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('ancien-mdp-123', $user->fresh()->password));
    }

    public function test_new_password_must_be_confirmed_long_enough_and_different(): void
    {
        $user = User::factory()->charge()->create(['password' => 'ancien-mdp-123']);

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'ancien-mdp-123',
            'password' => 'nouveau-mdp-456',
            'password_confirmation' => 'autre-chose',
        ])->assertSessionHasErrors('password');

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'ancien-mdp-123',
            'password' => 'court',
            'password_confirmation' => 'court',
        ])->assertSessionHasErrors('password');

        $this->actingAs($user)->put('/account/password', [
            'current_password' => 'ancien-mdp-123',
            'password' => 'ancien-mdp-123',
            'password_confirmation' => 'ancien-mdp-123',
        ])->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('ancien-mdp-123', $user->fresh()->password));
    }
}
