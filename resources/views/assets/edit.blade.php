@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl">
    @include('partials.back', ['fallback' => route('assets.show', $asset), 'label' => 'Volver a ' . $asset->name])

    <div class="eyebrow">Información del bien</div>
    <h1 class="mt-2 text-4xl font-bold">Editar {{ $asset->name }}</h1>
    <p class="mt-2 muted">Solo completa lo que sepas. Nada de esto es obligatorio salvo el nombre y la categoría.</p>

    <form method="POST" action="{{ route('assets.update', $asset) }}" class="paper-card mt-8 space-y-7 p-6 sm:p-9">
        @csrf @method('PUT')

        <div class="grid gap-5 sm:grid-cols-2">
            <label><span class="label">Nombre habitual *</span><input class="field" name="name" value="{{ old('name', $asset->name) }}" required></label>
            <label><span class="label">Categoría *</span>
                <select class="field" name="category_id" required>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $asset->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>

        <label><span class="label">Descripción</span><textarea class="field min-h-28" name="description" placeholder="Qué es o qué debemos recordar">{{ old('description', $asset->description) }}</textarea></label>

        <div>
            <h2 class="text-xl font-bold">Datos habituales</h2>
            <p class="mt-1 text-sm muted">Deja en blanco lo que no corresponda.</p>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <label><span class="label">Dirección o ubicación</span><input class="field" name="address" value="{{ old('address', $asset->details['address'] ?? '') }}"></label>
            <label><span class="label">Localidad</span><input class="field" name="town" value="{{ old('town', $asset->details['town'] ?? '') }}"></label>
            <label><span class="label">Provincia</span><input class="field" name="province" value="{{ old('province', $asset->details['province'] ?? '') }}"></label>
            <label><span class="label">Código postal</span><input class="field" name="postal_code" value="{{ old('postal_code', $asset->details['postal_code'] ?? '') }}"></label>
            <label><span class="label">Titular</span><input class="field" name="holder" value="{{ old('holder', $asset->details['holder'] ?? '') }}"></label>
            <label><span class="label">Superficie y unidad</span><input class="field" name="surface" placeholder="Ej.: 120 m²" value="{{ old('surface', $asset->details['surface'] ?? '') }}"></label>
            <label><span class="label">Marca</span><input class="field" name="brand" value="{{ old('brand', $asset->details['brand'] ?? '') }}"></label>
            <label><span class="label">Modelo</span><input class="field" name="model" value="{{ old('model', $asset->details['model'] ?? '') }}"></label>
            <label><span class="label">Matrícula</span><input class="field" name="registration" value="{{ old('registration', $asset->details['registration'] ?? '') }}"></label>
            <label><span class="label">Número de bastidor / serie</span><input class="field" name="vin" value="{{ old('vin', $asset->details['vin'] ?? '') }}"></label>
            <label><span class="label">Lugar donde está</span><input class="field" name="location" value="{{ old('location', $asset->details['location'] ?? '') }}"></label>
            <label><span class="label">Referencia catastral / parcela</span><input class="field" name="cadastral_reference" value="{{ old('cadastral_reference', $asset->details['cadastral_reference'] ?? '') }}"></label>
        </div>

        <details class="rounded-xl bg-[#f4f6f0] p-4">
            <summary class="cursor-pointer rounded-lg p-2 font-bold">Más datos opcionales</summary>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <label><span class="label">Fecha de adquisición</span><input class="field" type="date" name="acquired_at" value="{{ old('acquired_at', $asset->details['acquired_at'] ?? '') }}"></label>
                <label><span class="label">Año</span><input class="field" type="number" name="year" min="1900" max="2100" value="{{ old('year', $asset->details['year'] ?? '') }}"></label>
                <label><span class="label">Polígono y parcela</span><input class="field" name="plot" value="{{ old('plot', $asset->details['plot'] ?? '') }}"></label>
                <label><span class="label">Tipo o uso del terreno</span><input class="field" name="land_use" value="{{ old('land_use', $asset->details['land_use'] ?? '') }}"></label>
                <label class="sm:col-span-2"><span class="label">Enlace para consultar el mapa</span><input class="field" type="url" name="map_url" value="{{ old('map_url', $asset->details['map_url'] ?? '') }}"></label>
            </div>
        </details>

        <label><span class="label">Notas prácticas</span><textarea class="field min-h-32" name="notes" placeholder="Las escrituras originales están en la carpeta azul...">{{ old('notes', $asset->notes) }}</textarea></label>

        <div class="flex flex-wrap justify-end gap-3">
            <a class="soft-button" href="{{ route('assets.show', $asset) }}">Cancelar</a>
            <button class="primary-button" type="submit">Guardar información</button>
        </div>
    </form>
</div>
@endsection
