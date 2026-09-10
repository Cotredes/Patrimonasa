@extends('layouts.app')

@section('content')
@include('partials.back', ['fallback' => route('assets.index'), 'label' => 'Volver a Mis bienes'])

<div class="page-head">
    <div class="eyebrow">{{ $asset->category->name }}</div>
    <div class="flex flex-wrap gap-2">
        <form method="POST" action="{{ route('assets.favorite', $asset) }}">@csrf<button class="soft-button" type="submit">{{ $asset->is_favorite ? '★ Favorito' : '☆ Favorito' }}</button></form>
        <a class="soft-button" href="{{ route('assets.edit', $asset) }}">Editar información</a>
    </div>
</div>

<section class="mt-5 overflow-hidden rounded-3xl bg-[#e8f0e9]">
    <div class="grid lg:grid-cols-[.8fr_1.2fr]">
        @if($asset->coverPhoto)
            <img class="h-64 w-full object-cover lg:h-full lg:min-h-80" src="{{ route('photos.view', $asset->coverPhoto) }}" alt="Fotografía de {{ $asset->name }}">
        @else
            <div class="grid min-h-64 place-items-center bg-[#dcebdd] text-8xl text-[#9bb6a2]" aria-hidden="true">{{ $asset->category->icon === 'car' ? '🚗' : '⌂' }}</div>
        @endif
        <div class="p-7 sm:p-10">
            <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ $asset->name }}</h1>
            @if($asset->description)<p class="mt-4 max-w-2xl text-lg muted">{{ $asset->description }}</p>@endif
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="primary-button" href="#documentos">+ Añadir documento</a>
                <a class="soft-button" href="#fotos">+ Añadir fotos</a>
                @if(!empty($asset->details['map_url']))<a class="soft-button" href="{{ $asset->details['map_url'] }}" target="_blank" rel="noopener">Ver en Google Maps</a>@endif
            </div>
        </div>
    </div>
</section>

