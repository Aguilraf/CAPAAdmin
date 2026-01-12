<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de Catálogos
    Route::resource('empleados', \App\Http\Controllers\EmpleadoController::class);
    Route::resource('capitulos', \App\Http\Controllers\CapituloController::class);
    Route::resource('partidas', \App\Http\Controllers\PartidaController::class);
    Route::resource('leyendas', \App\Http\Controllers\LeyendaController::class);
    Route::resource('materiales', \App\Http\Controllers\MaterialController::class);
    Route::resource('unidades-medida', \App\Http\Controllers\UnidadMedidaController::class);

    // Rutas de Importación
    Route::get('/importar', [\App\Http\Controllers\ImportController::class, 'index'])->name('import.index');
    Route::post('/importar', [\App\Http\Controllers\ImportController::class, 'store'])->name('import.store');
    Route::get('/importar/plantilla', [\App\Http\Controllers\ImportController::class, 'downloadTemplate'])->name('import.template');
});

require __DIR__ . '/auth.php';
