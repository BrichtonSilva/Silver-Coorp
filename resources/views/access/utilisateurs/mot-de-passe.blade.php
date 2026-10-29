@extends('layouts.app')
@section('titre', 'Réinitialiser le mot de passe')

@section('contenu')
    <h1>Réinitialiser le mot de passe de {{ $utilisateur->nom }}</h1>

    <form method="post" action="{{ route('utilisateurs.mot-de-passe.reinitialiser', $utilisateur) }}" class="carte" novalidate>
        @csrf
        <p class="aide">Le mot de passe actuel n'est jamais affiché. Les sessions ouvertes de ce compte seront fermées.</p>

        <label for="champ-mot_de_passe">Nouveau mot de passe</label>
        <input type="password" id="champ-mot_de_passe" name="mot_de_passe" autocomplete="new-password" required @error('mot_de_passe') aria-invalid="true" aria-describedby="erreur-mot_de_passe" @enderror>
        <p class="aide">12 caractères minimum. <label style="display:inline;font-weight:400"><input type="checkbox" id="afficher-mdp"> Afficher le mot de passe</label></p>
        @error('mot_de_passe') <p class="erreur" id="erreur-mot_de_passe">{{ $message }}</p> @enderror

        <div class="actions">
            <button type="submit" class="bouton">Réinitialiser</button>
            <a href="{{ route('utilisateurs.index') }}" class="bouton secondaire">Annuler</a>
        </div>
    </form>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.getElementById('afficher-mdp').addEventListener('change', function (e) {
            document.getElementById('champ-mot_de_passe').type = e.target.checked ? 'text' : 'password';
        });
    </script>
@endsection
