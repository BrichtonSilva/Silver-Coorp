<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VenueManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_view_venues_index(): void
    {
        $admin = User::factory()->admin()->create();
        $charge = User::factory()->charge()->create();
        $consultant = User::factory()->consultant()->create();

        $this->actingAs($admin)->get('/venues')->assertStatus(200);
        $this->actingAs($charge)->get('/venues')->assertStatus(200);
        $this->actingAs($consultant)->get('/venues')->assertStatus(200);
    }

    public function test_guest_cannot_access_venues(): void
    {
        $this->get('/venues')->assertRedirect('/login');
    }

    public function test_charge_can_create_venue(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/venues', [
            'name' => 'Nouveau Centre de Congrès',
            'address' => '123 rue Test, 75000 Paris',
            'capacity' => 250,
            'type' => Venue::TYPE_EXTERNE,
            'rental_price' => 1500.00,
        ]);

        $response->assertRedirect('/venues');
        $this->assertDatabaseHas('venues', [
            'name' => 'Nouveau Centre de Congrès',
            'capacity' => 250,
            'type' => Venue::TYPE_EXTERNE,
            'rental_price' => 1500.00,
            'created_by' => $charge->id,
        ]);
    }

    public function test_admin_can_create_venue(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/venues', [
            'name' => 'Salle Admin',
            'address' => null,
            'capacity' => 50,
            'type' => Venue::TYPE_INTERNE,
            'rental_price' => 0,
        ]);

        $response->assertRedirect('/venues');
        $this->assertDatabaseHas('venues', ['name' => 'Salle Admin']);
    }

    public function test_consultant_cannot_create_venue(): void
    {
        $consultant = User::factory()->consultant()->create();

        $response = $this->actingAs($consultant)->post('/venues', [
            'name' => 'Test Consultant',
            'capacity' => 100,
            'type' => Venue::TYPE_INTERNE,
            'rental_price' => 100,
        ]);

        // Consultant should be forbidden from creating (RG17)
        $response->assertForbidden();
        $this->assertDatabaseMissing('venues', ['name' => 'Test Consultant']);
    }

    public function test_charge_can_edit_venue(): void
    {
        $charge = User::factory()->charge()->create();
        $venue = Venue::factory()->create(['created_by' => $charge->id]);

        $response = $this->actingAs($charge)->put("/venues/{$venue->id}", [
            'name' => 'Lieu Modifié',
            'address' => 'New address',
            'capacity' => 300,
            'type' => Venue::TYPE_EXTERNE,
            'rental_price' => 2000.50,
        ]);

        $response->assertRedirect('/venues');
        $this->assertDatabaseHas('venues', [
            'id' => $venue->id,
            'name' => 'Lieu Modifié',
        ]);
    }

    public function test_charge_can_delete_venue(): void
    {
        $charge = User::factory()->charge()->create();
        $venue = Venue::factory()->create(['created_by' => $charge->id]);

        $response = $this->actingAs($charge)->delete("/venues/{$venue->id}");

        $response->assertRedirect('/venues');
        $this->assertDatabaseMissing('venues', ['id' => $venue->id]);
    }

    public function test_show_displays_venue_details(): void
    {
        $admin = User::factory()->admin()->create();
        $venue = Venue::factory()->create();

        $response = $this->actingAs($admin)->get("/venues/{$venue->id}");

        $response->assertStatus(200);
        $response->assertViewIs('venues.show');
        $response->assertViewHas('venue');
    }

    public function test_name_is_required(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/venues', [
            'name' => '',
            'capacity' => 100,
            'type' => Venue::TYPE_INTERNE,
            'rental_price' => 100,
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_capacity_must_be_at_least_one(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/venues', [
            'name' => 'Test',
            'capacity' => 0,
            'type' => Venue::TYPE_INTERNE,
            'rental_price' => 100,
        ]);

        $response->assertSessionHasErrors(['capacity']);
    }

    public function test_type_must_be_valid(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/venues', [
            'name' => 'Test',
            'capacity' => 100,
            'type' => 'invalid_type',
            'rental_price' => 100,
        ]);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_rental_price_must_not_be_negative(): void
    {
        $charge = User::factory()->charge()->create();

        $response = $this->actingAs($charge)->post('/venues', [
            'name' => 'Test',
            'capacity' => 100,
            'type' => Venue::TYPE_INTERNE,
            'rental_price' => -10,
        ]);

        $response->assertSessionHasErrors(['rental_price']);
    }

    public function test_venues_index_filters_by_search(): void
    {
        $admin = User::factory()->admin()->create();
        Venue::factory()->create(['name' => 'Salle Alpha']);
        Venue::factory()->create(['name' => 'Salle Beta']);

        $response = $this->actingAs($admin)->get('/venues?search=Alpha');

        $response->assertStatus(200);
        $response->assertSee('Salle Alpha');
        $response->assertDontSee('Salle Beta');
    }

    public function test_venues_index_filters_by_type(): void
    {
        $admin = User::factory()->admin()->create();
        Venue::factory()->interne()->create(['name' => 'Lieu Interne Test']);
        Venue::factory()->externe()->create(['name' => 'Lieu Externe Test']);

        $response = $this->actingAs($admin)->get('/venues?type=interne');

        $response->assertStatus(200);
        $response->assertSee('Lieu Interne Test');
        $response->assertDontSee('Lieu Externe Test');
    }

    public function test_venues_index_is_paginated(): void
    {
        $admin = User::factory()->admin()->create();
        Venue::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get('/venues');

        $response->assertStatus(200);
        // 10 items per page, pagination should appear
        $response->assertSee('pagination');
    }
}
