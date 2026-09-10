<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Detrás del proxy de Loading, PHP recibe la petición en HTTP aunque el
     * visitante use HTTPS. Sin forzar el esquema, route()/asset() generarían
     * direcciones http://: el navegador enviaría el formulario a HTTP y no
     * adjuntaría la cookie de sesión (marcada como segura), de modo que cada
     * envío crearía una sesión vacía y el login fallaría con error 419.
     * Se fuerza https solo cuando APP_URL ya es https; no se confía en
     * ninguna cabecera del proxy para decidirlo.
     */
    public function boot(): void
    {
        if (parse_url((string) config('app.url'), PHP_URL_SCHEME) === 'https') {
            URL::forceScheme('https');
        }
    }
}