<div class="mt-8 grid gap-8 lg:grid-cols-[1.15fr_.85fr]">
    <section id="documentos" aria-label="Documentos del bien" class="scroll-mt-24">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <div class="eyebrow">Lo más importante</div>
                <h2 class="mt-1 text-2xl font-bold">Documentos <span class="text-base font-normal muted">({{ $asset->documents->count() }})</span></h2>
            </div>
            <a class="quiet-button" href="{{ route('assets.export', $asset) }}">↓ Exportar</a>
        </div>

        <div class="paper-card mt-4 divide-y divide-[#edf0ea]">
            @forelse($asset->documents as $document)
                <div class="list-row">
                    <span class="grid h-11 w-11 flex-none place-items-center rounded-xl {{ $document->is_important ? 'bg-[#fff0d9] text-[#a25d1f]' : 'bg-[#eef3ed] text-[#51715f]' }}" aria-hidden="true">{{ $document->is_important ? '★' : '▤' }}</span>
                    <div class="min-w-0 flex-1">
                        <a class="block truncate font-bold hover:text-[#2f6655]" href="{{ route('documents.view', $document) }}" target="_blank" rel="noopener">{{ $document->title }}</a>
                        <p class="mt-0.5 text-sm muted">{{ $document->type }} · {{ number_format($document->size / 1024 / 1024, 1) }} MB @if($document->expires_at) · vence {{ $document->expires_at->format('d/m/Y') }} @endif</p>
                    </div>
                    <div class="list-row-actions">
                        <a class="quiet-button" href="{{ route('documents.edit', $document) }}">Editar</a>
                        <a class="quiet-button" href="{{ route('documents.download', $document) }}">Descargar</a>
                        <form method="POST" action="{{ route('documents.trash', $document) }}">@csrf @method('DELETE')<button class="quiet-button danger-button" type="submit">Papelera</button></form>
                    </div>
                </div>
            @empty
                <div class="p-7 text-center">
                    <div class="text-4xl" aria-hidden="true">▤</div>
                    <p class="mt-2 font-semibold">Aquí aparecerán sus papeles</p>
                    <p class="mt-1 text-sm muted">Escrituras, seguros, manuales y todo lo relacionado con este bien.</p>
                </div>
            @endforelse
        </div>

        <form method="POST" action="{{ route('documents.store', $asset) }}" enctype="multipart/form-data" class="paper-card mt-4 p-5 sm:p-6">
            @csrf
            <h3 class="text-lg font-bold">Añadir documentos</h3>
            <p class="mt-1 text-sm muted">Puedes seleccionar varios archivos. También funciona con fotos hechas desde el móvil.</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <label class="sm:col-span-2"><span class="label">Archivos *</span><input class="field" type="file" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.txt" required></label>
                <label><span class="label">Título común (opcional)</span><input class="field" name="title" placeholder="Ej.: Escrituras"></label>
                <label><span class="label">Tipo</span>
                    <input class="field" name="type" list="document-types" placeholder="Ej.: Escritura">
                    <datalist id="document-types"><option>Escritura</option><option>Seguro</option><option>ITV</option><option>Plano</option><option>Manual</option><option>Certificado</option><option>Otro documento</option></datalist>
                </label>
            </div>
            <label class="mt-4 flex items-center gap-3"><input class="h-6 w-6 accent-[#2f6655]" type="checkbox" name="is_important" value="1"><span class="font-semibold">Marcar como importante</span></label>
            <button class="primary-button mt-4 w-full sm:w-auto" type="submit">Guardar documentos</button>
        </form>
    </section>

    <aside aria-label="Fotos e información">
        <div id="fotos" class="eyebrow scroll-mt-24">Para reconocerlo</div>
        <h2 class="mt-1 text-2xl font-bold">Fotografías</h2>

        @if($asset->photos->isEmpty())
            <div class="paper-card mt-4 p-5 text-sm muted">Todavía no hay fotos. Añade la primera para reconocer este bien de un vistazo.</div>
        @else
            <div class="mt-4 grid grid-cols-2 gap-3">
                @foreach($asset->photos as $photo)
                    <div class="overflow-hidden rounded-xl bg-[#eef3ed] ring-1 ring-[#e5e7df]">
                        <a href="{{ route('photos.view', $photo) }}" target="_blank" rel="noopener"><img class="aspect-square w-full object-cover" src="{{ route('photos.view', $photo) }}" alt="Fotografía de {{ $asset->name }}" loading="lazy"></a>
                        <div class="flex gap-1 p-2">
                            <form method="POST" action="{{ route('photos.cover', $photo) }}" class="flex-1">@csrf<button class="w-full rounded-lg bg-white px-2 py-2 text-xs font-bold ring-1 ring-[#e5e7df]" type="submit">{{ $photo->is_cover ? '★ Principal' : 'Principal' }}</button></form>
                            <form method="POST" action="{{ route('photos.delete', $photo) }}">@csrf @method('DELETE')<button class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-red-700 ring-1 ring-[#e5e7df]" type="submit" aria-label="Borrar fotografía">Borrar</button></form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('photos.store', $asset) }}" enctype="multipart/form-data" class="paper-card mt-4 p-5" data-auto-upload>
            @csrf
            <label><span class="label">Añadir fotos</span><input class="field" type="file" name="photos[]" multiple accept="image/*" required data-auto-upload-input></label>
            <p class="mt-2 text-sm muted">Se guardan solas en cuanto las eliges, sin pulsar nada más.</p>
            <p class="mt-2 text-sm muted" data-upload-status hidden>Subiendo fotos… no cierres esta página.</p>
            <noscript><button class="soft-button mt-4 w-full" type="submit">Guardar fotografías</button></noscript>
        </form>

        <div class="paper-card mt-6 p-5">
            <div class="eyebrow">Información</div>
            @if(empty($asset->details) && empty($asset->notes))
                <p class="mt-3 text-sm muted">Todavía no hay detalles. Usa «Editar información» para añadirlos poco a poco.</p>
            @else
                <dl class="mt-3 space-y-3">
                    @foreach($asset->details ?? [] as $key => $value)
                        @if($key === 'map_url') @continue @endif
                        <div class="flex justify-between gap-3 border-b border-[#edf0ea] pb-2">
                            <dt class="muted">{{ str_replace('_', ' ', ucfirst($key)) }}</dt>
                            <dd class="text-right font-semibold">{{ $value }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if($asset->notes)
                    <h3 class="mt-5 font-bold">Notas prácticas</h3>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6">{{ $asset->notes }}</p>
                @endif
            @endif
        </div>
    </aside>
</div>

<section class="mt-10 border-t border-[#e5e7df] pt-6" aria-label="Acciones delicadas">
    <p class="text-sm muted">Zona tranquila: archivar conserva todo, la papelera permite restaurar.</p>
    <div class="mt-3 flex flex-wrap gap-3">
        <form method="POST" action="{{ route('assets.archive', $asset) }}">@csrf<button class="quiet-button ring-1 ring-[#e5e7df]" type="submit">Archivar este bien</button></form>
        <form method="POST" action="{{ route('assets.trash', $asset) }}">@csrf @method('DELETE')<button class="quiet-button danger-button ring-1 ring-[#e9c4b9]" type="submit">Enviar a la papelera</button></form>
    </div>
</section>
@endsection
