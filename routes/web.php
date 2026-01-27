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
    // Specific routes must come BEFORE resource routes
    Route::get('empleados/plantilla', [\App\Http\Controllers\EmpleadoController::class, 'downloadTemplate'])->name('empleados.template');
    Route::post('empleados/import', [\App\Http\Controllers\EmpleadoController::class, 'import'])->name('empleados.import');
    Route::resource('empleados', \App\Http\Controllers\EmpleadoController::class);
    Route::resource('capitulos', \App\Http\Controllers\CapituloController::class);
    Route::resource('partidas', \App\Http\Controllers\PartidaController::class);
    Route::resource('leyendas', \App\Http\Controllers\LeyendaController::class);
    Route::resource('materiales', \App\Http\Controllers\MaterialController::class);
    Route::resource('unidades-medida', \App\Http\Controllers\UnidadMedidaController::class);

    // Rutas de Reportes
    Route::get('/reportes', [\App\Http\Controllers\ReportController::class, 'index'])->name('reportes.index');
    Route::get('/reportes/historial', [\App\Http\Controllers\ReportController::class, 'historial'])->name('reportes.historial');
    Route::post('/reportes/historial/{id}/print', [\App\Http\Controllers\ReportController::class, 'reprint'])->name('reportes.historial.print');
    Route::get('/reportes/solicitud-material', [\App\Http\Controllers\ReportController::class, 'createMaterialRequest'])->name('reportes.material-request.create');
    Route::post('/reportes/solicitud-material/print', [\App\Http\Controllers\ReportController::class, 'printMaterialRequest'])->name('reportes.material-request.print');

    // Rutas de Configuración
    Route::get('/configuracion', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/configuracion', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');

    // Rutas de Importación
    Route::get('/importar', [\App\Http\Controllers\ImportController::class, 'index'])->name('import.index');
    Route::post('/importar', [\App\Http\Controllers\ImportController::class, 'store'])->name('import.store');
    Route::get('/importar/plantilla', [\App\Http\Controllers\ImportController::class, 'downloadTemplate'])->name('import.template');
    // Rutas de Administración de Usuarios
    Route::resource('roles', \App\Http\Controllers\RoleController::class);
    Route::resource('users', \App\Http\Controllers\UserController::class);
});

require __DIR__ . '/auth.php';
