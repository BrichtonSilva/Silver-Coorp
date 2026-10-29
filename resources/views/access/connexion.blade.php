@extends('layouts.app')
@section('titre', 'Connexion')

@section('contenu')
    <div class="carte" style="max-width:32rem;margin:2rem auto">
        <h1>Connexion</h1>

        @if (!empty($expiree))
            <div class="message" role="status" style="border-color:var(--avert)">Votre session a expiré ou vous n'êtes pas connecté. Veuillez vous connecter.</div>
        @endif

        <form method="post" action="{{ route('connexion.envoyer') }}" novalidate>
            @csrf
            <label for="champ-identifiant">Identifiant</label>
            <input type="text" id="champ-identifiant" name="identifiant" value="{{ old('identifiant', $identifiant ?? '') }}" autocomplete="username" autocapitalize="none" spellcheck="false" required autofocus>

            <label for="champ-mot_de_passe">Mot de passe</label>
            <input type="password" id="champ-mot_de_passe" name="mot_de_passe" autocomplete="current-password" required>
            <p class="aide"><label style="display:inline;font-weight:400"><input type="checkbox" id="afficher-mdp"> Afficher le mot de passe</label></p>

            <div class="actions"><button type="submit" class="bouton">Se connecter</button></div>
        </form>
    </div>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.getElementById('afficher-mdp').addEventListener('change', function (e) {
            document.getElementById('champ-mot_de_passe').type = e.target.checked ? 'text' : 'password';
        });
    </script>
@endsection
