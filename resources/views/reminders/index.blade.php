@extends('layouts.app')

@section('content')
@include('partials.back', ['fallback' => route('home'), 'label' => 'Volver al inicio'])

<div class="eyebrow">Fechas importantes</div>
<h1 class="mt-2 text-4xl font-bold">Recordatorios</h1>
<p class="mt-2 max-w-2xl muted">Solo una ayuda para recordar fechas. Patrimonasa no cambia el estado de ningún bien por su cuenta.</p>

<div class="mt-8 grid gap-6 lg:grid-cols-[.85fr_1.15fr]">
    <form method="POST" action="{{ route('reminders.store') }}" class="paper-card h-fit p-6 sm:p-7">
        @csrf
        <h2 class="text-xl font-bold">Añadir recordatorio</h2>
        <label class="mt-5 block"><span class="label">Qué hay que recordar</span><input class="field" name="title" placeholder="Ej.: Próxima ITV" required></label>
        <label class="mt-4 block"><span class="label">Fecha</span><input class="field" type="date" name="due_date" required></label>
        <label class="mt-4 block"><span class="label">Bien relacionado (opcional)</span>
            <select class="field" name="asset_id">
                <option value="">Sin asignar</option>
                @foreach($assets as $asset)<option value="{{ $asset->id }}">{{ $asset->name }}</option>@endforeach
            </select>
        </label>
        <button class="primary-button mt-5 w-full" type="submit">Guardar recordatorio</button>
    </form>

    <div class="paper-card divide-y divide-[#edf0ea]">
        @forelse($reminders as $reminder)
            <div class="list-row {{ $reminder->completed ? 'opacity-60' : '' }}">
                <div class="min-w-0 flex-1">
                    <strong class="block text-lg {{ $reminder->completed ? 'line-through' : '' }}">{{ $reminder->title }}</strong>
                    <p class="mt-1 text-sm {{ !$reminder->completed && $reminder->due_date->isPast() ? 'font-bold text-[#a84d38]' : 'muted' }}">{{ $reminder->due_date->translatedFormat('j \d\e F \d\e Y') }} @if($reminder->asset) · {{ $reminder->asset->name }} @endif</p>
                </div>
                <div class="list-row-actions">
                    <form method="POST" action="{{ route('reminders.toggle', $reminder) }}">@csrf<button class="soft-button" type="submit">{{ $reminder->completed ? 'Reabrir' : 'Completar' }}</button></form>
                    <form method="POST" action="{{ route('reminders.delete', $reminder) }}">@csrf @method('DELETE')<button class="quiet-button danger-button" type="submit">Borrar</button></form>
                </div>
            </div>
        @empty
            <div class="p-10 text-center">
                <div class="text-5xl" aria-hidden="true">◷</div>
                <h2 class="mt-3 text-xl font-bold">No hay recordatorios</h2>
                <p class="mt-2 muted">Cuando haya una fecha importante, la puedes dejar aquí.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
