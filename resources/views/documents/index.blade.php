@extends('layouts.app')

@section('content')
@include('partials.back', ['fallback' => route('home'), 'label' => 'Volver al inicio'])

<div class="page-head">
    <div>
        <div class="eyebrow">Archivo completo</div>
        <h1 class="mt-2 text-4xl font-bold">Documentos</h1>
        <p class="mt-2 muted">Encuentra cualquier papel y sabrás a qué bien pertenece.</p>
    </div>
</div>

<form class="mt-8 flex gap-3 mobile-stack" action="{{ route('documents.all') }}" role="search">
    <input class="field flex-1" name="q" value="{{ $query }}" placeholder="Buscar por título, archivo, tipo o bien" aria-label="Buscar documentos">
    <button class="primary-button" type="submit">Buscar</button>
</form>

<div class="paper-card mt-6 divide-y divide-[#edf0ea]">
    @forelse($documents as $document)
        <div class="list-row">
            <span class="grid h-12 w-12 flex-none place-items-center rounded-xl {{ $document->is_important ? 'bg-[#fff0d9] text-[#a25d1f]' : 'bg-[#eef3ed] text-[#51715f]' }}" aria-hidden="true">{{ $document->is_important ? '★' : '▤' }}</span>
            <div class="min-w-0 flex-1">
                <a target="_blank" rel="noopener" class="block truncate text-lg font-bold hover:text-[#2f6655]" href="{{ route('documents.view', $document) }}">{{ $document->title }}</a>
                <p class="mt-1 text-sm muted">{{ $document->asset?->name ?? 'Pendiente de organizar' }} · {{ $document->type }} · {{ $document->original_name }}</p>
            </div>
            <div class="list-row-actions">
                <a class="quiet-button" href="{{ route('documents.edit', $document) }}">Editar</a>
                <a class="soft-button" href="{{ route('documents.download', $document) }}">Descargar</a>
            </div>
        </div>
    @empty
        <div class="p-10 text-center">
            <div class="text-5xl" aria-hidden="true">▤</div>
            <h2 class="mt-3 text-xl font-bold">No hay documentos que mostrar</h2>
            <p class="mt-2 muted">Añade el primero desde la ficha de un bien.</p>
            <a class="primary-button mt-5" href="{{ route('assets.index') }}">Ir a Mis bienes</a>
        </div>
    @endforelse
</div>
@endsection
