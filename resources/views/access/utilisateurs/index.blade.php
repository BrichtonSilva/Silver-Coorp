@extends('layouts.app')
@section('titre', 'Utilisateurs')

@section('contenu')
    <h1>Utilisateurs</h1>
    <div class="actions" style="margin-top:0"><a class="bouton" href="{{ route('utilisateurs.creer') }}">Créer un compte</a></div>

    <div class="tableau-defilant" style="margin-top:1rem">
        <table>
            <caption>Comptes de l'application</caption>
            <thead>
                <tr><th scope="col">Nom</th><th scope="col">Identifiant</th><th scope="col">Rôle</th><th scope="col">État</th><th scope="col">Dernière connexion</th><th scope="col">Actions</th></tr>
            </thead>
            <tbody>
                @forelse ($utilisateurs as $u)
                    <tr>
                        <td>{{ $u->nom }}</td>
                        <td>{{ $u->identifiant }}</td>
                        <td>{{ $u->role->libelle() }}</td>
                        <td><span class="badge {{ $u->actif ? 'actif' : 'inactif' }}">{{ $u->actif ? 'Actif' : 'Inactif' }}</span></td>
                        <td>{{ $u->derniere_connexion?->translatedFormat('d/m/Y H:i') ?? 'Jamais' }}</td>
                        <td>
                            <div class="actions" style="margin:0">
                                <a href="{{ route('utilisateurs.modifier', $u) }}">Modifier {{ $u->nom }}</a>
                                <a href="{{ route('utilisateurs.mot-de-passe', $u) }}">Réinitialiser le mot de passe de {{ $u->nom }}</a>
                                @if ($u->actif)
                                    @if ($u->id !== auth()->id())
                                        <form method="post" action="{{ route('utilisateurs.desactiver', $u) }}" data-confirmation="Désactiver le compte de {{ $u->nom }} ?">
                                            @csrf
                                            <button type="submit" class="bouton danger">Désactiver {{ $u->nom }}</button>
                                        </form>
                                    @endif
                                @else
                                    <form method="post" action="{{ route('utilisateurs.reactiver', $u) }}">
                                        @csrf
                                        <button type="submit" class="bouton secondaire">Réactiver {{ $u->nom }}</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Aucun compte enregistré.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="pagination">{{ $utilisateurs->links() }}</div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.querySelectorAll('form[data-confirmation]').forEach(function (f) {
            f.addEventListener('submit', function (e) { if (!window.confirm(f.dataset.confirmation)) { e.preventDefault(); } });
        });
    </script>
@endsection
