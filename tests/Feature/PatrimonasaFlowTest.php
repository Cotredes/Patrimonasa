<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PatrimonasaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_family_member_can_create_asset_and_upload_document(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Viviendas', 'icon' => 'home', 'position' => 0]);

        $this->actingAs($user)->post(route('assets.store'), ['name' => 'Casa del pueblo', 'category_id' => $category->id])->assertRedirect();
        $asset = Asset::firstOrFail();

        $this->actingAs($user)->post(route('documents.store', $asset), ['files' => [UploadedFile::fake()->create('escrituras.pdf', 120, 'application/pdf')], 'title' => 'Escrituras', 'type' => 'Escritura', 'is_important' => '1'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('assets', ['name' => 'Casa del pueblo']);
        $this->assertDatabaseHas('documents', ['title' => 'Escrituras', 'asset_id' => $asset->id, 'is_important' => 1]);
        Storage::disk('local')->assertExists(Document::firstOrFail()->path);
    }

    public function test_asset_can_be_sent_to_trash_and_restored(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Otros bienes', 'icon' => 'archive', 'position' => 0]);
        $asset = Asset::create(['category_id' => $category->id, 'name' => 'Herramienta familiar', 'slug' => 'herramienta-familiar']);

        $this->actingAs($user)->delete(route('assets.trash', $asset))->assertRedirect(route('assets.index'));
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
        $this->actingAs($user)->post(route('trash.asset.restore', $asset->id))->assertRedirect();
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'deleted_at' => null]);
    }

    public function test_family_member_can_upload_photos(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Viviendas', 'icon' => 'home', 'position' => 0]);
        $asset = Asset::create(['category_id' => $category->id, 'name' => 'Casa del pueblo', 'slug' => 'casa-del-pueblo']);

        $this->actingAs($user)->post(route('photos.store', $asset), [
            'photos' => [UploadedFile::fake()->image('casa.jpg'), UploadedFile::fake()->image('jardin.png')],
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertSame(2, $asset->photos()->count());
        foreach ($asset->photos as $photo) {
            Storage::disk('local')->assertExists($photo->path);
        }
    }

    public function test_asset_shows_maps_link_auto_upload_and_arrow_back(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Viviendas', 'icon' => 'home', 'position' => 0]);
        $asset = Asset::create([
            'category_id' => $category->id,
            'name' => 'Casa del pueblo',
            'slug' => 'casa-del-pueblo',
            'details' => ['map_url' => 'https://maps.google.com/?q=Casa'],
        ]);
        $sinMapa = Asset::create([
            'category_id' => $category->id,
            'name' => 'Piso sin mapa',
            'slug' => 'piso-sin-mapa',
        ]);

        $html = $this->actingAs($user)->get(route('assets.show', $asset))->assertOk()->getContent();
        $this->assertStringContainsString('Ver en Google Maps', $html);
        $this->assertStringContainsString('https://maps.google.com/?q=Casa', $html);
        $this->assertStringContainsString('data-auto-upload', $html);
        $this->assertStringContainsString('<noscript>', $html);
        $this->assertStringContainsString('aria-label="Volver a Mis bienes"', $html);
        $this->assertStringNotContainsString('>Volver a Mis bienes<', $html);

        $htmlSinMapa = $this->actingAs($user)->get(route('assets.show', $sinMapa))->assertOk()->getContent();
        $this->assertStringNotContainsString('Ver en Google Maps', $htmlSinMapa);
    }

    public function test_public_registration_does_not_exist(): void
    {
        $this->get('/crear-cuenta')->assertNotFound();
        $this->post('/crear-cuenta', [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'secreta123',
            'password_confirmation' => 'secreta123',
        ])->assertNotFound();

        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
    }

    public function test_admin_account_is_created_with_private_command(): void
    {
        $this->artisan('patrimonasa:crear-usuario', [
            'email' => 'admin@familia.es',
            '--nombre' => 'Admin',
            '--password' => 'secreta123',
        ])->assertSuccessful();

        $admin = User::where('email', 'admin@familia.es')->firstOrFail();
        $this->assertSame('admin', $admin->role);
        $this->assertTrue($this->app['hash']->check('secreta123', $admin->password));

        $this->post(route('login.store'), [
            'email' => 'admin@familia.es',
            'password' => 'secreta123',
        ])->assertRedirect(route('home'));
    }
}
