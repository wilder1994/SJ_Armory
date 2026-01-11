<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AlertsController;
use App\Http\Controllers\ResponsiblePortfolioController;
use App\Http\Controllers\WeaponController;
use App\Http\Controllers\WeaponClientAssignmentController;
use App\Http\Controllers\WeaponDocumentController;
use App\Http\Controllers\WeaponPhotoController;
use App\Http\Controllers\WeaponCustodyController;
use App\Http\Controllers\WeaponStatusController;
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
    return view('welcome');
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

    Route::post('/weapons/{weapon}/custodies', [WeaponCustodyController::class, 'store'])
        ->name('weapons.custodies.store');

    Route::post('/weapons/{weapon}/assignments', [WeaponClientAssignmentController::class, 'store'])
        ->name('weapons.assignments.store');
    Route::patch('/weapons/{weapon}/assignments/retire', [WeaponClientAssignmentController::class, 'retire'])
        ->name('weapons.assignments.retire');

    Route::patch('/weapons/{weapon}/status', [WeaponStatusController::class, 'update'])
        ->name('weapons.status.update');

    Route::get('/portfolios', [ResponsiblePortfolioController::class, 'index'])->name('portfolios.index');
    Route::get('/portfolios/{user}/edit', [ResponsiblePortfolioController::class, 'edit'])->name('portfolios.edit');
    Route::put('/portfolios/{user}', [ResponsiblePortfolioController::class, 'update'])->name('portfolios.update');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/custodies', [ReportController::class, 'weaponsByCustodian'])->name('reports.custodies');
    Route::get('/reports/assignments', [ReportController::class, 'weaponsByClient'])->name('reports.assignments');
    Route::get('/reports/no-destination', [ReportController::class, 'weaponsWithoutDestination'])->name('reports.no_destination');
    Route::get('/reports/history', [ReportController::class, 'history'])->name('reports.history');
    Route::get('/reports/audit', [ReportController::class, 'audit'])->name('reports.audit');

    Route::get('/alerts/documents', [AlertsController::class, 'documents'])->name('alerts.documents');
});

require __DIR__.'/auth.php';
