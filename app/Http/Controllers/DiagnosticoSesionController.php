<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DiagnosticoSesionController extends Controller
{
    /**
     * Diagnóstico TEMPORAL del error 419 en producción.
     *
     * Solo responde cuando la URL trae ?t= igual al DIAGNOSTIC_TOKEN del
     * .env del servidor; en cualquier otro caso devuelve 404. No muestra
     * ni guarda contraseñas, cookies, tokens ni contenidos de sesión:
     * solo indica con sí/no lo necesario para distinguir si la cookie
     * llega, si la sesión persiste y qué esquema detecta la aplicación.
     *
     * Para retirarlo: borra DIAGNOSTIC_TOKEN del .env (queda desactivado
     * al instante) y elimina este controlador, su vista y su ruta.
     */
    public function show(Request $request)
    {
        // Se lee con env() a propósito: si existiera una vieja caché de
        // configuración con otras rutas, esto mismo ya daría 404 y avisaría.
        $esperado = (string) env('DIAGNOSTIC_TOKEN', '');

        if ($esperado === '' || ! hash_equals($esperado, (string) $request->query('t'))) {
            abort(404);
        }

        $nombreCookie = (string) config('session.cookie');
        $persistia = $request->session()->has('_diag_marca');
        $request->session()->put('_diag_marca', now()->toIso8601String());

        return response()->view('diagnostico', [
            'esquema' => $request->getScheme(),
            'anfitrion' => $request->getHost(),
            'esSegura' => $request->isSecure(),
            'accionLogin' => route('login.store'),
            'nombreCookie' => $nombreCookie,
            'cookieRecibida' => $request->cookies->has($nombreCookie),
            'sesionTieneToken' => is_string($request->session()->token()),
            'marcaPersistio' => $persistia,
            'protoReenviado' => $request->headers->has('X-Forwarded-Proto'),
        ]);
    }
}
