@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl">
    @include('partials.back', ['fallback' => $document->asset ? route('assets.show', $document->asset) : route('documents.all'), 'label' => $document->asset ? 'Volver a ' . $document->asset->name : 'Volver a Documentos'])

    <div class="eyebrow">Datos del documento</div>
    <h1 class="mt-2 text-4xl font-bold">Editar documento</h1>
    <p class="mt-2 muted">El archivo original se conserva sin cambios.</p>

    <form method="POST" action="{{ route('documents.update', $document) }}" class="paper-card mt-8 space-y-5 p-6 sm:p-9">
        @csrf @method('PUT')
        <label><span class="label">Título</span><input class="field" name="title" value="{{ old('title', $document->title) }}" required></label>
        <label><span class="label">Bien al que pertenece</span>
            <select class="field" name="asset_id">
                <option value="">Pendiente de organizar</option>
                @foreach($assets as $asset)
                    <option value="{{ $asset->id }}" @selected(old('asset_id', $document->asset_id) == $asset->id)>{{ $asset->name }}</option>
                @endforeach
            </select>
        </label>
        <label><span class="label">Tipo de documento</span><input class="field" name="type" value="{{ old('type', $document->type) }}" required></label>
        <div class="grid gap-5 sm:grid-cols-2">
            <label><span class="label">Fecha del documento</span><input class="field" type="date" name="document_date" value="{{ old('document_date', optional($document->document_date)->format('Y-m-d')) }}"></label>
            <label><span class="label">Fecha de vencimiento</span><input class="field" type="date" name="expires_at" value="{{ old('expires_at', optional($document->expires_at)->format('Y-m-d')) }}"></label>
        </div>
        <label><span class="label">Notas</span><textarea class="field min-h-28" name="notes">{{ old('notes', $document->notes) }}</textarea></label>
        <label class="flex items-center gap-3"><input class="h-6 w-6 accent-[#2f6655]" type="checkbox" name="is_important" value="1" @checked(old('is_important', $document->is_important))><span class="font-semibold">Marcar como importante</span></label>
        <div class="flex flex-wrap gap-3">
            <a class="soft-button flex-1" href="{{ $document->asset ? route('assets.show', $document->asset) : route('documents.all') }}">Cancelar</a>
            <button class="primary-button flex-1" type="submit">Guardar cambios</button>
        </div>
    </form>
</div>
@endsection
