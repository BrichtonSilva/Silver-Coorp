<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_view_clients_index(): void
    {
        $admin = User::factory()->admin()->create();
        $charge = User::factory()->charge()->create();
        $consultant = User::factory()->consultant()->create();

        $this->actingAs($admin)->get('/clients')->assertStatus(200);
        $this->actingAs($charge)->get('/clients')->assertStatus(200);
        $this->actingAs($consultant)->get('/clients')->assertStatus(200);
    }

    public function test_guest_cannot_access_clients(): void
    {
        $this->get('/clients')->assertRedirect('/login');
    }

    public function test_charge_can_create_client(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/clients', [
            'name' => 'Nouveau Client SARL',
            'type' => Client::TYPE_ENTREPRISE,
            'email' => 'contact@nouveausarl.fr',
            'phone' => '01 23 45 67 89',
            'address' => '123 rue Test, 75000 Paris',
        ]);

        $response->assertRedirect('/clients');
        $this->assertDatabaseHas('clients', [
            'name' => 'Nouveau Client SARL',
            'type' => Client::TYPE_ENTREPRISE,
            'created_by' => $charge->id,
        ]);
    }

    public function test_admin_can_create_client(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/clients', [
            'name' => 'Admin Client',
            'type' => Client::TYPE_PARTICULIER,
            'email' => null,
            'phone' => null,
            'address' => null,
        ]);

        $response->assertRedirect('/clients');
        $this->assertDatabaseHas('clients', ['name' => 'Admin Client']);
    }

    public function test_consultant_cannot_create_client(): void
    {
        $consultant = User::factory()->consultant()->create();

        $response = $this->actingAs($consultant)->post('/clients', [
            'name' => 'Test Consultant',
            'type' => Client::TYPE_PARTICULIER,
        ]);

        // Consultant should be forbidden from creating (RG17)
        $response->assertForbidden();
        $this->assertDatabaseMissing('clients', ['name' => 'Test Consultant']);
    }

    public function test_charge_can_edit_client(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create(['created_by' => $charge->id]);

        $response = $this->actingAs($charge)->put("/clients/{$client->id}", [
            'name' => 'Client Modifié',
            'type' => Client::TYPE_ENTREPRISE,
            'email' => 'modified@test.fr',
            'phone' => '06 11 22 33 44',
            'address' => 'New address',
        ]);

        $response->assertRedirect('/clients');
        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'Client Modifié',
        ]);
    }

    public function test_charge_can_delete_client(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create(['created_by' => $charge->id]);

        $response = $this->actingAs($charge)->delete("/clients/{$client->id}");

        $response->assertRedirect('/clients');
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }

    public function test_show_displays_client_details(): void
    {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($admin)->get("/clients/{$client->id}");

        $response->assertStatus(200);
        $response->assertViewIs('clients.show');
        $response->assertViewHas('client');
    }

    public function test_name_is_required(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/clients', [
            'name' => '',
            'type' => Client::TYPE_PARTICULIER,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_type_must_be_valid(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/clients', [
            'name' => 'Test',
            'type' => 'invalid_type',
        ]);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_email_must_be_valid_when_provided(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/clients', [
            'name' => 'Test',
            'type' => Client::TYPE_PARTICULIER,
            'email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_clients_index_filters_by_search(): void
    {
        $admin = User::factory()->admin()->create();
        Client::factory()->create(['name' => 'Alpha Corporation']);
        Client::factory()->create(['name' => 'Beta Industries']);

        $response = $this->actingAs($admin)->get('/clients?search=Alpha');

        $response->assertStatus(200);
        $response->assertSee('Alpha Corporation');
        $response->assertDontSee('Beta Industries');
    }

    public function test_clients_index_filters_by_type(): void
    {
        $admin = User::factory()->admin()->create();
        Client::factory()->particulier()->create(['name' => 'Part Test']);
        Client::factory()->entreprise()->create(['name' => 'Ent Test']);

        $response = $this->actingAs($admin)->get('/clients?type=particulier');

        $response->assertStatus(200);
        $response->assertSee('Part Test');
        $response->assertDontSee('Ent Test');
    }
}
