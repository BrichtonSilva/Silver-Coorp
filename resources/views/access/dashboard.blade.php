@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
@php
use App\Models\Event;
use App\Models\EventProviderAssignment;
use App\Models\Task;

$upcomingEvents = Event::where('date', '>=', now()->toDateString())
    ->where('status', '!=', Event::STATUS_ANNULE)
    ->where('date', '<=', now()->addDays(30)->toDateString())
    ->orderBy('date')
    ->with(['client', 'venue'])
    ->get();

$countsByStatus = Event::selectRaw('status, COUNT(*) as count')
    ->where('status', '!=', Event::STATUS_ANNULE)
    ->groupBy('status')
    ->pluck('count', 'status')
    ->toArray();

$countsByType = Event::selectRaw('type, COUNT(*) as count')
    ->where('status', '!=', Event::STATUS_ANNULE)
    ->groupBy('type')
    ->pluck('count', 'type')
    ->toArray();

// RG14: Alerts
$alerts = [];

// Provider not confirmed with event <= 15 days
$soonEvents = Event::where('date', '>=', now()->toDateString())
    ->where('date', '<=', now()->addDays(15)->toDateString())
    ->where('status', Event::STATUS_CONFIRME)
    ->with(['assignments.provider'])
    ->get();

foreach ($soonEvents as $evt) {
    foreach ($evt->assignments as $assignment) {
        if ($assignment->status !== EventProviderAssignment::STATUS_CONFIRME
            && $assignment->status !== EventProviderAssignment::STATUS_ANNULE) {
            $alerts[] = [
                'type' => 'provider',
                'message' => "Prestataire \"{$assignment->provider->name}\" non confirmé pour l'événement de {$evt->client->name} ({$evt->date->format('d/m/Y')})",
                'event' => $evt,
                'severity' => 'warning',
            ];
        }
    }
}

// Over budget events
$allEvents = Event::where('status', '!=', Event::STATUS_ANNULE)->get();
foreach ($allEvents as $evt) {
    if ($evt->isOverBudget()) {
        $alerts[] = [
            'type' => 'budget',
            'message' => "Dépassement de budget pour l'événement de {$evt->client->name} (dépenses : " . number_format($evt->realExpenses(), 2, ',', ' ') . " € / budget : " . number_format($evt->budget, 2, ',', ' ') . " €)",
            'event' => $evt,
            'severity' => 'error',
        ];
    }
}

// Overdue tasks
$overdueTasks = Task::where('is_done', false)
    ->whereNotNull('due_date')
    ->where('due_date', '<', now()->toDateString())
    ->with('event.client')
    ->get();

foreach ($overdueTasks as $task) {
    $alerts[] = [
        'type' => 'task',
        'message' => "Tâche en retard : \"{$task->title}\" pour l'événement de {$task->event->client->name}",
        'event' => $task->event,
        'severity' => 'warning',
    ];
}
@endphp

<div class="card">
    <div class="card-header">
        <h3>Bienvenue, {{ auth()->user()->name }}</h3>
        <span class="badge badge-{{ auth()->user()->role === 'administrateur' ? 'admin' : (auth()->user()->role === 'charge_evenements' ? 'charge' : 'consultant') }}">
            {{ App\Models\User::ROLE_LABELS[auth()->user()->role] }}
        </span>
    </div>
    <div class="card-body">
        <p>Voici un aperçu de l'activité de l'agence.</p>
    </div>
</div>

{{-- Stats cards --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(180px, 1fr));gap:16px;margin-bottom:20px;">
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:var(--color-primary);">{{ $upcomingEvents->count() }}</div>
            <div style="font-size:0.85rem;color:var(--color-text-muted);">Événements 30 jours</div>
        </div>
    </div>
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:var(--color-success);">{{ $countsByStatus[Event::STATUS_CONFIRME] ?? 0 }}</div>
            <div style="font-size:0.85rem;color:var(--color-text-muted);">Confirmés</div>
        </div>
    </div>
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:var(--color-warning);">{{ $countsByStatus[Event::STATUS_BROUILLON] ?? 0 }}</div>
            <div style="font-size:0.85rem;color:var(--color-text-muted);">Brouillons</div>
        </div>
    </div>
    <div class="card" style="margin-bottom:0;">
        <div class="card-body" style="text-align:center;">
            <div style="font-size:2rem;font-weight:700;color:var(--color-danger);">{{ count($alerts) }}</div>
            <div style="font-size:0.85rem;color:var(--color-text-muted);">Alertes</div>
        </div>
    </div>
</div>

{{-- Upcoming events --}}
<div class="card">
    <div class="card-header">
        <h3>Événements à venir (30 jours)</h3>
    </div>
    <div class="card-body">
        @if($upcomingEvents->count() > 0)
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Client</th>
                        <th scope="col">Type</th>
                        <th scope="col">Lieu</th>
                        <th scope="col">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcomingEvents as $evt)
                    <tr>
                        <td>{{ $evt->date->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('events.show', $evt) }}" style="color:var(--color-primary);text-decoration:none;font-weight:500;">
                                {{ $evt->client->name }}
                            </a>
                        </td>
                        <td>{{ Event::TYPE_LABELS[$evt->type] ?? $evt->type }}</td>
                        <td>{{ $evt->venue->name ?? '—' }}</td>
                        <td>
                            @if($evt->status === Event::STATUS_BROUILLON)
                                <span class="badge" style="background:#f3f4f6;color:#374151;">Brouillon</span>
                            @elseif($evt->status === Event::STATUS_CONFIRME)
                                <span class="badge" style="background:#dbeafe;color:#1e40af;">Confirmé</span>
                            @elseif($evt->status === Event::STATUS_TERMINE)
                                <span class="badge" style="background:#d1fae5;color:#065f46;">Terminé</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <p>Aucun événement prévu dans les 30 prochains jours.</p>
        </div>
        @endif
    </div>
</div>

{{-- Alerts --}}
@if(count($alerts) > 0)
<div class="card">
    <div class="card-header">
        <h3>Alertes en cours</h3>
    </div>
    <div class="card-body">
        @foreach($alerts as $alert)
        <div class="alert alert-{{ $alert['severity'] }}" role="alert" style="display:flex;align-items:center;justify-content:space-between;">
            <span>{{ $alert['message'] }}</span>
            <a href="{{ route('events.show', $alert['event']) }}" class="btn btn-sm btn-secondary">Voir l'événement</a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Events by type --}}
<div class="card">
    <div class="card-header">
        <h3>Répartition par type</h3>
    </div>
    <div class="card-body">
        @if(count($countsByType) > 0)
        <div style="display:flex;flex-wrap:wrap;gap:12px;">
            @foreach($countsByType as $type => $count)
            <div style="padding:10px 16px;background:var(--color-bg);border-radius:var(--radius);display:flex;align-items:center;gap:8px;">
                <span style="font-weight:600;">{{ Event::TYPE_LABELS[$type] ?? $type }}</span>
                <span class="badge" style="background:var(--color-primary);color:#fff;">{{ $count }}</span>
            </div>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <p>Aucun événement enregistré.</p>
        </div>
        @endif
    </div>
</div>
@endsection
