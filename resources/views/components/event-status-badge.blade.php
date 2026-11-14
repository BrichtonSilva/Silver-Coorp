{{--
    Statut d'un événement (brouillon, confirmé, terminé, annulé).
    Usage : <x-event-status-badge :status="$event->status" />
    N'affiche rien si le statut est inconnu.
--}}
@props(['status'])

@php
    $variants = [
        \App\Models\Event::STATUS_BROUILLON => 'neutral',
        \App\Models\Event::STATUS_CONFIRME => 'info',
        \App\Models\Event::STATUS_TERMINE => 'success',
        \App\Models\Event::STATUS_ANNULE => 'danger',
    ];
@endphp

@if(isset($variants[$status]))
    <x-badge :variant="$variants[$status]" {{ $attributes }}>{{ \App\Models\Event::STATUS_LABELS[$status] }}</x-badge>
@endif
