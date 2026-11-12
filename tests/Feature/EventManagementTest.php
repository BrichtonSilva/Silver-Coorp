<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Event;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_any_authenticated_user_can_view_events_index(): void
    {
        $admin = User::factory()->admin()->create();
        $charge = User::factory()->charge()->create();
        $consultant = User::factory()->consultant()->create();

        $this->actingAs($admin)->get('/events')->assertStatus(200);
        $this->actingAs($charge)->get('/events')->assertStatus(200);
        $this->actingAs($consultant)->get('/events')->assertStatus(200);
    }

    public function test_guest_cannot_access_events(): void
    {
        $this->get('/events')->assertRedirect('/login');
    }

    public function test_charge_can_create_event(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 100]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_SEMINAIRE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 5000.00,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertRedirect('/events');
        $this->assertDatabaseHas('events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_SEMINAIRE,
            'venue_id' => $venue->id,
            'status' => Event::STATUS_BROUILLON,
            'charged_user_id' => $charge->id,
        ]);
    }

    public function test_admin_can_create_event(): void
    {
        $admin = User::factory()->admin()->create();
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 200]);

        $response = $this->actingAs($admin)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_MARIAGE,
            'date' => now()->addMonth()->toDateString(),
            'start_time' => '14:00',
            'end_time' => '23:00',
            'venue_id' => $venue->id,
            'guest_count' => 100,
            'budget' => 15000.00,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertRedirect('/events');
        $this->assertDatabaseHas('events', [
            'type' => Event::TYPE_MARIAGE,
            'guest_count' => 100,
        ]);
    }

    public function test_consultant_cannot_create_event(): void
    {
        $consultant = User::factory()->consultant()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 50]);

        $response = $this->actingAs($consultant)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_CONFERENCE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'venue_id' => $venue->id,
            'guest_count' => 30,
            'budget' => 2000.00,
            'charged_user_id' => $consultant->id,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('events', ['type' => Event::TYPE_CONFERENCE]);
    }

    public function test_charge_can_edit_event(): void
    {
        $charge = User::factory()->charge()->create();
        $event = Event::factory()->brouillon()->create([
            'charged_user_id' => $charge->id,
        ]);
        $newClient = Client::factory()->create();

        $response = $this->actingAs($charge)->put("/events/{$event->id}", [
            'client_id' => $newClient->id,
            'type' => Event::TYPE_CONCERT,
            'date' => $event->date->toDateString(),
            'start_time' => '20:00',
            'end_time' => '23:00',
            'venue_id' => $event->venue_id,
            'guest_count' => $event->guest_count,
            'budget' => $event->budget,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertRedirect('/events');
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'client_id' => $newClient->id,
            'type' => Event::TYPE_CONCERT,
        ]);
    }

    public function test_cannot_edit_terminated_event(): void
    {
        $charge = User::factory()->charge()->create();
        $event = Event::factory()->termine()->create([
            'charged_user_id' => $charge->id,
        ]);

        $response = $this->actingAs($charge)->get("/events/{$event->id}/edit");

        $response->assertRedirect('/events');
        $response->assertSessionHas('error');
    }

    public function test_cannot_edit_cancelled_event(): void
    {
        $charge = User::factory()->charge()->create();
        $event = Event::factory()->annule()->create([
            'charged_user_id' => $charge->id,
        ]);

        $response = $this->actingAs($charge)->get("/events/{$event->id}/edit");

        $response->assertRedirect('/events');
        $response->assertSessionHas('error');
    }

    public function test_show_displays_event_details(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $response = $this->actingAs($admin)->get("/events/{$event->id}");

        $response->assertStatus(200);
        $response->assertViewIs('events.show');
        $response->assertViewHas('event');
    }

    public function test_client_id_is_required(): void
    {
        $charge = User::factory()->charge()->create();
        $venue = Venue::factory()->create(['capacity' => 50]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => '',
            'type' => Event::TYPE_SEMINAIRE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'venue_id' => $venue->id,
            'guest_count' => 10,
            'budget' => 1000,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertSessionHasErrors(['client_id']);
    }

    public function test_type_must_be_valid(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 50]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => 'invalid_type',
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'venue_id' => $venue->id,
            'guest_count' => 10,
            'budget' => 1000,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertSessionHasErrors(['type']);
    }

    public function test_date_cannot_be_in_the_past(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 50]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_SEMINAIRE,
            'date' => now()->subWeek()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'venue_id' => $venue->id,
            'guest_count' => 10,
            'budget' => 1000,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertSessionHasErrors(['date']);
    }

    public function test_end_time_must_be_after_start_time(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 50]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_SEMINAIRE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '09:00',
            'venue_id' => $venue->id,
            'guest_count' => 10,
            'budget' => 1000,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertSessionHasErrors(['end_time']);
    }

    public function test_guest_count_must_be_at_least_one(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 50]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_SEMINAIRE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'venue_id' => $venue->id,
            'guest_count' => 0,
            'budget' => 1000,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertSessionHasErrors(['guest_count']);
    }

    public function test_budget_must_not_be_negative(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 50]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_SEMINAIRE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'venue_id' => $venue->id,
            'guest_count' => 10,
            'budget' => -100,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertSessionHasErrors(['budget']);
    }

    public function test_rg3_no_double_booking(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 100]);

        Event::factory()->confirme()->create([
            'venue_id' => $venue->id,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_CONFERENCE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 3000,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertSessionHasErrors(['venue_id']);
    }

    public function test_rg3_cancelled_events_do_not_block_venue(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 100]);

        Event::factory()->annule()->create([
            'venue_id' => $venue->id,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
        ]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_CONFERENCE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '12:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 3000,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertRedirect('/events');
        $this->assertDatabaseHas('events', [
            'venue_id' => $venue->id,
            'type' => Event::TYPE_CONFERENCE,
        ]);
    }

    public function test_rg3_back_to_back_bookings_are_allowed(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 100]);

        Event::factory()->confirme()->create([
            'venue_id' => $venue->id,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_CONFERENCE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '12:00',
            'end_time' => '15:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 3000,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertRedirect('/events');
    }

    public function test_rg4_guest_count_must_not_exceed_venue_capacity(): void
    {
        $charge = User::factory()->charge()->create();
        $client = Client::factory()->create();
        $venue = Venue::factory()->create(['capacity' => 50]);

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_SEMINAIRE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'venue_id' => $venue->id,
            'guest_count' => 100,
            'budget' => 5000,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertSessionHasErrors(['guest_count']);
    }

    public function test_rg6_draft_can_be_confirmed(): void
    {
        $charge = User::factory()->charge()->create();
        $event = Event::factory()->brouillon()->create([
            'charged_user_id' => $charge->id,
        ]);

        $response = $this->actingAs($charge)->patch("/events/{$event->id}/confirm");

        $response->assertRedirect('/events');
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'status' => Event::STATUS_CONFIRME,
        ]);
    }

    public function test_rg6_only_draft_can_be_confirmed(): void
    {
        $charge = User::factory()->charge()->create();
        $event = Event::factory()->confirme()->create([
            'charged_user_id' => $charge->id,
        ]);

        $response = $this->actingAs($charge)->patch("/events/{$event->id}/confirm");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_rg6_confirmed_events_auto_terminate_after_date(): void
    {
        $charge = User::factory()->charge()->create();
        Event::factory()->confirme()->create([
            'charged_user_id' => $charge->id,
            'date' => now()->subDay()->toDateString(),
        ]);

        $this->actingAs($charge)->get('/events');

        $this->assertDatabaseHas('events', [
            'status' => Event::STATUS_TERMINE,
        ]);
    }

    public function test_rg8_cancel_requires_reason(): void
    {
        $charge = User::factory()->charge()->create();
        $event = Event::factory()->confirme()->create([
            'charged_user_id' => $charge->id,
        ]);

        $response = $this->actingAs($charge)->post("/events/{$event->id}/cancel", [
            'cancellation_reason' => '',
        ]);

        $response->assertSessionHasErrors(['cancellation_reason']);
    }

    public function test_rg8_cancelled_event_remains_visible(): void
    {
        $charge = User::factory()->charge()->create();
        $event = Event::factory()->confirme()->create([
            'charged_user_id' => $charge->id,
        ]);

        $response = $this->actingAs($charge)->post("/events/{$event->id}/cancel", [
            'cancellation_reason' => 'Changement de date par le client.',
        ]);

        $response->assertRedirect('/events');
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'status' => Event::STATUS_ANNULE,
            'cancellation_reason' => 'Changement de date par le client.',
        ]);
    }

    public function test_us9_events_index_is_sorted_by_date_and_paginated(): void
    {
        $admin = User::factory()->admin()->create();
        Event::factory()->count(15)->create();

        $response = $this->actingAs($admin)->get('/events');

        $response->assertStatus(200);
        $response->assertSee('pagination');
    }

    public function test_us10_search_by_client_name(): void
    {
        $admin = User::factory()->admin()->create();
        $clientA = Client::factory()->create(['name' => 'Alpha Corp']);
        $clientB = Client::factory()->create(['name' => 'Beta Ltd']);
        Event::factory()->create(['client_id' => $clientA->id]);
        Event::factory()->create(['client_id' => $clientB->id]);

        $response = $this->actingAs($admin)->get('/events?search=Alpha');

        $response->assertStatus(200);
        $response->assertSee('Alpha Corp');
        $response->assertDontSee('Beta Ltd');
    }

    public function test_us10_filter_by_type(): void
    {
        $admin = User::factory()->admin()->create();
        Event::factory()->create(['type' => Event::TYPE_MARIAGE]);
        Event::factory()->create(['type' => Event::TYPE_CONCERT]);

        $response = $this->actingAs($admin)->get('/events?type=mariage');

        $response->assertStatus(200);
        $response->assertSee('Mariage');
        $response->assertDontSee('Concert');
    }

    public function test_us10_filter_by_status(): void
    {
        $admin = User::factory()->admin()->create();
        Event::factory()->brouillon()->create();
        Event::factory()->confirme()->create();

        $response = $this->actingAs($admin)->get('/events?status=brouillon');

        $response->assertStatus(200);
        $response->assertSee('Brouillon');
    }

    public function test_us10_filter_by_venue(): void
    {
        $admin = User::factory()->admin()->create();
        $venueA = Venue::factory()->create(['name' => 'Salle Alpha']);
        $venueB = Venue::factory()->create(['name' => 'Salle Beta']);
        Event::factory()->create(['venue_id' => $venueA->id]);
        Event::factory()->create(['venue_id' => $venueB->id]);

        $response = $this->actingAs($admin)->get("/events?venue={$venueA->id}");

        $response->assertStatus(200);
        $response->assertSee('Salle Alpha');
        $response->assertDontSee('Salle Beta');
    }

    public function test_us10_filter_by_date_range(): void
    {
        $admin = User::factory()->admin()->create();
        Event::factory()->create(['date' => now()->addWeek()->toDateString()]);
        Event::factory()->create(['date' => now()->addMonth()->toDateString()]);

        $response = $this->actingAs($admin)->get('/events?date_from=' . now()->addDays(5)->toDateString() . '&date_to=' . now()->addDays(15)->toDateString());

        $response->assertStatus(200);
    }

    public function test_us11_cannot_update_terminated_event(): void
    {
        $charge = User::factory()->charge()->create();
        $event = Event::factory()->termine()->create([
            'charged_user_id' => $charge->id,
        ]);
        $newClient = Client::factory()->create();

        $response = $this->actingAs($charge)->put("/events/{$event->id}", [
            'client_id' => $newClient->id,
            'type' => $event->type,
            'date' => $event->date->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'venue_id' => $event->venue_id,
            'guest_count' => $event->guest_count,
            'budget' => $event->budget,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertRedirect('/events');
        $response->assertSessionHas('error');
    }

    public function test_us11_cannot_update_cancelled_event(): void
    {
        $charge = User::factory()->charge()->create();
        $event = Event::factory()->annule()->create([
            'charged_user_id' => $charge->id,
        ]);
        $newClient = Client::factory()->create();

        $response = $this->actingAs($charge)->put("/events/{$event->id}", [
            'client_id' => $newClient->id,
            'type' => $event->type,
            'date' => $event->date->toDateString(),
            'start_time' => '09:00',
            'end_time' => '17:00',
            'venue_id' => $event->venue_id,
            'guest_count' => $event->guest_count,
            'budget' => $event->budget,
            'charged_user_id' => $charge->id,
        ]);

        $response->assertRedirect('/events');
        $response->assertSessionHas('error');
    }

    public function test_us13_cancel_with_confirmation_and_reason(): void
    {
        $charge = User::factory()->charge()->create();
        $event = Event::factory()->confirme()->create([
            'charged_user_id' => $charge->id,
        ]);

        $response = $this->actingAs($charge)->post("/events/{$event->id}/cancel", [
            'cancellation_reason' => 'Annulation demandée par le client.',
        ]);

        $response->assertRedirect('/events');
        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'status' => Event::STATUS_ANNULE,
        ]);
    }
}
