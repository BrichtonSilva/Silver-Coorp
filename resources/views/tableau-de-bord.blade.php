@extends('layouts.app')
@section('titre', 'Tableau de bord')

@section('contenu')
    <h1>Tableau de bord</h1>
    <div class="carte">
        <p>Bienvenue {{ auth()->user()->nom }}. Vous êtes connecté en tant que <strong>{{ auth()->user()->role->libelle() }}</strong>.</p>
        <p class="aide">Les indicateurs, les alertes et les événements à venir apparaîtront ici.</p>
    </div>
@endsection
