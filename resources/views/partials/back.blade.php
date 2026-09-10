{{-- Flecha de volver grande y siempre visible, solo la flecha (sin texto).
    Uso: @include('partials.back', ['fallback' => route('home'), 'label' => 'Volver al inicio'])
    La etiqueta accesible se conserva en aria-label para lectores de pantalla.
--}}
@props(['fallback' => url()->previous(), 'label' => 'Volver'])

<div class="back-wrap">
    <a
        href="{{ $fallback }}"
        onclick="if (window.history.length > 1) { window.history.back(); return false; }"
        class="back-button"
        aria-label="{{ $label }}"
        title="{{ $label }}"
    >
        <span class="back-arrow" aria-hidden="true">&larr;</span>
    </a>
</div>
