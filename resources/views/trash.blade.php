@extends('layouts.app')

@section('content')
@include('partials.back', ['fallback' => route('home'), 'label' => 'Volver al inicio'])

<div class="eyebrow">Con calma, nada se pierde</div>
<h1 class="mt-2 text-4xl font-bold">Papelera</h1>
<p class="mt-2 max-w-2xl muted">Los bienes y documentos permanecen aquí hasta que decidáis restaurarlos. El borrado definitivo solo lo hace la persona administradora.</p>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <section aria-label="Bienes retirados">
        <h2 class="text-2xl font-bold">Bienes retirados</h2>
        <div class="paper-card mt-4 divide-y divide-[#edf0ea]">
            @forelse($assets as $asset)
                <div class="list-row">
                    <div class="min-w-0 flex-1">
                        <strong class="block truncate">{{ $asset->name }}</strong>
                        <p class="text-sm muted">{{ $asset->category->name ?? 'Sin categoría' }}</p>
                    </div>
                    <div class="list-row-actions">
                        <form method="POST" action="{{ route('trash.asset.restore', $asset->id) }}">@csrf<button class="soft-button" type="submit">Restaurar</button></form>
                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('trash.asset.force', $asset->id) }}">@csrf @method('DELETE')<button class="quiet-button danger-button" type="submit">Borrar definitivamente</button></form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-7 muted">No hay bienes en la papelera.</div>
            @endforelse
        </div>
    </section>

    <section aria-label="Documentos retirados">
        <h2 class="text-2xl font-bold">Documentos retirados</h2>
        <div class="paper-card mt-4 divide-y divide-[#edf0ea]">
            @forelse($documents as $document)
                <div class="list-row">
                    <div class="min-w-0 flex-1">
                        <strong class="block truncate">{{ $document->title }}</strong>
                        <p class="text-sm muted">{{ $document->asset?->name ?? 'Sin bien' }}</p>
                    </div>
                    <div class="list-row-actions">
                        <form method="POST" action="{{ route('trash.document.restore', $document->id) }}">@csrf<button class="soft-button" type="submit">Restaurar</button></form>
                    </div>
                </div>
            @empty
                <div class="p-7 muted">No hay documentos en la papelera.</div>
            @endforelse
        </div>
    </section>
</div>
@endsection
