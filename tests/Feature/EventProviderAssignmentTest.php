<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventProviderAssignment;
use App\Models\Provider;
use App\Models\User;
use App\Models\Venue;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventProviderAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_charge_can_add_assignment(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);
        $provider = Provider::factory()->create();

        $response = $this->actingAs($charge)->post(route('events.assignments.store', $event), [
            'provider_id' => $provider->id,
            'cost' => 1500.00,
            'status' => EventProviderAssignment::STATUS_A_CONTACTER,
        ]);

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('event_provider_assignments', [
            'event_id' => $event->id,
            'provider_id' => $provider->id,
            'cost' => 1500.00,
        ]);
    }

    public function test_consultant_cannot_add_assignment(): void
    {
        $consultant = User::factory()->consultant()->create();
        $event = $this->createEvent($consultant);
        $provider = Provider::factory()->create();

        $response = $this->actingAs($consultant)->post(route('events.assignments.store', $event), [
            'provider_id' => $provider->id,
            'cost' => 1500.00,
            'status' => EventProviderAssignment::STATUS_A_CONTACTER,
        ]);

        $response->assertForbidden();
    }

    public function test_rg10_prevents_provider_double_booking(): void
    {
        $charge = User::factory()->charge()->create();
        $venue = Venue::factory()->create();
        $client = Client::factory()->create();
        $provider = Provider::factory()->create();

        // First event
        $event1 = Event::create([
            'client_id' => $client->id,
            'type' => Event::TYPE_MARIAGE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '23:00:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 10000,
            'status' => Event::STATUS_CONFIRME,
            'charged_user_id' => $charge->id,
        ]);

        EventProviderAssignment::create([
            'event_id' => $event1->id,
            'provider_id' => $provider->id,
            'cost' => 1000,
            'status' => EventProviderAssignment::STATUS_CONFIRME,
        ]);

        // Second event on same date with overlapping time
        $event2 = Event::create([
            'client_id' => $client->id,
            'type' => Event::TYPE_CONCERT,
            'date' => $event1->date,
            'start_time' => '18:00:00',
            'end_time' => '22:00:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 5000,
            'status' => Event::STATUS_BROUILLON,
            'charged_user_id' => $charge->id,
        ]);

        $response = $this->actingAs($charge)->post(route('events.assignments.store', $event2), [
            'provider_id' => $provider->id,
            'cost' => 800,
            'status' => EventProviderAssignment::STATUS_A_CONTACTER,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('event_provider_assignments', [
            'event_id' => $event2->id,
            'provider_id' => $provider->id,
        ]);
    }

    public function test_rg10_allows_non_overlapping_events(): void
    {
        $charge = User::factory()->charge()->create();
        $venue = Venue::factory()->create();
        $client = Client::factory()->create();
        $provider = Provider::factory()->create();

        // First event: 14:00-17:00
        $event1 = Event::create([
            'client_id' => $client->id,
            'type' => Event::TYPE_MARIAGE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '17:00:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 10000,
            'status' => Event::STATUS_CONFIRME,
            'charged_user_id' => $charge->id,
        ]);

        EventProviderAssignment::create([
            'event_id' => $event1->id,
            'provider_id' => $provider->id,
            'cost' => 1000,
            'status' => EventProviderAssignment::STATUS_CONFIRME,
        ]);

        // Second event: 17:00-20:00 (exactly follows first)
        $event2 = Event::create([
            'client_id' => $client->id,
            'type' => Event::TYPE_CONCERT,
            'date' => $event1->date,
            'start_time' => '17:00:00',
            'end_time' => '20:00:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 5000,
            'status' => Event::STATUS_BROUILLON,
            'charged_user_id' => $charge->id,
        ]);

        $response = $this->actingAs($charge)->post(route('events.assignments.store', $event2), [
            'provider_id' => $provider->id,
            'cost' => 800,
            'status' => EventProviderAssignment::STATUS_A_CONTACTER,
        ]);

        $response->assertRedirect(route('events.show', $event2));
        $this->assertDatabaseHas('event_provider_assignments', [
            'event_id' => $event2->id,
            'provider_id' => $provider->id,
        ]);
    }

    public function test_rg10_allows_cancelled_event_provider_reuse(): void
    {
        $charge = User::factory()->charge()->create();
        $venue = Venue::factory()->create();
        $client = Client::factory()->create();
        $provider = Provider::factory()->create();

        // Cancelled event
        $event1 = Event::create([
            'client_id' => $client->id,
            'type' => Event::TYPE_MARIAGE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '23:00:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 10000,
            'status' => Event::STATUS_ANNULE,
            'cancellation_reason' => 'Test',
            'charged_user_id' => $charge->id,
        ]);

        EventProviderAssignment::create([
            'event_id' => $event1->id,
            'provider_id' => $provider->id,
            'cost' => 1000,
            'status' => EventProviderAssignment::STATUS_CONFIRME,
        ]);

        // New event on same date/time
        $event2 = Event::create([
            'client_id' => $client->id,
            'type' => Event::TYPE_CONCERT,
            'date' => $event1->date,
            'start_time' => '14:00:00',
            'end_time' => '23:00:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 5000,
            'status' => Event::STATUS_BROUILLON,
            'charged_user_id' => $charge->id,
        ]);

        $response = $this->actingAs($charge)->post(route('events.assignments.store', $event2), [
            'provider_id' => $provider->id,
            'cost' => 800,
            'status' => EventProviderAssignment::STATUS_A_CONTACTER,
        ]);

        $response->assertRedirect(route('events.show', $event2));
    }

    public function test_charge_can_update_assignment_status(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);
        $provider = Provider::factory()->create();

        $assignment = EventProviderAssignment::create([
            'event_id' => $event->id,
            'provider_id' => $provider->id,
            'cost' => 1000,
            'status' => EventProviderAssignment::STATUS_A_CONTACTER,
        ]);

        $response = $this->actingAs($charge)->put(route('events.assignments.update', [$event, $assignment]), [
            'cost' => 1200,
            'status' => EventProviderAssignment::STATUS_CONFIRME,
        ]);

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('event_provider_assignments', [
            'id' => $assignment->id,
            'status' => EventProviderAssignment::STATUS_CONFIRME,
            'cost' => 1200,
        ]);
    }

    public function test_charge_can_delete_assignment(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);
        $provider = Provider::factory()->create();

        $assignment = EventProviderAssignment::create([
            'event_id' => $event->id,
            'provider_id' => $provider->id,
            'cost' => 1000,
            'status' => EventProviderAssignment::STATUS_A_CONTACTER,
        ]);

        $response = $this->actingAs($charge)->delete(route('events.assignments.destroy', [$event, $assignment]));

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseMissing('event_provider_assignments', ['id' => $assignment->id]);
    }

    public function test_rg12_real_expenses_calculation(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);
        $provider = Provider::factory()->create();

        // Add confirmed assignment
        EventProviderAssignment::create([
            'event_id' => $event->id,
            'provider_id' => $provider->id,
            'cost' => 1500,
            'status' => EventProviderAssignment::STATUS_CONFIRME,
        ]);

        // Add cancelled assignment (should not count)
        EventProviderAssignment::create([
            'event_id' => $event->id,
            'provider_id' => Provider::factory()->create()->id,
            'cost' => 500,
            'status' => EventProviderAssignment::STATUS_ANNULE,
        ]);

        $event->refresh();
        $expected = 1500 + $event->venue->rental_price;
        $this->assertEquals($expected, $event->realExpenses());
    }

    public function test_budget_remaining_and_over_budget(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge, ['budget' => 5000]);
        $provider = Provider::factory()->create();

        EventProviderAssignment::create([
            'event_id' => $event->id,
            'provider_id' => $provider->id,
            'cost' => 3000,
            'status' => EventProviderAssignment::STATUS_CONFIRME,
        ]);

        $event->refresh();
        $this->assertFalse($event->isOverBudget());
        $this->assertGreaterThan(0, $event->budgetRemaining());

        // Add another assignment to go over budget
        EventProviderAssignment::create([
            'event_id' => $event->id,
            'provider_id' => Provider::factory()->create()->id,
            'cost' => 5000,
            'status' => EventProviderAssignment::STATUS_CONFIRME,
        ]);

        $event->refresh();
        $this->assertTrue($event->isOverBudget());
        $this->assertLessThan(0, $event->budgetRemaining());
    }

    private function createEvent(User $user, array $overrides = []): Event
    {
        $venue = Venue::factory()->create();
        $client = Client::factory()->create();

        return Event::create(array_merge([
            'client_id' => $client->id,
            'type' => Event::TYPE_MARIAGE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '14:00:00',
            'end_time' => '23:00:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 10000,
            'status' => Event::STATUS_BROUILLON,
            'charged_user_id' => $user->id,
        ], $overrides));
    }
}
