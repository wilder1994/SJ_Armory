<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WeaponController;
use App\Http\Controllers\WeaponDocumentController;
use App\Http\Controllers\WeaponPhotoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::resource('clients', ClientController::class)->except(['show']);
    Route::resource('weapons', WeaponController::class);

    Route::post('/weapons/{weapon}/photos', [WeaponPhotoController::class, 'store'])
        ->name('weapons.photos.store');
    Route::patch('/weapons/{weapon}/photos/{photo}/primary', [WeaponPhotoController::class, 'setPrimary'])
        ->name('weapons.photos.primary');
    Route::delete('/weapons/{weapon}/photos/{photo}', [WeaponPhotoController::class, 'destroy'])
        ->name('weapons.photos.destroy');

    Route::post('/weapons/{weapon}/documents', [WeaponDocumentController::class, 'store'])
        ->name('weapons.documents.store');
    Route::get('/weapons/{weapon}/documents/{document}/download', [WeaponDocumentController::class, 'download'])
        ->name('weapons.documents.download');
    Route::delete('/weapons/{weapon}/documents/{document}', [WeaponDocumentController::class, 'destroy'])
        ->name('weapons.documents.destroy');
});

require __DIR__.'/auth.php';
