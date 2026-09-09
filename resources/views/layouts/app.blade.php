<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#2f6655"><meta name="description" content="Tu archivo familiar de bienes y documentos">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}"><title>{{ $title ?? 'Patrimonasa' }} · Patrimonasa</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
<a href="#contenido" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:font-bold">Saltar al contenido</a>
<header class="border-b border-[#e5e7df] bg-white/90 backdrop-blur sticky top-0 z-30">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
        <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Ir al inicio">
            <span class="grid h-11 w-11 place-items-center rounded-2xl bg-[#2f6655] text-2xl text-white" aria-hidden="true">&#9000;</span>
            <span><strong class="block text-xl tracking-tight">Patrimonasa</strong><small class="muted hidden sm:block">El archivo de nuestra familia</small></span>
        </a>
        @auth
        <div class="flex items-center gap-2 sm:gap-4">
            <span class="hidden text-sm font-semibold text-[#52675e] md:block">Hola, {{ Str::before(auth()->user()->name, ' ') }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="quiet-button" type="submit">Salir</button></form>
        </div>
        @endauth
    </div>
</header>
@auth
<nav class="border-b border-[#e5e7df] bg-[#f4f6f0]" aria-label="Navegación principal">
    <div class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-4 py-2 sm:px-6">
        <a href="{{ route('home') }}" class="whitespace-nowrap rounded-lg px-3 py-2 text-[.95rem] font-semibold {{ request()->routeIs('home') ? 'bg-white text-[#2f6655] shadow-sm' : 'text-[#52675e] hover:bg-white' }}">&#9000; Inicio</a>
        <a href="{{ route('assets.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 text-[.95rem] font-semibold {{ request()->routeIs('assets.*') ? 'bg-white text-[#2f6655] shadow-sm' : 'text-[#52675e] hover:bg-white' }}">Mis bienes</a>
        <a href="{{ route('documents.all') }}" class="whitespace-nowrap rounded-lg px-3 py-2 text-[.95rem] font-semibold {{ request()->routeIs('documents.*') ? 'bg-white text-[#2f6655] shadow-sm' : 'text-[#52675e] hover:bg-white' }}">Documentos</a>
        <a href="{{ route('reminders.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 text-[.95rem] font-semibold {{ request()->routeIs('reminders.*') ? 'bg-white text-[#2f6655] shadow-sm' : 'text-[#52675e] hover:bg-white' }}">Recordatorios</a>
        <a href="{{ route('categories.index') }}" class="whitespace-nowrap rounded-lg px-3 py-2 text-[.95rem] font-semibold {{ request()->routeIs('categories.*') ? 'bg-white text-[#2f6655] shadow-sm' : 'text-[#52675e] hover:bg-white' }}">Organización</a>
        <a href="{{ route('trash.index') }}" class="ml-auto whitespace-nowrap rounded-lg px-3 py-2 text-[.95rem] font-semibold {{ request()->routeIs('trash.*') ? 'bg-white text-[#2f6655] shadow-sm' : 'text-[#52675e] hover:bg-white' }}">Papelera</a>
    </div>
</nav>
@endauth
<main id="contenido" class="mx-auto max-w-7xl px-4 py-7 sm:px-6 sm:py-10">@include('partials.flash') @yield('content')</main>
<footer class="mx-auto max-w-7xl px-4 pb-10 pt-2 sm:px-6">
    <div class="signature-card mx-auto max-w-2xl text-center" aria-label="Patrimonasa está hecho por Ángel Muñoz">
        <span class="signature-spark" aria-hidden="true">✦</span>
        <p class="signature-text">Hecho por Ángel Muñoz con mucho cariño</p>
        <span class="signature-spark" aria-hidden="true">✦</span>
    </div>
    <p class="mt-4 text-center text-sm muted">Patrimonasa · Un lugar tranquilo para lo que importa.</p>
</footer>
</body></html>
