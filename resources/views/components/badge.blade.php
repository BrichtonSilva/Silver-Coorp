{{--
    Pastille colorée (statut, type, catégorie…).
    Usage : <x-badge variant="success">Confirmé</x-badge>
    Variantes : neutral, info, success, danger, warning, indigo, pink, violet (voir .badge-* dans le layout).
    Les autres attributs (class, title…) sont transmis à la balise <span>.
--}}
@props(['variant' => 'neutral'])

<span {{ $attributes->class(['badge', 'badge-' . $variant]) }}>{{ $slot }}</span>
