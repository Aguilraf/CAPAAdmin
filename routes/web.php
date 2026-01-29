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
    // Rutas de Catálogos (Solo Administrador)
    Route::middleware(['role:Administrador'])->group(function () {
        Route::get('empleados/plantilla', [\App\Http\Controllers\EmpleadoController::class, 'downloadTemplate'])->name('empleados.template');
        Route::post('empleados/import', [\App\Http\Controllers\EmpleadoController::class, 'import'])->name('empleados.import');
        Route::resource('empleados', \App\Http\Controllers\EmpleadoController::class);
        Route::resource('capitulos', \App\Http\Controllers\CapituloController::class);
        Route::resource('partidas', \App\Http\Controllers\PartidaController::class);
        Route::resource('leyendas', \App\Http\Controllers\LeyendaController::class);
        Route::resource('materiales', \App\Http\Controllers\MaterialController::class);
        Route::resource('unidades-medida', \App\Http\Controllers\UnidadMedidaController::class);

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

    // Rutas de Reportes
    Route::middleware(['permission:generar reportes'])->group(function () {
        Route::get('/reportes', [\App\Http\Controllers\ReportController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/historial', [\App\Http\Controllers\ReportController::class, 'historial'])->name('reportes.historial');
        Route::post('/reportes/historial/{id}/print', [\App\Http\Controllers\ReportController::class, 'reprint'])->name('reportes.historial.print');
        Route::get('/reportes/solicitud-material', [\App\Http\Controllers\ReportController::class, 'createMaterialRequest'])->name('reportes.material-request.create');
        Route::post('/reportes/solicitud-material/defaults', [\App\Http\Controllers\ReportController::class, 'saveDefaults'])->name('reportes.material-request.defaults');
        Route::post('/reportes/solicitud-material/print', [\App\Http\Controllers\ReportController::class, 'printMaterialRequest'])->name('reportes.material-request.print');
    });

    // Rutas de Bomberos (Integración)
    // Capturar - accesible con permiso
    Route::middleware(['permission:capturar bomberos'])->group(function () {
        Route::get('/firefighters/capture', function () {
            return Inertia::render('Firefighters/Capture', [
                'communities' => \App\Models\Community::all(),
                'firefighters' => \App\Models\Firefighter::with('community')->get(),
            ]);
        })->name('firefighters.capture');
    });


    // Recibir - accesible con permiso
    Route::middleware(['permission:recibir bomberos'])->get('/firefighters/receive', function () {
        return Inertia::render('Firefighters/Receive', [
            'communities' => \App\Models\Community::all(),
            'settings' => \App\Models\FirefighterSetting::first(),
        ]);
    })->name('firefighters.receive');

    // Reportes - accesible con permiso
    Route::middleware(['permission:reportes bomberos'])->get('/firefighters/report', function (\Illuminate\Http\Request $request) {
        $requirements = \App\Models\Capture::select('year', 'requirement_number', 'requirement_type')
            ->whereNotNull('year')
            ->whereNotNull('requirement_number')
            ->where('requirement_type', 'bomberos') // Solo bomberos
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('requirement_number', 'desc')
            ->get();

        $captures = [];
        if ($request->filled('year') && $request->filled('requirement_number')) {
            $captures = \App\Models\Capture::with(['community', 'firefighter'])
                ->where('year', $request->year)
                ->where('requirement_number', $request->requirement_number)
                ->where('requirement_type', 'bomberos')
                ->get();
        }

        return Inertia::render('Firefighters/Report', [
            'requirements' => $requirements,
            'captures' => $captures,
            'filters' => $request->only(['year', 'requirement_number']),
            'settings' => \App\Models\FirefighterSetting::first(),
            'requirementTypes' => \App\Models\Capture::REQUIREMENT_TYPES,
        ]);
    })->name('firefighters.report');

    // Settings - accesible con permiso
    Route::middleware(['permission:configurar bomberos'])->group(function () {
        Route::get('/firefighters/settings', [\App\Http\Controllers\FirefighterSettingController::class, 'index'])->name('firefighters.settings');
        Route::post('/firefighters/settings', [\App\Http\Controllers\FirefighterSettingController::class, 'update'])->name('firefighters.settings.update');
    });

    // Comunidades - accesible con permiso
    Route::middleware(['permission:ver comunidades'])->get('/firefighters/communities', function () {
        return Inertia::render('Firefighters/Communities', [
            'communities' => \App\Models\Community::all(),
        ]);
    })->name('firefighters.communities');

    // Lista de Bomberos - accesible con permiso
    Route::middleware(['permission:ver bomberos'])->get('/firefighters/list', function () {
        return Inertia::render('Firefighters/Firefighters', [
            'firefighters' => \App\Models\Firefighter::with('community')->get(),
            'communities' => \App\Models\Community::all(),
        ]);
    })->name('firefighters.list');

    // Configuración - accesible con permiso
    Route::middleware(['permission:configurar bomberos'])->get('/firefighters/settings', function () {
        return Inertia::render('Firefighters/Settings', [
            'settings' => \App\Models\FirefighterSetting::first(),
        ]);
    })->name('firefighters.settings');

    // Importar - accesible con permiso
    Route::middleware(['permission:importar bomberos'])->get('/firefighters/import', function () {
        return Inertia::render('Firefighters/ImportCaptures');
    })->name('firefighters.import');
});

require __DIR__ . '/auth.php';
