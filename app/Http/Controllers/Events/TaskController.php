<?php

namespace App\Http\Controllers\Events;

use App\Http\Requests\Events\StoreTaskRequest;
use App\Http\Requests\Events\UpdateTaskRequest;
use App\Models\Event;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;

class TaskController extends Controller
{
    /**
     * Store a new task.
     */
    public function store(StoreTaskRequest $request, Event $event): RedirectResponse
    {
        $data = $request->validated();

        Task::create([
            'event_id' => $event->id,
            'title' => $data['title'],
            'responsible' => $data['responsible'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'is_done' => false,
        ]);

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'La tâche a été ajoutée à la checklist.');
    }

    /**
     * Update a task.
     */
    public function update(UpdateTaskRequest $request, Event $event, Task $task): RedirectResponse
    {
        $data = $request->validated();

        $task->fill([
            'title' => $data['title'],
            'responsible' => $data['responsible'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ]);

        $task->save();

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'La tâche a été modifiée.');
    }

    /**
     * Toggle task done status.
     */
    public function toggle(Event $event, Task $task): RedirectResponse
    {
        $task->is_done = !$task->is_done;
        $task->save();

        $status = $task->is_done ? 'faite' : 'non faite';

        return redirect()
            ->route('events.show', $event)
            ->with('success', "La tâche \"{$task->title}\" est marquée comme {$status}.");
    }

    /**
     * Delete a task.
     */
    public function destroy(Event $event, Task $task): RedirectResponse
    {
        $task->delete();

        return redirect()
            ->route('events.show', $event)
            ->with('success', 'La tâche a été supprimée.');
    }
}
