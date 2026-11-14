{{--
    Statut d'un prestataire affecté à un événement.
    Usage : <x-assignment-status-badge :status="$assignment->status" :label="$assignment->statusLabel()" />
--}}
@props(['status', 'label'])

@php
    $variants = [
        'a_contacter' => 'warning',
        'contacte' => 'info',
        'confirme' => 'success',
        'annule' => 'danger',
    ];
@endphp

<x-badge :variant="$variants[$status] ?? 'neutral'" {{ $attributes }}>{{ $label }}</x-badge>
