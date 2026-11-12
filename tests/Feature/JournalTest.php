<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Event;
use App\Models\JournalEntry;
use App\Models\User;
use App\Models\Venue;
use App\Services\JournalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    public function test_journal_entry_is_created_on_event_store(): void
    {
        $charge = User::factory()->charge()->create();
        $venue = Venue::factory()->create();
        $client = Client::factory()->create();

        $response = $this->actingAs($charge)->post('/events', [
            'client_id' => $client->id,
            'type' => Event::TYPE_MARIAGE,
            'date' => now()->addWeek()->toDateString(),
            'start_time' => '14:00',
            'end_time' => '23:00',
            'venue_id' => $venue->id,
            'guest_count' => 50,
            'budget' => 10000,
            'charged_user_id' => $charge->id,
        ]);

        $event = Event::latest()->first();

        $this->assertDatabaseHas('journal_entries', [
            'event_id' => $event->id,
            'user_id' => $charge->id,
            'action' => JournalEntry::ACTION_CREATION,
        ]);
    }

    public function test_journal_entry_is_created_on_event_update(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        $response = $this->actingAs($charge)->put("/events/{$event->id}", [
            'client_id' => $event->client_id,
            'type' => $event->type,
            'date' => $event->date->toDateString(),
            'start_time' => '14:00',
            'end_time' => '23:00',
            'venue_id' => $event->venue_id,
            'guest_count' => $event->guest_count,
            'budget' => 15000,
            'charged_user_id' => $event->charged_user_id,
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'event_id' => $event->id,
            'user_id' => $charge->id,
            'action' => JournalEntry::ACTION_MODIFICATION,
        ]);
    }

    public function test_journal_entry_is_created_on_event_confirm(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge, ['status' => Event::STATUS_BROUILLON]);

        $response = $this->actingAs($charge)->patch("/events/{$event->id}/confirm");

        $this->assertDatabaseHas('journal_entries', [
            'event_id' => $event->id,
            'user_id' => $charge->id,
            'action' => JournalEntry::ACTION_CHANGEMENT_STATUT,
        ]);
    }

    public function test_journal_entry_is_created_on_event_cancel(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge, ['status' => Event::STATUS_CONFIRME]);

        $response = $this->actingAs($charge)->post("/events/{$event->id}/cancel", [
            'cancellation_reason' => 'Test d\'annulation',
        ]);

        $this->assertDatabaseHas('journal_entries', [
            'event_id' => $event->id,
            'user_id' => $charge->id,
            'action' => JournalEntry::ACTION_ANNULATION,
        ]);
    }

    public function test_journal_entries_are_visible_on_event_show(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        JournalService::log(JournalEntry::ACTION_CREATION, $event->id, 'Événement créé', $charge->id);
        JournalService::log(JournalEntry::ACTION_MODIFICATION, $event->id, 'Budget modifié', $charge->id);

        $response = $this->actingAs($charge)->get("/events/{$event->id}");

        $response->assertStatus(200);
        $response->assertSee('Journal d\'historique');
        $response->assertSee('Création');
        $response->assertSee('Modification');
        $response->assertSee($charge->name);
    }

    public function test_journal_entries_cannot_be_modified(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        $entry = JournalService::log(JournalEntry::ACTION_CREATION, $event->id, 'Test', $charge->id);

        // Try to update (should fail — no route exists)
        $response = $this->actingAs($charge)->put("/journal/{$entry->id}", [
            'details' => 'Modified',
        ]);

        $response->assertNotFound();
    }

    public function test_journal_entries_cannot_be_deleted(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        $entry = JournalService::log(JournalEntry::ACTION_CREATION, $event->id, 'Test', $charge->id);

        // Try to delete (should fail — no route exists)
        $response = $this->actingAs($charge)->delete("/journal/{$entry->id}");

        $response->assertNotFound();
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
