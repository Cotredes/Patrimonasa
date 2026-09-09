@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl">
    @include('partials.back', ['fallback' => route('assets.index'), 'label' => 'Volver a Mis bienes'])

    <div class="eyebrow">Un paso sencillo</div>
    <h1 class="mt-2 text-4xl font-bold">Añadir un bien</h1>
    <p class="mt-3 text-lg muted">Empieza por lo esencial. Podrás completar la información y añadir papeles después.</p>

    <form method="POST" action="{{ route('assets.store') }}" class="paper-card mt-8 space-y-7 p-6 sm:p-9">
        @csrf
        <label class="block">
            <span class="label">¿Cómo quieres llamarlo? <span class="text-[#b75d45]">*</span></span>
            <input class="field text-lg" name="name" value="{{ old('name') }}" placeholder="Por ejemplo: Casa del pueblo" autofocus required>
            <small class="mt-2 block muted">Usa un nombre que reconozcáis fácilmente.</small>
        </label>

        <fieldset>
            <legend class="label">¿Qué tipo de bien es? <span class="text-[#b75d45]">*</span></legend>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                @foreach($categories as $category)
                    <label class="cursor-pointer">
                        <input class="peer sr-only" type="radio" name="category_id" value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'checked' : '' }} required>
                        <span class="flex items-center gap-3 rounded-xl border border-[#dbe4db] p-4 font-semibold peer-checked:border-[#2f6655] peer-checked:bg-[#e8f0e9] peer-focus-visible:outline peer-focus-visible:outline-2 peer-focus-visible:outline-[#d39255]">
                            <span class="text-2xl" aria-hidden="true">▧</span>{{ $category->name }}
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="flex gap-3 mobile-stack">
            <a class="soft-button flex-1" href="{{ route('assets.index') }}">Cancelar</a>
            <button class="primary-button flex-1" type="submit">Crear bien</button>
        </div>
    </form>
</div>
@endsection
