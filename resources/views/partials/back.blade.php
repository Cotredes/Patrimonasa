{{-- Flecha de volver grande y siempre visible.
    Uso: @include('partials.back', ['fallback' => route('home'), 'label' => 'Volver al inicio'])
--}}
@props(['fallback' => url()->previous(), 'label' => 'Volver'])

<div class="back-wrap">
    <a
        href="{{ $fallback }}"
        onclick="if (window.history.length > 1) { window.history.back(); return false; }"
        class="back-button"
        aria-label="{{ $label }}"
    >
        <span class="back-arrow" aria-hidden="true">&larr;</span>
        <span>{{ $label }}</span>
    </a>
</div>
