<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Task;
use App\Models\User;
use App\Models\Venue;
use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_charge_can_add_task(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        $response = $this->actingAs($charge)->post(route('events.tasks.store', $event), [
            'title' => 'Réserver le traiteur',
            'responsible' => 'Marie Dupont',
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('tasks', [
            'event_id' => $event->id,
            'title' => 'Réserver le traiteur',
            'responsible' => 'Marie Dupont',
        ]);
    }

    public function test_consultant_cannot_add_task(): void
    {
        $consultant = User::factory()->consultant()->create();
        $event = $this->createEvent($consultant);

        $response = $this->actingAs($consultant)->post(route('events.tasks.store', $event), [
            'title' => 'Test',
        ]);

        $response->assertForbidden();
    }

    public function test_task_title_is_required(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        $response = $this->actingAs($charge)->post(route('events.tasks.store', $event), [
            'title' => '',
        ]);

        $response->assertSessionHasErrors(['title']);
    }

    public function test_charge_can_toggle_task_done(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        $task = Task::create([
            'event_id' => $event->id,
            'title' => 'Test task',
            'is_done' => false,
        ]);

        $this->assertFalse($task->fresh()->is_done);

        $response = $this->actingAs($charge)->patch(route('events.tasks.toggle', [$event, $task]));

        $response->assertRedirect(route('events.show', $event));
        $this->assertTrue($task->fresh()->is_done);
    }

    public function test_charge_can_delete_task(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        $task = Task::create([
            'event_id' => $event->id,
            'title' => 'Task to delete',
            'is_done' => false,
        ]);

        $response = $this->actingAs($charge)->delete(route('events.tasks.destroy', [$event, $task]));

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_rg13_checklist_progress(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        $this->assertEquals(0, $event->checklistProgress());

        Task::create(['event_id' => $event->id, 'title' => 'T1', 'is_done' => true]);
        Task::create(['event_id' => $event->id, 'title' => 'T2', 'is_done' => false]);
        Task::create(['event_id' => $event->id, 'title' => 'T3', 'is_done' => false]);

        $event->refresh();
        $this->assertEquals(33, $event->checklistProgress());

        Task::create(['event_id' => $event->id, 'title' => 'T4', 'is_done' => true]);

        $event->refresh();
        $this->assertEquals(50, $event->checklistProgress());
    }

    public function test_rg14_overdue_task_detection(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        $overdueTask = Task::create([
            'event_id' => $event->id,
            'title' => 'Overdue task',
            'due_date' => now()->subDay()->toDateString(),
            'is_done' => false,
        ]);

        $futureTask = Task::create([
            'event_id' => $event->id,
            'title' => 'Future task',
            'due_date' => now()->addDay()->toDateString(),
            'is_done' => false,
        ]);

        $doneTask = Task::create([
            'event_id' => $event->id,
            'title' => 'Done task',
            'due_date' => now()->subDay()->toDateString(),
            'is_done' => true,
        ]);

        $this->assertTrue($overdueTask->isOverdue());
        $this->assertFalse($futureTask->isOverdue());
        $this->assertFalse($doneTask->isOverdue());

        $overdueTasks = $event->overdueTasks();
        $this->assertCount(1, $overdueTasks);
        $this->assertEquals('Overdue task', $overdueTasks->first()->title);
    }

    public function test_task_without_due_date_is_not_overdue(): void
    {
        $charge = User::factory()->charge()->create();
        $event = $this->createEvent($charge);

        $task = Task::create([
            'event_id' => $event->id,
            'title' => 'No due date',
            'due_date' => null,
            'is_done' => false,
        ]);

        $this->assertFalse($task->isOverdue());
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
