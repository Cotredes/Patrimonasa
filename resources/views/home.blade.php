@extends('layouts.app')

@section('content')
<section class="relative overflow-hidden rounded-3xl bg-[#e8f0e9] px-6 py-8 sm:px-10 sm:py-12">
    <div class="relative z-10 max-w-3xl">
        <div class="eyebrow">Tu archivo familiar</div>
        <h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-5xl">Hola, {{ Str::before(auth()->user()->name, ' ') }}.<br><span class="text-[#2f6655]">¿Qué quieres encontrar?</span></h1>
        <p class="mt-4 max-w-xl text-lg muted">Busca una casa, un vehículo o un documento. Todo está aquí para volver a encontrarlo cuando lo necesites.</p>
        <form action="{{ route('home') }}" class="mt-7 flex max-w-2xl gap-3 mobile-stack" role="search">
            <label class="relative block flex-1">
                <span class="sr-only">Buscar</span>
                <span class="absolute left-4 top-3.5 text-xl" aria-hidden="true">&#8981;</span>
                <input class="field pl-12" name="q" value="{{ $query }}" placeholder="Busca una casa, un vehículo o un documento" aria-label="Busca una casa, un vehículo o un documento">
            </label>
            <button class="primary-button" type="submit">Buscar</button>
        </form>
    </div>
    <div class="pointer-events-none absolute -right-6 -top-10 hidden select-none text-[10rem] leading-none text-[#d3e4d5] opacity-70 sm:block" aria-hidden="true">&#9000;</div>
</section>

<div class="page-head mt-8">
    <div>
        <div class="eyebrow">Acceso rápido</div>
        <h2 class="mt-1 text-2xl font-bold">Tus bienes</h2>
    </div>
    <a class="primary-button" href="{{ route('assets.create') }}">+ Añadir un bien</a>
</div>

<div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach($categories as $category)
        <a href="{{ route('assets.index', ['category' => $category->id]) }}" class="paper-card group flex items-center gap-4 p-5 hover:-translate-y-0.5 hover:border-[#9fc4a8]">
            <span class="grid h-14 w-14 flex-none place-items-center rounded-2xl bg-[#f2ecdf] text-2xl" aria-hidden="true">{{ ['home' => '⌂', 'map' => '◎', 'key' => '⚿', 'building' => '▥', 'car' => '🚗', 'archive' => '▧'][$category->icon] ?? '▧' }}</span>
            <span class="min-w-0">
                <strong class="block truncate text-lg">{{ $category->name }}</strong>
                <small class="muted">Ver los bienes de esta categoría</small>
            </span>
            <span class="ml-auto text-xl text-[#9ab2a2] group-hover:text-[#2f6655]" aria-hidden="true">&rarr;</span>
        </a>
    @endforeach
</div>

<div class="mt-10 grid gap-7 lg:grid-cols-[1.35fr_.65fr]">
    <section aria-label="Favoritos y recientes">
        <div class="flex items-end justify-between gap-3">
            <div>
                <div class="eyebrow">Para tenerlos a mano</div>
                <h2 class="mt-1 text-2xl font-bold">Favoritos y recientes</h2>
            </div>
            <a class="quiet-button" href="{{ route('assets.index') }}">Ver todos &rarr;</a>
        </div>
        @if($assets->isEmpty())
            <div class="paper-card mt-4 p-8 text-center">
                <div class="text-5xl" aria-hidden="true">⌂</div>
                <h3 class="mt-3 text-xl font-bold">Todavía no hay bienes</h3>
                <p class="mx-auto mt-2 max-w-md muted">Empieza con algo sencillo. Solo necesitamos un nombre y una categoría.</p>
                <a class="primary-button mt-5" href="{{ route('assets.create') }}">Añadir mi primer bien</a>
            </div>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                @foreach($assets->take(4) as $asset)
                    <a href="{{ route('assets.show', $asset) }}" class="paper-card overflow-hidden hover:-translate-y-0.5">
                        @if($asset->coverPhoto)
                            <img class="h-40 w-full object-cover" src="{{ route('photos.view', $asset->coverPhoto) }}" alt="Fotografía de {{ $asset->name }}">
                        @else
                            <div class="grid h-40 place-items-center bg-[#eef3ed] text-6xl text-[#9bb6a2]" aria-hidden="true">{{ $asset->category->icon === 'car' ? '🚗' : '⌂' }}</div>
                        @endif
                        <div class="p-4">
                            <div class="flex justify-between gap-3">
                                <strong class="text-lg">{{ $asset->name }}</strong>
                                @if($asset->is_favorite)<span title="Favorito" aria-label="Favorito">★</span>@endif
                            </div>
                            <small class="muted">{{ $asset->category->name }}</small>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <aside aria-label="Recordatorios próximos">
        <div class="eyebrow">Próximamente</div>
        <h2 class="mt-1 text-2xl font-bold">Recordatorios</h2>
        <div class="paper-card mt-4 divide-y divide-[#edf0ea]">
            @forelse($reminders as $reminder)
                <a href="{{ $reminder->asset ? route('assets.show', $reminder->asset) : route('reminders.index') }}" class="block p-4 hover:bg-[#f7faf5]">
                    <strong class="block">{{ $reminder->title }}</strong>
                    <span class="text-sm {{ $reminder->due_date->isPast() ? 'font-bold text-[#a84d38]' : 'muted' }}">{{ $reminder->due_date->translatedFormat('j \d\e F') }}@if($reminder->asset) · {{ $reminder->asset->name }}@endif</span>
                </a>
            @empty
                <div class="p-5">
                    <p class="font-semibold">No hay fechas pendientes.</p>
                    <p class="mt-1 text-sm muted">Puedes añadir un recordatorio cuando quieras.</p>
                    <a class="quiet-button mt-2 !px-0" href="{{ route('reminders.index') }}">Gestionar recordatorios &rarr;</a>
                </div>
            @endforelse
        </div>
    </aside>
</div>

@if($query !== '')
    <section class="mt-10" aria-label="Resultados de búsqueda">
        <div class="eyebrow">Resultados para «{{ $query }}»</div>
        <h2 class="mt-1 text-2xl font-bold">Encontrado</h2>
        @if($assets->isEmpty() && $documents->isEmpty())
            <div class="mt-4 rounded-xl bg-[#fff3ef] p-5">No hemos encontrado «{{ $query }}». <a class="font-bold underline" href="{{ route('home') }}">Limpiar búsqueda</a></div>
        @else
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @foreach($assets->take(4) as $asset)
                    <a href="{{ route('assets.show', $asset) }}" class="paper-card p-4">
                        <strong>⌂ {{ $asset->name }}</strong>
                        <span class="mt-1 block text-sm muted">{{ $asset->category->name }} · Bien</span>
                    </a>
                @endforeach
                @foreach($documents->take(6) as $document)
                    <a href="{{ route('documents.view', $document) }}" class="paper-card p-4">
                        <strong>▤ {{ $document->title }}</strong>
                        <span class="mt-1 block text-sm muted">{{ $document->asset?->name ?? 'Pendiente de organizar' }} · {{ $document->type }}</span>
                    </a>
                @endforeach
            </div>
            <a class="quiet-button mt-3 !px-0" href="{{ route('home') }}">Limpiar búsqueda</a>
        @endif
    </section>
@endif
@endsection
