<?php

use App\Http\Controllers\PatrimonasaController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/entrar', [PatrimonasaController::class, 'login'])->name('login');
    Route::post('/entrar', [PatrimonasaController::class, 'authenticate'])->name('login.store');
    Route::get('/crear-cuenta', [PatrimonasaController::class, 'register'])->name('register');
    Route::post('/crear-cuenta', [PatrimonasaController::class, 'storeUser'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/', [PatrimonasaController::class, 'home'])->name('home');
    Route::post('/salir', [PatrimonasaController::class, 'logout'])->name('logout');

    Route::get('/bienes', [PatrimonasaController::class, 'assets'])->name('assets.index');
    Route::get('/documentos', [PatrimonasaController::class, 'documentsAll'])->name('documents.all');
    Route::get('/bienes/nuevo', [PatrimonasaController::class, 'createAsset'])->name('assets.create');
    Route::post('/bienes', [PatrimonasaController::class, 'storeAsset'])->name('assets.store');
    Route::get('/bienes/{asset}', [PatrimonasaController::class, 'showAsset'])->name('assets.show');
    Route::get('/bienes/{asset}/editar', [PatrimonasaController::class, 'editAsset'])->name('assets.edit');
    Route::put('/bienes/{asset}', [PatrimonasaController::class, 'updateAsset'])->name('assets.update');
    Route::post('/bienes/{asset}/favorito', [PatrimonasaController::class, 'toggleFavorite'])->name('assets.favorite');
    Route::post('/bienes/{asset}/archivar', [PatrimonasaController::class, 'archiveAsset'])->name('assets.archive');
    Route::delete('/bienes/{asset}', [PatrimonasaController::class, 'trashAsset'])->name('assets.trash');
    Route::get('/bienes/{asset}/exportar', [PatrimonasaController::class, 'exportAsset'])->name('assets.export');
    Route::post('/bienes/{asset}/documentos', [PatrimonasaController::class, 'storeDocuments'])->name('documents.store');
    Route::post('/bienes/{asset}/fotos', [PatrimonasaController::class, 'storePhotos'])->name('photos.store');

    Route::get('/documentos/{document}/ver', [PatrimonasaController::class, 'document'])->name('documents.view');
    Route::get('/documentos/{document}/descargar', [PatrimonasaController::class, 'downloadDocument'])->name('documents.download');
    Route::get('/documentos/{document}/editar', [PatrimonasaController::class, 'editDocument'])->name('documents.edit');
    Route::put('/documentos/{document}', [PatrimonasaController::class, 'updateDocument'])->name('documents.update');
    Route::delete('/documentos/{document}', [PatrimonasaController::class, 'trashDocument'])->name('documents.trash');
    Route::get('/fotos/{photo}', [PatrimonasaController::class, 'photo'])->name('photos.view');
    Route::post('/fotos/{photo}/principal', [PatrimonasaController::class, 'coverPhoto'])->name('photos.cover');
    Route::delete('/fotos/{photo}', [PatrimonasaController::class, 'deletePhoto'])->name('photos.delete');

    Route::get('/categorias', [PatrimonasaController::class, 'categories'])->name('categories.index');
    Route::post('/categorias', [PatrimonasaController::class, 'storeCategory'])->name('categories.store');
    Route::put('/categorias/{category}', [PatrimonasaController::class, 'updateCategory'])->name('categories.update');
    Route::post('/categorias/{category}/archivar', [PatrimonasaController::class, 'archiveCategory'])->name('categories.archive');

    Route::get('/recordatorios', [PatrimonasaController::class, 'reminders'])->name('reminders.index');
    Route::post('/recordatorios', [PatrimonasaController::class, 'storeReminder'])->name('reminders.store');
    Route::post('/recordatorios/{reminder}/completar', [PatrimonasaController::class, 'toggleReminder'])->name('reminders.toggle');
    Route::delete('/recordatorios/{reminder}', [PatrimonasaController::class, 'deleteReminder'])->name('reminders.delete');

    Route::get('/papelera', [PatrimonasaController::class, 'trash'])->name('trash.index');
    Route::post('/papelera/bien/{asset}/restaurar', [PatrimonasaController::class, 'restoreAsset'])->name('trash.asset.restore');
    Route::post('/papelera/documento/{document}/restaurar', [PatrimonasaController::class, 'restoreDocument'])->name('trash.document.restore');
    Route::delete('/papelera/bien/{asset}', [PatrimonasaController::class, 'forceAsset'])->name('trash.asset.force');
});
