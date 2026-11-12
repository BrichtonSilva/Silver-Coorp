@extends('layouts.app')

@section('title', 'Tableau de bord')

@section('content')
@php
use App\Models\Event;
use App\Models\EventProviderAssignment;
use App\Models\Task;

$upcomingEvents = Event::where('date', '>=', now()->toDateString())
    ->where('status', '!=', Event::STATUS_ANNULE)
    ->whereDate('date', '<=', now()->addDays(30)->toDateString())
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
    ->whereDate('date', '<=', now()->addDays(15)->toDateString())
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

// Événements par mois (mois en cours + 5 suivants)
$monthlyEvents = Event::whereDate('date', '>=', now()->startOfMonth()->toDateString())
    ->where('status', '!=', Event::STATUS_ANNULE)
    ->get()
    ->groupBy(fn ($e) => $e->date->format('Y-m'));

$months = [];
for ($i = 0; $i < 6; $i++) {
    $m = now()->startOfMonth()->addMonthsNoOverflow($i);
    $months[] = [
        'label' => $m->locale('fr')->translatedFormat('M'),
        'count' => isset($monthlyEvents[$m->format('Y-m')]) ? $monthlyEvents[$m->format('Y-m')]->count() : 0,
    ];
}
$maxMonth = max(1, max(array_column($months, 'count')));
$maxType = max(1, count($countsByType) ? max($countsByType) : 1);
$maxStatus = max(1, count($countsByStatus) ? max($countsByStatus) : 1);
@endphp

<div class="hero">
    <div>
        <h3>Bonjour, {{ auth()->user()->name }}</h3>
        <p>Voici un aperçu de l'activité de l'agence.</p>
    </div>
    <span class="badge">{{ App\Models\User::ROLE_LABELS[auth()->user()->role] }}</span>
</div>

{{-- Indicateurs --}}
<div class="kpi-grid">
    <div class="kpi">
        <div class="kpi-icon indigo"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg></div>
        <div>
            <div class="kpi-value">{{ $upcomingEvents->count() }}</div>
            <div class="kpi-label">Événements 30 jours</div>
        </div>
    </div>
    <div class="kpi">
        <div class="kpi-icon green"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg></div>
        <div>
            <div class="kpi-value">{{ $countsByStatus[Event::STATUS_CONFIRME] ?? 0 }}</div>
            <div class="kpi-label">Confirmés</div>
        </div>
    </div>
    <div class="kpi">
        <div class="kpi-icon amber"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg></div>
        <div>
            <div class="kpi-value">{{ $countsByStatus[Event::STATUS_BROUILLON] ?? 0 }}</div>
            <div class="kpi-label">Brouillons</div>
        </div>
    </div>
    <div class="kpi">
        <div class="kpi-icon red"><svg class="icon" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg></div>
        <div>
            <div class="kpi-value">{{ count($alerts) }}</div>
            <div class="kpi-label">Alertes</div>
        </div>
    </div>
</div>

{{-- Alertes en cours --}}
@if(count($alerts) > 0)
<div class="card">
    <div class="card-header">
        <h3>Alertes en cours</h3>
        <span class="badge badge-danger">{{ count($alerts) }}</span>
    </div>
    <div class="card-body">
        @foreach($alerts as $alert)
        <div class="alert-item {{ $alert['severity'] }}" role="alert">
            <span>{{ $alert['message'] }}</span>
            <a href="{{ route('events.show', $alert['event']) }}" class="btn btn-sm btn-secondary">Voir l'événement</a>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- Graphiques --}}
<div class="dash-grid">
    <div class="card">
        <div class="card-header"><h3>Événements par mois</h3></div>
        <div class="card-body">
            <div class="month-chart" role="img" aria-label="Nombre d'événements par mois sur les six prochains mois">
                @foreach($months as $month)
                <div class="month-col">
                    <span class="month-value">{{ $month['count'] }}</span>
                    <div class="month-bar {{ $month['count'] === 0 ? 'zero' : '' }}" style="height:{{ $month['count'] === 0 ? 4 : max(8, round($month['count'] / $maxMonth * 100)) }}%;"></div>
                    <span class="month-name">{{ $month['label'] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Répartition par statut</h3></div>
        <div class="card-body">
            @php
                $statusBars = [
                    Event::STATUS_BROUILLON => 'amber',
                    Event::STATUS_CONFIRME => '',
                    Event::STATUS_TERMINE => 'green',
                ];
            @endphp
            @foreach($statusBars as $status => $barClass)
            @php $count = $countsByStatus[$status] ?? 0; @endphp
            <div class="bar-row">
                <span>{{ Event::STATUS_LABELS[$status] ?? $status }}</span>
                <div class="bar-track"><div class="bar-fill {{ $barClass }}" style="width:{{ $count === 0 ? 0 : max(4, round($count / $maxStatus * 100)) }}%;"></div></div>
                <span class="bar-count">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3>Répartition par type</h3></div>
        <div class="card-body">
            @if(count($countsByType) > 0)
                @foreach($countsByType as $type => $count)
                <div class="bar-row">
                    <span>{{ Event::TYPE_LABELS[$type] ?? $type }}</span>
                    <div class="bar-track"><div class="bar-fill" style="width:{{ max(4, round($count / $maxType * 100)) }}%;"></div></div>
                    <span class="bar-count">{{ $count }}</span>
                </div>
                @endforeach
            @else
            <div class="empty-state">
                <p>Aucun événement enregistré.</p>
            </div>
            @endif
        </div>
    </div>
</div>

{{-- Événements à venir --}}
<div class="card">
    <div class="card-header">
        <h3>Événements à venir (30 jours)</h3>
        <a href="{{ route('events.index') }}" class="btn btn-sm btn-secondary">Tous les événements</a>
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
                            <a href="{{ route('events.show', $evt) }}" class="link">{{ $evt->client->name }}</a>
                        </td>
                        <td>{{ Event::TYPE_LABELS[$evt->type] ?? $evt->type }}</td>
                        <td>{{ $evt->venue->name ?? '—' }}</td>
                        <td>
                            @if($evt->status === Event::STATUS_BROUILLON)
                                <span class="badge badge-neutral">Brouillon</span>
                            @elseif($evt->status === Event::STATUS_CONFIRME)
                                <span class="badge badge-info">Confirmé</span>
                            @elseif($evt->status === Event::STATUS_TERMINE)
                                <span class="badge badge-success">Terminé</span>
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
@endsection
