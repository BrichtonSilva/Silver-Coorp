@if ($errors->any())
    <div class="message erreur-bloc" role="alert" id="resume-erreurs" tabindex="-1">
        <strong>{{ $errors->count() > 1 ? 'Le formulaire contient des erreurs :' : 'Le formulaire contient une erreur :' }}</strong>
        <ul>
            @foreach ($errors->messages() as $champ => $messages)
                <li>
                    @if (in_array($champ, ['connexion', 'compte']))
                        {{ $messages[0] }}
                    @else
                        <a href="#champ-{{ $champ }}">{{ $messages[0] }}</a>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
@endif
@isset($erreurConnexion)
    <div class="message erreur-bloc" role="alert">{{ $erreurConnexion }}</div>
@endisset
