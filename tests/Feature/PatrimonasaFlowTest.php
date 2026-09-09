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

    public function test_registration_closes_after_first_account(): void
    {
        $this->get(route('register'))->assertOk();

        $this->post(route('register.store'), [
            'name' => 'Primera cuenta',
            'email' => 'primera@familia.es',
            'password' => 'secreta123',
            'password_confirmation' => 'secreta123',
        ])->assertRedirect(route('home'));

        $this->assertEquals('admin', User::where('email', 'primera@familia.es')->firstOrFail()->role);

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->get(route('register'))->assertRedirect(route('login'));

        $this->post(route('register.store'), [
            'name' => 'Intruso',
            'email' => 'intruso@example.com',
            'password' => 'secreta123',
            'password_confirmation' => 'secreta123',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('users', ['email' => 'intruso@example.com']);
    }
}
