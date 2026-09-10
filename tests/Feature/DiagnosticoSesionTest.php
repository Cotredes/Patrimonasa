<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiagnosticoSesionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        putenv('DIAGNOSTIC_TOKEN');
        parent::tearDown();
    }

    public function test_diagnostic_is_disabled_without_token(): void
    {
        putenv('DIAGNOSTIC_TOKEN');
        $this->get(route('diag.sesion'))->assertNotFound();
        $this->get(route('diag.sesion', ['t' => 'cualquiera']))->assertNotFound();
    }

    public function test_diagnostic_shows_session_state_without_secrets(): void
    {
        putenv('DIAGNOSTIC_TOKEN=secreto-test');

        $primera = $this->get(route('diag.sesion', ['t' => 'secreto-test']))->assertOk();
        $primera->assertSee('La sesión anterior sigue guardada');
        $this->assertStringNotContainsString('secreto-test', $primera->getContent(), 'El token de acceso no debe aparecer.');

        $segunda = $this->get(route('diag.sesion', ['t' => 'secreto-test']))->assertOk();
        $this->assertStringContainsString('Sí', $segunda->getContent());
        $this->assertStringNotContainsString((string) session()->token(), $segunda->getContent(), 'El token CSRF no debe aparecer.');
    }

    public function test_diagnostic_rejects_wrong_token(): void
    {
        putenv('DIAGNOSTIC_TOKEN=secreto-test');
        $this->get(route('diag.sesion', ['t' => 'otro']))->assertNotFound();
    }
}
