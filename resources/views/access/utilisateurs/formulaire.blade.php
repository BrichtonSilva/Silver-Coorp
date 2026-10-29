@extends('layouts.app')
@php $creation = ! $utilisateur->exists; @endphp
@section('titre', $creation ? 'Créer un compte' : 'Modifier un compte')

@section('contenu')
    <h1>{{ $creation ? 'Créer un compte' : 'Modifier le compte de '.$utilisateur->nom }}</h1>

    <form method="post" action="{{ $creation ? route('utilisateurs.enregistrer') : route('utilisateurs.mettre-a-jour', $utilisateur) }}" class="carte" novalidate>
        @csrf
        @unless ($creation) @method('PUT') @endunless

        <p class="aide">Tous les champs sont obligatoires.</p>

        <label for="champ-nom">Nom</label>
        <input type="text" id="champ-nom" name="nom" maxlength="100" value="{{ old('nom', $utilisateur->nom) }}" required @error('nom') aria-invalid="true" aria-describedby="erreur-nom" @enderror>
        @error('nom') <p class="erreur" id="erreur-nom">{{ $message }}</p> @enderror

        <label for="champ-identifiant">Identifiant de connexion</label>
        <input type="text" id="champ-identifiant" name="identifiant" maxlength="100" value="{{ old('identifiant', $utilisateur->identifiant) }}" autocapitalize="none" spellcheck="false" required @error('identifiant') aria-invalid="true" aria-describedby="erreur-identifiant" @enderror>
        <p class="aide">Unique, sans distinction de majuscules. Lettres, chiffres et . _ @ - uniquement.</p>
        @error('identifiant') <p class="erreur" id="erreur-identifiant">{{ $message }}</p> @enderror

        @if ($creation)
            <label for="champ-mot_de_passe">Mot de passe</label>
            <input type="password" id="champ-mot_de_passe" name="mot_de_passe" autocomplete="new-password" required @error('mot_de_passe') aria-invalid="true" aria-describedby="erreur-mot_de_passe" @enderror>
            <p class="aide">12 caractères minimum. <label style="display:inline;font-weight:400"><input type="checkbox" id="afficher-mdp"> Afficher le mot de passe</label></p>
            @error('mot_de_passe') <p class="erreur" id="erreur-mot_de_passe">{{ $message }}</p> @enderror
        @endif

        <label for="champ-role">Rôle</label>
        <select id="champ-role" name="role" required @error('role') aria-invalid="true" aria-describedby="erreur-role" @enderror>
            @foreach ($roles as $role)
                <option value="{{ $role->value }}" @selected(old('role', $utilisateur->role?->value) === $role->value)>{{ $role->libelle() }}</option>
            @endforeach
        </select>
        @error('role') <p class="erreur" id="erreur-role">{{ $message }}</p> @enderror

        <div class="actions">
            <button type="submit" class="bouton">Enregistrer</button>
            <a href="{{ route('utilisateurs.index') }}" class="bouton secondaire">Retour à la liste</a>
        </div>
    </form>

    @if ($creation)
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.getElementById('afficher-mdp').addEventListener('change', function (e) {
                document.getElementById('champ-mot_de_passe').type = e.target.checked ? 'text' : 'password';
            });
        </script>
    @endif
@endsection
