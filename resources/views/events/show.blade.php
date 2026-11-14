@extends('layouts.app')

@section('title', 'Événement — ' . ($event->client?->name ?? 'Détail'))

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Détail de l'événement</h3>
        @if(auth()->user()->canWrite() && !$event->isTerminated() && !$event->isCancelled())
        <a href="{{ route('events.edit', $event) }}" class="btn btn-secondary btn-sm">Modifier</a>
        @endif
    </div>
    <div class="card-body">
        <div class="detail-grid">
            <div>
                <div class="form-group">
                    <label class="detail-label">Client</label>
                    <p>
                        <a href="{{ route('clients.show', $event->client) }}" class="link">
                            {{ $event->client?->name ?? '—' }}
                        </a>
                    </p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Type</label>
                    <p>
                        <x-event-type-badge :type="$event->type" />
                    </p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Date</label>
                    <p>{{ $event->date->format('d/m/Y') }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Horaire</label>
                    <p>{{ \Illuminate\Support\Carbon::parse($event->start_time)->format('H:i') }} – {{ \Illuminate\Support\Carbon::parse($event->end_time)->format('H:i') }}</p>
                </div>
            </div>

            <div>
                <div class="form-group">
                    <label class="detail-label">Lieu</label>
                    <p>
                        <a href="{{ route('venues.show', $event->venue) }}" class="link">
                            {{ $event->venue?->name ?? '—' }}
                        </a>
                        @if($event->venue)
                            <span class="form-help">(capacité : {{ $event->venue->capacity }})</span>
                        @endif
                    </p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Nombre d'invités</label>
                    <p>{{ $event->guest_count }}</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Budget</label>
                    <p>{{ number_format($event->budget, 2, ',', ' ') }} €</p>
                </div>

                <div class="form-group">
                    <label class="detail-label">Chargé d'événements</label>
                    <p>{{ $event->chargedUser?->name ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div style="margin-top:16px;border-top:1px solid var(--color-border);padding-top:16px;">
            <div class="form-group">
                <label class="detail-label">Statut</label>
                <p>
                    <x-event-status-badge :status="$event->status" />
                </p>
            </div>

            @if($event->cancellation_reason)
            <div class="form-group">
                <label class="detail-label">Motif d'annulation</label>
                <p>{{ $event->cancellation_reason }}</p>
            </div>
            @endif

            <div class="form-group">
                <label class="detail-label">Date de création</label>
                <p>{{ $event->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('events.index') }}" class="btn btn-secondary">Retour à la liste</a>

            @if(auth()->user()->canWrite())
                @if($event->isDraft())
                <form method="POST" action="{{ route('events.confirm', $event) }}" style="display:inline;">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Confirmer cet événement ?');">Confirmer l'événement</button>
                </form>
                @endif

                @if(!$event->isTerminated() && !$event->isCancelled())
                <form method="POST" action="{{ route('events.cancel', $event) }}" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cet événement ? Cette action est irréversible.');">
                    @csrf
                    <div class="form-group" style="display:inline-block;margin-bottom:0;">
                        <input type="text" name="cancellation_reason" class="form-control" placeholder="Motif d'annulation..." required style="display:inline-block;width:auto;min-width:250px;" maxlength="1000">
                    </div>
                    <button type="submit" class="btn btn-danger">Annuler l'événement</button>
                </form>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- M8 — Budget tracking --}}
<div class="card">
    <div class="card-header">
        <h3>Suivi du budget</h3>
    </div>
    <div class="card-body">
        @php
            $realExpenses = $event->realExpenses();
            $remaining = $event->budgetRemaining();
            $overBudget = $event->isOverBudget();
            $progressPercent = $event->budget > 0 ? min(100, ($realExpenses / $event->budget) * 100) : 0;
        @endphp
        <div class="kpi-grid">
            <div class="stat-box">
                <div style="font-size:0.8rem;color:var(--color-text-muted);">Budget prévisionnel</div>
                <div style="font-size:1.3rem;font-weight:700;">{{ number_format($event->budget, 2, ',', ' ') }} €</div>
            </div>
            <div class="stat-box">
                <div style="font-size:0.8rem;color:var(--color-text-muted);">Dépenses réelles</div>
                <div style="font-size:1.3rem;font-weight:700;">{{ number_format($realExpenses, 2, ',', ' ') }} €</div>
            </div>
            <div class="stat-box">
                <div style="font-size:0.8rem;color:var(--color-text-muted);">Reste disponible</div>
                <div style="font-size:1.3rem;font-weight:700;{{ $overBudget ? 'color:var(--color-danger);' : 'color:var(--color-success);' }}">
                    {{ number_format($remaining, 2, ',', ' ') }} €
                </div>
            </div>
            <div class="stat-box">
                <div style="font-size:0.8rem;color:var(--color-text-muted);">Utilisation</div>
                <div style="font-size:1.3rem;font-weight:700;">{{ number_format($progressPercent, 1, ',', ' ') }} %</div>
            </div>
        </div>
        @if($overBudget)
        <div class="alert alert-error" role="alert">
            <strong>Dépassement de budget !</strong> Les dépenses réelles dépassent le budget prévisionnel de {{ number_format(abs($remaining), 2, ',', ' ') }} €.
        </div>
        @endif
        <div style="background:var(--color-border);height:8px;border-radius:4px;overflow:hidden;">
            <div style="background:{{ $overBudget ? 'var(--color-danger)' : 'var(--color-primary)' }};height:100%;width:{{ min(100, $progressPercent) }}%;transition:width 0.3s;"></div>
        </div>
    </div>
</div>

{{-- M6 — Provider assignments --}}
<div class="card">
    <div class="card-header">
        <h3>Prestataires affectés</h3>
        @if(auth()->user()->canWrite() && !$event->isTerminated() && !$event->isCancelled())
        <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('add-assignment-form').style.display='block';this.style.display='none';">+ Ajouter un prestataire</button>
        @endif
    </div>
    <div class="card-body">
        @if(auth()->user()->canWrite() && !$event->isTerminated() && !$event->isCancelled())
        <form id="add-assignment-form" method="POST" action="{{ route('events.assignments.store', $event) }}" style="display:none;margin-bottom:20px;padding:16px;background:var(--color-bg);border-radius:var(--radius);">
            @csrf
            <div class="inline-form-grid">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Prestataire <span class="required">*</span></label>
                    <select name="provider_id" class="form-control" required>
                        <option value="">Choisir...</option>
                        @foreach(App\Models\Provider::orderBy('name')->get() as $provider)
                        <option value="{{ $provider->id }}">{{ $provider->name }} ({{ App\Models\Provider::CATEGORY_LABELS[$provider->category] ?? $provider->category }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Coût (€) <span class="required">*</span></label>
                    <input type="number" name="cost" class="form-control" step="0.01" min="0" required value="{{ old('cost', 0) }}">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Statut <span class="required">*</span></label>
                    <select name="status" class="form-control" required>
                        @foreach(App\Models\EventProviderAssignment::STATUSES as $status)
                        <option value="{{ $status }}" {{ old('status') === $status ? 'selected' : '' }}>{{ App\Models\EventProviderAssignment::STATUS_LABELS[$status] }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-assignment-form').style.display='none';this.parentElement.previousElementSibling.previousElementSibling.previousElementSibling.querySelector('button').style.display='inline-flex';">Annuler</button>
                </div>
            </div>
        </form>
        @endif

        @if($event->assignments->count() > 0)
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Prestataire</th>
                        <th scope="col">Catégorie</th>
                        <th scope="col">Coût</th>
                        <th scope="col">Statut</th>
                        @if(auth()->user()->canWrite() && !$event->isTerminated() && !$event->isCancelled())
                        <th scope="col">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($event->assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->provider->name }}</td>
                        <td>
                            <x-badge variant="indigo">{{ App\Models\Provider::CATEGORY_LABELS[$assignment->provider->category] ?? $assignment->provider->category }}</x-badge>
                        </td>
                        <td>{{ number_format($assignment->cost, 2, ',', ' ') }} €</td>
                        <td>
                            <x-assignment-status-badge :status="$assignment->status" :label="$assignment->statusLabel()" />
                        </td>
                        @if(auth()->user()->canWrite() && !$event->isTerminated() && !$event->isCancelled())
                        <td>
                            <div class="actions">
                                <form method="POST" action="{{ route('events.assignments.update', [$event, $assignment]) }}" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="form-control" style="width:auto;display:inline-block;" onchange="this.form.submit()">
                                        @foreach(App\Models\EventProviderAssignment::STATUSES as $status)
                                        <option value="{{ $status }}" {{ $assignment->status === $status ? 'selected' : '' }}>{{ App\Models\EventProviderAssignment::STATUS_LABELS[$status] }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="cost" value="{{ $assignment->cost }}">
                                </form>
                                <form method="POST" action="{{ route('events.assignments.destroy', [$event, $assignment]) }}" style="display:inline;" onsubmit="return confirm('Supprimer cette affectation ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <p>Aucun prestataire affecté à cet événement.</p>
        </div>
        @endif
    </div>
</div>

{{-- M7 — Checklist --}}
<div class="card">
    <div class="card-header">
        <h3>Checklist de préparation</h3>
        @php $progress = $event->checklistProgress(); @endphp
        <div style="display:flex;align-items:center;gap:12px;">
            <x-badge :variant="$progress === 100 ? 'success' : 'info'">{{ $progress }} %</x-badge>
            @if(auth()->user()->canWrite() && !$event->isTerminated() && !$event->isCancelled())
            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('add-task-form').style.display='block';this.style.display='none';">+ Ajouter une tâche</button>
            @endif
        </div>
    </div>
    <div class="card-body">
        @if(auth()->user()->canWrite() && !$event->isTerminated() && !$event->isCancelled())
        <form id="add-task-form" method="POST" action="{{ route('events.tasks.store', $event) }}" style="display:none;margin-bottom:20px;padding:16px;background:var(--color-bg);border-radius:var(--radius);">
            @csrf
            <div class="inline-form-grid">
                <div class="form-group" style="margin-bottom:0;">
                    <label>Intitulé <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" required maxlength="255" value="{{ old('title') }}">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Responsable</label>
                    <input type="text" name="responsible" class="form-control" maxlength="255" value="{{ old('responsible') }}">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Échéance</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}">
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('add-task-form').style.display='none';this.parentElement.previousElementSibling.previousElementSibling.previousElementSibling.querySelector('button').style.display='inline-flex';">Annuler</button>
                </div>
            </div>
        </form>
        @endif

        @if($event->tasks->count() > 0)
        <div style="background:var(--color-border);height:8px;border-radius:4px;overflow:hidden;margin-bottom:20px;">
            <div style="background:var(--color-primary);height:100%;width:{{ $progress }}%;transition:width 0.3s;"></div>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th scope="col" style="width:40px;"></th>
                        <th scope="col">Tâche</th>
                        <th scope="col">Responsable</th>
                        <th scope="col">Échéance</th>
                        <th scope="col">Statut</th>
                        @if(auth()->user()->canWrite() && !$event->isTerminated() && !$event->isCancelled())
                        <th scope="col">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($event->tasks as $task)
                    <tr class="{{ $task->isOverdue() ? 'row-overdue' : '' }}">
                        <td>
                            <form method="POST" action="{{ route('events.tasks.toggle', [$event, $task]) }}" style="display:inline;">
                                @csrf
                                @method('PATCH')
                                <input type="checkbox" {{ $task->is_done ? 'checked' : '' }} onchange="this.form.submit()" style="cursor:pointer;width:18px;height:18px;" aria-label="Marquer comme {{ $task->is_done ? 'non faite' : 'faite' }}">
                            </form>
                        </td>
                        <td class="{{ $task->is_done ? 'line-through' : '' }}">{{ $task->title }}</td>
                        <td>{{ $task->responsible ?? '—' }}</td>
                        <td>{{ $task->due_date?->format('d/m/Y') ?? '—' }}</td>
                        <td>
                            @if($task->is_done)
                                <x-badge variant="success">Faite</x-badge>
                            @elseif($task->isOverdue())
                                <x-badge variant="danger">En retard</x-badge>
                            @else
                                <x-badge variant="warning">À faire</x-badge>
                            @endif
                        </td>
                        @if(auth()->user()->canWrite() && !$event->isTerminated() && !$event->isCancelled())
                        <td>
                            <div class="actions">
                                <form method="POST" action="{{ route('events.tasks.destroy', [$event, $task]) }}" style="display:inline;" onsubmit="return confirm('Supprimer cette tâche ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <p>Aucune tâche dans la checklist.</p>
        </div>
        @endif
    </div>
</div>

{{-- RG16 — Journal d'historique --}}
<div class="card">
    <div class="card-header">
        <h3>Journal d'historique</h3>
    </div>
    <div class="card-body">
        @if($event->journalEntries->count() > 0)
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Action</th>
                        <th scope="col">Utilisateur</th>
                        <th scope="col">Détail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($event->journalEntries as $entry)
                    <tr>
                        <td>{{ $entry->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <x-badge variant="indigo">{{ $entry->actionLabel() }}</x-badge>
                        </td>
                        <td>{{ $entry->user?->name ?? 'Système' }}</td>
                        <td>{{ $entry->details ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="empty-state">
            <p>Aucune entrée dans le journal.</p>
        </div>
        @endif
    </div>
</div>
@endsection
