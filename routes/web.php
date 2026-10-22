<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LivraisonController;
use App\Http\Controllers\RapportController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::get('/login', [AuthController::class, 'showLogin']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showLogin'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');

// Password reset
Route::get('/auth/password_reset', [AuthController::class, 'showResetForm'])->name('password.request');
Route::post('/auth/password_reset', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/auth/password_reset/confirm/{token}', [AuthController::class, 'showResetFormToken'])->name('password.reset');
Route::post('/auth/password_reset/confirm', [AuthController::class, 'reset'])->name('password.update');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/', DashboardController::class)->name('dashboard');
    Route::get('/livraisons/', [LivraisonController::class, 'liste'])->name('liste_livraisons');
});

// Livraison routes (open, like Django)
Route::get('/ajouter/', [LivraisonController::class, 'ajouter'])->name('ajouter_livraison');
Route::post('/ajouter/', [LivraisonController::class, 'ajouter']);
Route::get('/modifier/{id}/', [LivraisonController::class, 'modifier'])->name('modifier_livraison');
Route::post('/modifier/{id}/', [LivraisonController::class, 'modifier']);
Route::get('/supprimer/{id}/', [LivraisonController::class, 'supprimer'])->name('supprimer_livraison');
Route::get('/modifier-statut-camion/{camionId}/', [LivraisonController::class, 'modifierStatut'])->name('modifier_statut_camion');
Route::post('/modifier-statut-camion/{camionId}/', [LivraisonController::class, 'modifierStatut']);
Route::get('/exporter-livraisons-excel/', [LivraisonController::class, 'exporter'])->name('exporter_livraisons_excel');

// Rapports (open)
Route::get('/rapports/', [RapportController::class, 'index'])->name('liste_rapports');
Route::get('/rapports/ajouter/', [RapportController::class, 'ajouter'])->name('ajouter_rapport');
Route::post('/rapports/ajouter/', [RapportController::class, 'ajouter']);
Route::get('/rapports/telecharger/{id}/', [RapportController::class, 'telecharger'])->name('telecharger_rapport');
Route::get('/supprimer_rapport/{id}/', [RapportController::class, 'supprimer'])->name('supprimer_rapport');

// Admin (protected by is_admin middleware)
Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/camions', [AdminController::class, 'camionsIndex'])->name('admin.camions.index');
    Route::get('/camions/create', [AdminController::class, 'camionsCreate'])->name('admin.camions.create');
    Route::post('/camions', [AdminController::class, 'camionsStore'])->name('admin.camions.store');
    Route::get('/camions/{id}/edit', [AdminController::class, 'camionsEdit'])->name('admin.camions.edit');
    Route::put('/camions/{id}', [AdminController::class, 'camionsUpdate'])->name('admin.camions.update');
    Route::delete('/camions/{id}', [AdminController::class, 'camionsDestroy'])->name('admin.camions.destroy');

    Route::get('/livraisons', [AdminController::class, 'livraisonsIndex'])->name('admin.livraisons.index');
    Route::get('/livraisons/create', [AdminController::class, 'livraisonsCreate'])->name('admin.livraisons.create');
    Route::post('/livraisons', [AdminController::class, 'livraisonsStore'])->name('admin.livraisons.store');
    Route::get('/livraisons/{id}/edit', [AdminController::class, 'livraisonsEdit'])->name('admin.livraisons.edit');
    Route::put('/livraisons/{id}', [AdminController::class, 'livraisonsUpdate'])->name('admin.livraisons.update');
    Route::delete('/livraisons/{id}', [AdminController::class, 'livraisonsDestroy'])->name('admin.livraisons.destroy');
});
