@extends('layouts.app')

@section('content')
@include('partials.back', ['fallback' => route('home'), 'label' => 'Volver al inicio'])

<div class="page-head">
    <div>
        <div class="eyebrow">Archivo familiar</div>
        <h1 class="mt-2 text-4xl font-bold">Mis bienes</h1>
        <p class="mt-2 muted">Todo lo que queremos conservar y recordar.</p>
    </div>
    <a class="primary-button" href="{{ route('assets.create') }}">+ Añadir un bien</a>
</div>

<div class="mt-8 flex flex-wrap gap-2" role="group" aria-label="Filtrar por categoría">
    <a class="soft-button {{ !$selectedCategory ? '!bg-[#2f6655] !text-white' : '' }}" href="{{ route('assets.index') }}">Todos</a>
    @foreach($categories as $category)
        <a class="soft-button {{ (int) $selectedCategory === (int) $category->id ? '!bg-[#2f6655] !text-white' : '' }}" href="{{ route('assets.index', ['category' => $category->id]) }}">{{ $category->name }}</a>
    @endforeach
</div>

@if($assets->isEmpty())
    <div class="paper-card mt-8 p-10 text-center">
        <div class="text-5xl" aria-hidden="true">⌂</div>
        <h2 class="mt-3 text-2xl font-bold">No hay bienes aquí</h2>
        <p class="mt-2 muted">Puedes añadir el primero ahora. Solo te pediremos dos cosas.</p>
        <a class="primary-button mt-5" href="{{ route('assets.create') }}">Añadir un bien</a>
    </div>
@else
    <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        @foreach($assets as $asset)
            <article class="paper-card overflow-hidden">
                <a href="{{ route('assets.show', $asset) }}" aria-label="Abrir {{ $asset->name }}">
                    @if($asset->coverPhoto)
                        <img class="h-48 w-full object-cover" src="{{ route('photos.view', $asset->coverPhoto) }}" alt="Fotografía de {{ $asset->name }}">
                    @else
                        <div class="grid h-48 place-items-center bg-[#eef3ed] text-7xl text-[#9bb6a2]" aria-hidden="true">{{ $asset->category->icon === 'car' ? '🚗' : '⌂' }}</div>
                    @endif
                </a>
                <div class="p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <a href="{{ route('assets.show', $asset) }}"><h2 class="text-xl font-bold hover:text-[#2f6655]">{{ $asset->name }}</h2></a>
                            <p class="mt-1 text-sm muted">{{ $asset->category->name }}</p>
                        </div>
                        @if($asset->is_favorite)<span class="text-xl text-[#c4773e]" title="Favorito" aria-label="Favorito">★</span>@endif
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a class="soft-button flex-1" href="{{ route('assets.show', $asset) }}">Abrir ficha</a>
                        <a class="quiet-button" href="{{ route('assets.edit', $asset) }}" aria-label="Editar {{ $asset->name }}">Editar</a>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
@endsection
