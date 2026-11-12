<?php

namespace Tests\Feature;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProviderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_view_providers_index(): void
    {
        $admin = User::factory()->admin()->create();
        $charge = User::factory()->charge()->create();
        $consultant = User::factory()->consultant()->create();

        $this->actingAs($admin)->get('/providers')->assertStatus(200);
        $this->actingAs($charge)->get('/providers')->assertStatus(200);
        $this->actingAs($consultant)->get('/providers')->assertStatus(200);
    }

    public function test_guest_cannot_access_providers(): void
    {
        $this->get('/providers')->assertRedirect('/login');
    }

    public function test_charge_can_create_provider(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/providers', [
            'name' => 'Traiteur Gourmet',
            'category' => Provider::CATEGORY_TRAITEUR,
            'contact' => 'Jean Dupont',
            'phone' => '01 23 45 67 89',
            'email' => 'contact@traiteurgourmet.fr',
            'indicative_price' => 1500.00,
        ]);

        $response->assertRedirect('/providers');
        $this->assertDatabaseHas('providers', [
            'name' => 'Traiteur Gourmet',
            'category' => Provider::CATEGORY_TRAITEUR,
            'created_by' => $charge->id,
        ]);
    }

    public function test_admin_can_create_provider(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/providers', [
            'name' => 'Sonorisation Pro',
            'category' => Provider::CATEGORY_SONORISATION,
            'contact' => null,
            'phone' => null,
            'email' => null,
            'indicative_price' => null,
        ]);

        $response->assertRedirect('/providers');
        $this->assertDatabaseHas('providers', ['name' => 'Sonorisation Pro']);
    }

    public function test_consultant_cannot_create_provider(): void
    {
        $consultant = User::factory()->consultant()->create();

        $response = $this->actingAs($consultant)->post('/providers', [
            'name' => 'Test Consultant',
            'category' => Provider::CATEGORY_AUTRE,
        ]);

        // Consultant should be forbidden from creating (RG17)
        $response->assertForbidden();
        $this->assertDatabaseMissing('providers', ['name' => 'Test Consultant']);
    }

    public function test_charge_can_edit_provider(): void
    {
        $charge = User::factory()->charge()->create();
        $provider = Provider::factory()->create(['created_by' => $charge->id]);

        $response = $this->actingAs($charge)->put("/providers/{$provider->id}", [
            'name' => 'Prestataire Modifié',
            'category' => Provider::CATEGORY_DECORATION,
            'contact' => 'Marie Martin',
            'phone' => '06 11 22 33 44',
            'email' => 'modified@test.fr',
            'indicative_price' => 2000.50,
        ]);

        $response->assertRedirect('/providers');
        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'name' => 'Prestataire Modifié',
        ]);
    }

    public function test_charge_can_delete_provider(): void
    {
        $charge = User::factory()->charge()->create();
        $provider = Provider::factory()->create(['created_by' => $charge->id]);

        $response = $this->actingAs($charge)->delete("/providers/{$provider->id}");

        $response->assertRedirect('/providers');
        $this->assertDatabaseMissing('providers', ['id' => $provider->id]);
    }

    public function test_show_displays_provider_details(): void
    {
        $admin = User::factory()->admin()->create();
        $provider = Provider::factory()->create();

        $response = $this->actingAs($admin)->get("/providers/{$provider->id}");

        $response->assertStatus(200);
        $response->assertViewIs('providers.show');
        $response->assertViewHas('provider');
    }

    public function test_name_is_required(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/providers', [
            'name' => '',
            'category' => Provider::CATEGORY_TRAITEUR,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_category_must_be_valid(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/providers', [
            'name' => 'Test',
            'category' => 'invalid_category',
        ]);

        $response->assertSessionHasErrors(['category']);
    }

    public function test_email_must_be_valid_when_provided(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/providers', [
            'name' => 'Test',
            'category' => Provider::CATEGORY_TRAITEUR,
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_providers_index_filters_by_search(): void
    {
        $admin = User::factory()->admin()->create();
        Provider::factory()->create(['name' => 'Alpha Traiteur']);
        Provider::factory()->create(['name' => 'Beta Sonorisation']);

        $response = $this->actingAs($admin)->get('/providers?search=Alpha');

        $response->assertStatus(200);
        $response->assertSee('Alpha Traiteur');
        $response->assertDontSee('Beta Sonorisation');
    }

    public function test_providers_index_filters_by_category(): void
    {
        $admin = User::factory()->admin()->create();
        Provider::factory()->category(Provider::CATEGORY_TRAITEUR)->create(['name' => 'Traiteur Test']);
        Provider::factory()->category(Provider::CATEGORY_PHOTOGRAPHE)->create(['name' => 'Photo Test']);

        $response = $this->actingAs($admin)->get('/providers?category=traiteur');

        $response->assertStatus(200);
        $response->assertSee('Traiteur Test');
        $response->assertDontSee('Photo Test');
    }
}
