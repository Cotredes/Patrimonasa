@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-2xl">
    <div class="eyebrow">Diagnóstico temporal</div>
    <h1 class="mt-2 text-3xl font-bold">Sesión y CSRF</h1>
    <p class="mt-2 muted">Visita esta página dos veces seguidas con el mismo navegador. La segunda vez, «La sesión anterior sigue guardada» debe decir que sí. No se muestra aquí ningún secreto.</p>

    <dl class="paper-card mt-6 divide-y divide-[#edf0ea]">
        <div class="flex justify-between gap-3 p-4"><dt class="muted">Esquema que detecta la aplicación</dt><dd class="font-bold">{{ $esquema }}</dd></div>
        <div class="flex justify-between gap-3 p-4"><dt class="muted">Anfitrión que detecta</dt><dd class="font-bold">{{ $anfitrion }}</dd></div>
        <div class="flex justify-between gap-3 p-4"><dt class="muted">Conexión considerada segura</dt><dd class="font-bold">{{ $esSegura ? 'Sí' : 'No' }}</dd></div>
        <div class="flex justify-between gap-3 p-4"><dt class="muted">Destino del formulario de entrada</dt><dd class="break-all text-right font-bold">{{ $accionLogin }}</dd></div>
        <div class="flex justify-between gap-3 p-4"><dt class="muted">Cookie «{{ $nombreCookie }}» recibida</dt><dd class="font-bold">{{ $cookieRecibida ? 'Sí' : 'No' }}</dd></div>
        <div class="flex justify-between gap-3 p-4"><dt class="muted">La sesión tiene token</dt><dd class="font-bold">{{ $sesionTieneToken ? 'Sí' : 'No' }}</dd></div>
        <div class="flex justify-between gap-3 p-4"><dt class="muted">La sesión anterior sigue guardada</dt><dd class="font-bold">{{ $marcaPersistio ? 'Sí' : 'No (primera visita)' }}</dd></div>
        <div class="flex justify-between gap-3 p-4"><dt class="muted">El proxy anuncia el protocolo</dt><dd class="font-bold">{{ $protoReenviado ? 'Sí' : 'No' }}</dd></div>
    </dl>

    <p class="mt-4 text-sm muted">Página temporal de diagnóstico: se retirará cuando el acceso vuelva a funcionar.</p>
</div>
@endsection
