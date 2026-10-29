@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination">
        <ul style="list-style:none;display:flex;flex-wrap:wrap;gap:.35rem;padding:0;margin:0">
            @if ($paginator->onFirstPage())
                <li><span class="bouton secondaire" aria-disabled="true" style="opacity:.5">Précédent</span></li>
            @else
                <li><a class="bouton secondaire" href="{{ $paginator->previousPageUrl() }}" rel="prev">Précédent</a></li>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <li><span class="bouton secondaire" aria-hidden="true">{{ $element }}</span></li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li><span class="bouton" aria-current="page">{{ $page }}</span></li>
                        @else
                            <li><a class="bouton secondaire" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <li><a class="bouton secondaire" href="{{ $paginator->nextPageUrl() }}" rel="next">Suivant</a></li>
            @else
                <li><span class="bouton secondaire" aria-disabled="true" style="opacity:.5">Suivant</span></li>
            @endif
        </ul>
    </nav>
@endif
