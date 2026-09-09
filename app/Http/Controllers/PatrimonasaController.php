<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetPhoto;
use App\Models\Category;
use App\Models\Document;
use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class PatrimonasaController extends Controller
{
    public function login()
    {
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email'], 'password' => ['required']]);
        if (! Auth::attempt($data, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'El correo o la contraseña no son correctos.'])->withInput();
        }
        $request->session()->regenerate();

        return redirect()->intended(route('home'))->with('success', 'Has entrado en Patrimonasa.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function home(Request $request)
    {
        $this->seedCategories();
        $query = trim((string) $request->input('q'));
        $assets = Asset::with(['category', 'photos'])->whereNull('archived_at')->latest();
        $documents = Document::with('asset')->latest();
        if ($query !== '') {
            $like = '%'.str_replace(['%', '_'], ['\\%', '\\_'], $query).'%';
            $assets->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)->orWhere('description', 'like', $like)->orWhere('notes', 'like', $like)->orWhere('details', 'like', $like);
            });
            $documents->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)->orWhere('original_name', 'like', $like)->orWhere('notes', 'like', $like);
            });
        }

        return view('home', [
            'assets' => $assets->get(), 'documents' => $documents->get(), 'query' => $query,
            'categories' => Category::where('is_archived', false)->orderBy('position')->get(),
            'reminders' => Reminder::with('asset')->where('completed', false)->whereDate('due_date', '<=', now()->addDays(30))->orderBy('due_date')->take(5)->get(),
            'counts' => ['assets' => Asset::whereNull('archived_at')->count(), 'documents' => Document::count(), 'categories' => Category::where('is_archived', false)->count()],
        ]);
    }

    public function assets(Request $request)
    {
        $this->seedCategories();
        $category = $request->integer('category');
        $assets = Asset::with(['category', 'photos'])->whereNull('archived_at')->when($category, fn ($q) => $q->where('category_id', $category))->latest()->get();

        return view('assets.index', ['assets' => $assets, 'categories' => Category::where('is_archived', false)->orderBy('position')->get(), 'selectedCategory' => $category]);
    }

    public function documentsAll(Request $request)
    {
        $query = trim((string) $request->input('q'));
        $documents = Document::with(['asset.category'])->when($query, function ($q) use ($query) {
            $like = '%'.$query.'%';
            $q->where(fn ($inner) => $inner->where('title', 'like', $like)->orWhere('original_name', 'like', $like)->orWhere('type', 'like', $like)->orWhere('notes', 'like', $like)->orWhereHas('asset', fn ($asset) => $asset->where('name', 'like', $like)));
        })->orderByDesc('is_important')->latest()->get();

        return view('documents.index', compact('documents', 'query'));
    }

    public function createAsset()
    {
        $this->seedCategories();

        return view('assets.create', ['categories' => Category::where('is_archived', false)->orderBy('position')->get()]);
    }

    public function storeAsset(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'category_id' => ['required', 'exists:categories,id']]);
        $asset = Asset::create([...$data, 'slug' => Str::slug($data['name']).'-'.Str::lower(Str::random(5))]);

        return redirect()->route('assets.show', $asset)->with('success', "Los datos de {$asset->name} se han guardado.");
    }

    public function showAsset(Asset $asset)
    {
        abort_if($asset->trashed(), 404);
        $asset->load(['category', 'photos', 'documents' => fn ($q) => $q->orderByDesc('is_important')->latest(), 'reminders']);

        return view('assets.show', compact('asset'));
    }

    public function editAsset(Asset $asset)
    {
        return view('assets.edit', ['asset' => $asset, 'categories' => Category::where('is_archived', false)->orderBy('position')->get()]);
    }

    public function updateAsset(Request $request, Asset $asset)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:150'], 'category_id' => ['required', 'exists:categories,id'], 'description' => ['nullable', 'string'], 'notes' => ['nullable', 'string'], 'details' => ['nullable', 'string'], 'address' => ['nullable', 'string', 'max:255'], 'town' => ['nullable', 'string', 'max:120'], 'province' => ['nullable', 'string', 'max:120'], 'postal_code' => ['nullable', 'string', 'max:20'], 'map_url' => ['nullable', 'url', 'max:500'], 'cadastral_reference' => ['nullable', 'string', 'max:100'], 'surface' => ['nullable', 'string', 'max:80'], 'acquired_at' => ['nullable', 'date'], 'holder' => ['nullable', 'string', 'max:150'], 'brand' => ['nullable', 'string', 'max:100'], 'model' => ['nullable', 'string', 'max:100'], 'registration' => ['nullable', 'string', 'max:30'], 'vin' => ['nullable', 'string', 'max:80'], 'year' => ['nullable', 'integer', 'min:1900', 'max:2100'], 'color' => ['nullable', 'string', 'max:60'], 'location' => ['nullable', 'string', 'max:255'], 'plot' => ['nullable', 'string', 'max:100'], 'land_use' => ['nullable', 'string', 'max:120']]);
        $detailKeys = ['address', 'town', 'province', 'postal_code', 'map_url', 'cadastral_reference', 'surface', 'acquired_at', 'holder', 'brand', 'model', 'registration', 'vin', 'year', 'color', 'location', 'plot', 'land_use'];
        $details = collect($data)->only($detailKeys)->filter(fn ($v) => $v !== null && $v !== '')->all();
        $asset->update(['name' => $data['name'], 'category_id' => $data['category_id'], 'description' => $data['description'] ?? null, 'notes' => $data['notes'] ?? null, 'details' => $details]);

        return redirect()->route('assets.show', $asset)->with('success', "Los datos de {$asset->name} se han guardado.");
    }

    public function toggleFavorite(Asset $asset)
    {
        $asset->update(['is_favorite' => ! $asset->is_favorite]);

        return back()->with('success', $asset->is_favorite ? 'Se ha añadido a tus favoritos.' : 'Se ha quitado de tus favoritos.');
    }

    public function archiveAsset(Asset $asset)
    {
        $asset->update(['archived_at' => now()]);

        return redirect()->route('assets.index')->with('success', "«{$asset->name}» se ha archivado. Sus documentos siguen guardados.");
    }

    public function trashAsset(Asset $asset)
    {
        $asset->delete();

        return redirect()->route('assets.index')->with('success', "«{$asset->name}» se ha enviado a la papelera. Puedes restaurarlo.");
    }

    public function categories()
    {
        $this->seedCategories();

        return view('categories.index', ['categories' => Category::withCount('assets')->orderBy('position')->get()]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80'], 'icon' => ['nullable', 'string', 'max:30']]);
        Category::create([...$data, 'icon' => $data['icon'] ?? 'archive', 'position' => Category::max('position') + 1]);

        return back()->with('success', 'La categoría se ha creado.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:80'], 'icon' => ['nullable', 'string', 'max:30']]);
        $category->update($data);

        return back()->with('success', 'La categoría se ha actualizado.');
    }

    public function archiveCategory(Category $category)
    {
        $category->update(['is_archived' => true]);

        return back()->with('success', 'La categoría se ha archivado. Sus bienes no se han borrado.');
    }

    public function storeDocuments(Request $request, Asset $asset)
    {
        $request->validate(['files' => ['required', 'array', 'min:1'], 'files.*' => ['file', 'max:51200', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx,txt'], 'type' => ['nullable', 'string', 'max:80'], 'title' => ['nullable', 'string', 'max:180'], 'is_important' => ['nullable', 'boolean']]);
        $saved = 0;
        foreach ($request->file('files', []) as $file) {
            $path = $file->store('documents');
            Document::create(['asset_id' => $asset->id, 'title' => $request->input('title') ?: pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME), 'original_name' => $file->getClientOriginalName(), 'path' => $path, 'mime_type' => $file->getMimeType(), 'size' => $file->getSize(), 'type' => $request->input('type') ?: 'Otro documento', 'is_important' => $request->boolean('is_important'), 'uploaded_by' => Auth::id()]);
            $saved++;
        }

        return back()->with('success', "Se han añadido {$saved} documentos a «{$asset->name}».");
    }

    public function document(Document $document)
    {
        abort_unless($document->path && Storage::exists($document->path), 404);

        return response()->file(Storage::path($document->path), ['Content-Type' => $document->mime_type ?: 'application/octet-stream']);
    }

    public function editDocument(Document $document)
    {
        return view('documents.edit', ['document' => $document, 'assets' => Asset::whereNull('archived_at')->orderBy('name')->get()]);
    }

    public function downloadDocument(Document $document)
    {
        abort_unless(Storage::exists($document->path), 404);

        return Storage::download($document->path, $document->original_name);
    }

    public function updateDocument(Request $request, Document $document)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'type' => ['required', 'string', 'max:80'], 'document_date' => ['nullable', 'date'], 'expires_at' => ['nullable', 'date'], 'notes' => ['nullable', 'string'], 'is_important' => ['nullable', 'boolean'], 'asset_id' => ['nullable', 'exists:assets,id']]);
        $document->update([...$data, 'is_important' => $request->boolean('is_important')]);

        return back()->with('success', 'Los datos del documento se han guardado.');
    }

    public function trashDocument(Document $document)
    {
        $document->delete();

        return back()->with('success', 'El documento se ha enviado a la papelera.');
    }

    public function storePhotos(Request $request, Asset $asset)
    {
        $request->validate(['photos' => ['required', 'array'], 'photos.*' => ['image', 'max:20480']]);
        foreach ($request->file('photos', []) as $photo) {
            $asset->photos()->create(['path' => $photo->store('photos'), 'position' => $asset->photos()->count()]);
        }

        return back()->with('success', 'Las fotografías se han añadido.');
    }

    public function photo(AssetPhoto $photo)
    {
        abort_unless(Storage::exists($photo->path), 404);

        return response()->file(Storage::path($photo->path));
    }

    public function coverPhoto(AssetPhoto $photo)
    {
        AssetPhoto::where('asset_id', $photo->asset_id)->update(['is_cover' => false]);
        $photo->update(['is_cover' => true]);

        return back()->with('success', 'La fotografía principal se ha cambiado.');
    }

    public function deletePhoto(AssetPhoto $photo)
    {
        Storage::delete($photo->path);
        $photo->delete();

        return back()->with('success', 'La fotografía se ha eliminado.');
    }

    public function reminders()
    {
        return view('reminders.index', ['reminders' => Reminder::with('asset')->orderBy('completed')->orderBy('due_date')->get(), 'assets' => Asset::whereNull('archived_at')->orderBy('name')->get()]);
    }

    public function storeReminder(Request $request)
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:150'], 'due_date' => ['required', 'date'], 'asset_id' => ['nullable', 'exists:assets,id']]);
        Reminder::create($data);

        return back()->with('success', 'El recordatorio se ha guardado.');
    }

    public function toggleReminder(Reminder $reminder)
    {
        $reminder->update(['completed' => ! $reminder->completed]);

        return back()->with('success', $reminder->completed ? 'Recordatorio completado.' : 'Recordatorio reactivado.');
    }

    public function deleteReminder(Reminder $reminder)
    {
        $reminder->delete();

        return back()->with('success', 'El recordatorio se ha eliminado.');
    }

    public function trash()
    {
        return view('trash', ['assets' => Asset::onlyTrashed()->with('category')->latest()->get(), 'documents' => Document::onlyTrashed()->with('asset')->latest()->get()]);
    }

    public function restoreAsset(int $asset)
    {
        Asset::onlyTrashed()->findOrFail($asset)->restore();

        return back()->with('success', 'El bien se ha restaurado.');
    }

    public function restoreDocument(int $document)
    {
        Document::onlyTrashed()->findOrFail($document)->restore();

        return back()->with('success', 'El documento se ha restaurado.');
    }

    public function forceAsset(int $asset)
    {
        abort_unless(Auth::user()->isAdmin(), 403);
        Asset::onlyTrashed()->findOrFail($asset)->forceDelete();

        return back()->with('success', 'El bien se ha eliminado definitivamente.');
    }

    public function exportAsset(Asset $asset)
    {
        $asset->load(['category', 'documents', 'photos']);
        $filename = Str::slug($asset->name).'-patrimonasa.zip';
        $path = storage_path('app/'.$filename);
        $zip = new ZipArchive;
        abort_unless($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500);
        $zip->addFromString('datos.json', json_encode($asset->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        foreach ($asset->documents as $document) {
            if (Storage::exists($document->path)) {
                $zip->addFile(Storage::path($document->path), 'documentos/'.$document->original_name);
            }
        }
        foreach ($asset->photos as $photo) {
            if (Storage::exists($photo->path)) {
                $zip->addFile(Storage::path($photo->path), 'fotos/'.basename($photo->path));
            }
        }
        $zip->close();

        return response()->download($path)->deleteFileAfterSend(true);
    }

    private function seedCategories(): void
    {
        if (Category::exists()) {
            return;
        }
        foreach (['Viviendas' => 'home', 'Terrenos y fincas' => 'map', 'Garajes y trasteros' => 'key', 'Locales y naves' => 'building', 'Vehículos' => 'car', 'Otros bienes' => 'archive'] as $position => $icon) {
            Category::create(['name' => $position, 'icon' => $icon, 'position' => Category::count()]);
        }
    }
}
