<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

class LoginCsrfTest extends TestCase
{
    use RefreshDatabase;

    private function csrfTokenFrom(string $html): ?string
    {
        if (preg_match('/name="_token"\s+value="([^"]+)"/', $html, $m)) {
            return $m[1];
        }

        return null;
    }

    /** Simula que el navegador retiene las cookies (las seguras no viajan por HTTP). */
    private function olvidarCookies(): void
    {
        foreach (['defaultCookies', 'unencryptedCookies'] as $propiedad) {
            $prop = new ReflectionProperty($this, $propiedad);
            $prop->setAccessible(true);
            $prop->setValue($this, []);
        }
    }

    /** Vacía la memoria de la sesión para simular peticiones de procesos
     *  distintos, como hace php-fpm en producción (en los tests el
     *  almacén se comparte y falsearía la prueba). */
    private function procesoNuevo(): void
    {
        $this->app['session']->driver()->flush();
        $this->olvidarCookies();
    }

    private function galletaDeSesion(TestResponse $respuesta): ?Cookie
    {
        foreach ($respuesta->headers->getCookies() as $galleta) {
            if ($galleta->getName() === config('session.cookie')) {
                return $galleta;
            }
        }

        return null;
    }

    public function test_login_form_uses_https_behind_plain_http_proxy(): void
    {
        config()->set('app.url', 'https://patrimonio.casetashormigon.es');
        $this->app->register(AppServiceProvider::class, true);

        // El backend recibe HTTP (proxy sin cabeceras de confianza).
        $html = $this->get('http://patrimonio.casetashormigon.es/entrar')->assertOk()->getContent();

        $this->assertStringContainsString('action="https://patrimonio.casetashormigon.es/entrar"', $html);
        $this->assertDoesNotMatchRegularExpression('#(src|href)="http://#', $html);
    }

    public function test_session_cookie_is_secure_and_lax_when_configured(): void
    {
        config()->set('session.cookie', 'patrimonasa_session_v2');
        config()->set('session.secure', true);

        $cookies = [];
        foreach ($this->get('/entrar')->assertOk()->headers->getCookies() as $cookie) {
            $cookies[$cookie->getName()] = $cookie;
        }

        $sesion = $cookies['patrimonasa_session_v2'] ?? null;
        $this->assertNotNull($sesion, 'La respuesta debe crear la cookie de sesión.');
        $this->assertTrue($sesion->isSecure(), 'La cookie de sesión debe llevar el aviso Secure.');
        $this->assertSame('lax', strtolower((string) $sesion->getSameSite()));
    }

    public function test_login_rejects_post_with_valid_token_but_missing_session_cookie(): void
    {
        // Laravel omite CSRF en los tests; con otro entorno se aplica de verdad.
        $this->app->instance('env', 'local');

        $user = User::factory()->create(['password' => Hash::make('Secreta123')]);

        $respuesta = $this->get('/entrar')->assertOk();
        $token = $this->csrfTokenFrom($respuesta->getContent());
        $this->assertNotEmpty($token, 'El formulario debe incluir el token CSRF.');

        $galleta = $this->galletaDeSesion($respuesta);
        $this->assertNotNull($galleta, 'La respuesta debe crear la cookie de sesión.');

        // Control: token + cookie de sesión → entra (la protección está activa
        // y la sesión se recupera del almacenamiento, no de la memoria).
        $this->procesoNuevo();
        $this->withUnencryptedCookie($galleta->getName(), $galleta->getValue())
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'Secreta123', '_token' => $token])
            ->assertRedirect(route('home'));

        // Caso 419: navegador nuevo sin cookies pero con el token del formulario
        // (el navegador retiene la cookie segura en HTTP) → sesión nueva y
        // vacía en el servidor → 419.
        $this->app['auth']->logout();
        $this->procesoNuevo();
        $nuevoToken = $this->csrfTokenFrom($this->get('/entrar')->assertOk()->getContent());
        $this->assertNotEmpty($nuevoToken);
        $this->procesoNuevo();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'Secreta123', '_token' => $nuevoToken])
            ->assertStatus(419);
    }
}
