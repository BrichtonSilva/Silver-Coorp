{{--
    Type d'événement (mariage, séminaire…) avec sa couleur.
    Usage : <x-event-type-badge :type="$event->type" />
--}}
@props(['type'])

@php
    $variants = [
        \App\Models\Event::TYPE_MARIAGE => 'pink',
        \App\Models\Event::TYPE_SEMINAIRE => 'info',
        \App\Models\Event::TYPE_CONFERENCE => 'indigo',
        \App\Models\Event::TYPE_CONCERT => 'warning',
    ];
@endphp

<x-badge :variant="$variants[$type] ?? 'neutral'" {{ $attributes }}>{{ \App\Models\Event::TYPE_LABELS[$type] ?? $type }}</x-badge>
