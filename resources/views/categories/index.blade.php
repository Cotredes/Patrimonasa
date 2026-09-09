@extends('layouts.app')

@section('content')
@include('partials.back', ['fallback' => route('home'), 'label' => 'Volver al inicio'])

<div class="eyebrow">Organización</div>
<h1 class="mt-2 text-4xl font-bold">Categorías</h1>
<p class="mt-2 max-w-2xl muted">Organiza los bienes como os resulte natural. Archivar una categoría nunca borra sus bienes.</p>

<div class="mt-8 grid gap-6 lg:grid-cols-[.85fr_1.15fr]">
    <form method="POST" action="{{ route('categories.store') }}" class="paper-card h-fit p-6 sm:p-7">
        @csrf
        <h2 class="text-xl font-bold">Crear una categoría</h2>
        <p class="mt-1 text-sm muted">Por ejemplo: Herramientas del taller.</p>
        <label class="mt-5 block"><span class="label">Nombre</span><input class="field" name="name" placeholder="Nombre de la categoría" required></label>
        <label class="mt-4 block"><span class="label">Icono (opcional)</span><input class="field" name="icon" placeholder="home, car, archive..."></label>
        <button class="primary-button mt-5 w-full" type="submit">Crear categoría</button>
    </form>

    <div class="space-y-4">
        @forelse($categories as $category)
            <article class="paper-card p-5">
                <div class="flex flex-wrap items-center gap-4">
                    <span class="grid h-12 w-12 flex-none place-items-center rounded-xl bg-[#eef3ed] text-2xl" aria-hidden="true">▧</span>
                    <div class="min-w-0 flex-1">
                        <strong class="block truncate text-lg">{{ $category->name }}</strong>
                        <p class="text-sm muted">{{ $category->assets_count }} bienes · {{ $category->is_archived ? 'Archivada' : 'Activa' }}</p>
                    </div>
                    @if($category->is_archived)
                        <span class="rounded-full bg-[#f2ecdf] px-3 py-1 text-sm font-bold muted">Archivada</span>
                    @endif
                </div>
                @if(!$category->is_archived)
                    <div class="mt-4 grid gap-3 border-t border-[#edf0ea] pt-4 sm:grid-cols-[1fr_auto]">
                        <form method="POST" action="{{ route('categories.update', $category) }}" class="flex gap-2 mobile-stack">
                            @csrf @method('PUT')
                            <label class="flex-1"><span class="sr-only">Nombre de la categoría</span><input class="field" name="name" value="{{ $category->name }}" required></label>
                            <button class="soft-button" type="submit">Guardar</button>
                        </form>
                        <form method="POST" action="{{ route('categories.archive', $category) }}">@csrf<button class="quiet-button danger-button w-full sm:w-auto" type="submit">Archivar</button></form>
                    </div>
                @endif
            </article>
        @empty
            <div class="paper-card p-8 text-center muted">Todavía no hay categorías.</div>
        @endforelse
    </div>
</div>
@endsection
