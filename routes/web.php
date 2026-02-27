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

// Ruta para servir imágenes directamente (Opción Nuclear)
Route::get('/media/{path}', [\App\Http\Controllers\MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // Rutas de Catálogos
    // Specific routes must come BEFORE resource routes
    // Rutas de Catálogos (Solo Administrador)
    Route::middleware(['role:Administrador'])->group(function () {
        /* Route::get('empleados/plantilla', [\App\Http\Controllers\EmpleadoController::class, 'downloadTemplate'])->name('empleados.template');
        Route::get('empleados/export', [\App\Http\Controllers\EmpleadoController::class, 'export'])->name('empleados.export');
        Route::post('empleados/import', [\App\Http\Controllers\EmpleadoController::class, 'import'])->name('empleados.import'); */
        Route::resource('empleados', \App\Http\Controllers\EmpleadoController::class);
        Route::resource('capitulos', \App\Http\Controllers\CapituloController::class);
        Route::resource('partidas', \App\Http\Controllers\PartidaController::class);
        Route::resource('leyendas', \App\Http\Controllers\LeyendaController::class);
        Route::resource('materiales', \App\Http\Controllers\MaterialController::class);
        Route::resource('materiales', \App\Http\Controllers\MaterialController::class);
        Route::resource('unidades-medida', \App\Http\Controllers\UnidadMedidaController::class);
        Route::resource('puestos', \App\Http\Controllers\PuestoController::class);
        Route::resource('organismos', \App\Http\Controllers\OrganismoController::class);


        // Viaticos Catalogs
        Route::resource('vehicles', \App\Http\Controllers\VehicleController::class);
        Route::resource('travel-allowance-rates', \App\Http\Controllers\TravelAllowanceRateController::class);

        // Rutas de Importación/Exportación Unificada
        Route::get('/importar', [\App\Http\Controllers\ImportController::class, 'index'])->name('import.index');
        Route::post('/importar', [\App\Http\Controllers\ImportController::class, 'store'])->name('import.store');
        Route::get('/exportar', [\App\Http\Controllers\ImportController::class, 'export'])->name('import.export');
        Route::get('/importar/plantilla', [\App\Http\Controllers\ImportController::class, 'downloadTemplate'])->name('import.template');

        // Rutas de Administración de Usuarios
        Route::resource('roles', \App\Http\Controllers\RoleController::class);
        Route::resource('users', \App\Http\Controllers\UserController::class);

        // Administración de Vacaciones
        Route::get('/admin/vacaciones', [\App\Http\Controllers\VacationAdminController::class, 'index'])->name('vacations.admin.index');
        Route::post('/admin/vacaciones/generar', [\App\Http\Controllers\VacationAdminController::class, 'generatePeriod'])->name('vacations.admin.generate');
        Route::post('/admin/vacaciones/generar-masivo', [\App\Http\Controllers\VacationAdminController::class, 'bulkGenerate'])->name('vacations.admin.generate-bulk');

        // Rutas de Cancelación
        Route::get('/admin/vacaciones/cancelar', [\App\Http\Controllers\VacationAdminController::class, 'cancellationIndex'])->name('vacations.admin.cancellation');
        Route::get('/admin/vacaciones/cancelar/{empleado}', [\App\Http\Controllers\VacationAdminController::class, 'showPeriods'])->name('vacations.admin.periods');
        Route::delete('/admin/vacaciones/periodo/{periodo}', [\App\Http\Controllers\VacationAdminController::class, 'destroyPeriod'])->name('vacations.admin.periods.destroy');

        // Administración de Solicitudes
        Route::get('/admin/solicitudes', [\App\Http\Controllers\VacationRequestAdminController::class, 'index'])->name('vacations.admin.requests');
        Route::post('/admin/solicitudes/{id}/approve', [\App\Http\Controllers\VacationRequestAdminController::class, 'approve'])->name('vacations.admin.requests.approve');
        Route::post('/admin/solicitudes/{id}/reject', [\App\Http\Controllers\VacationRequestAdminController::class, 'reject'])->name('vacations.admin.requests.reject');
    });

    // Rutas de Reportes
    Route::middleware(['role:Administrador|permission:generar reportes|permission:gestionar reportes'])->group(function () {
        Route::get('/reportes', [\App\Http\Controllers\ReportController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/historial', [\App\Http\Controllers\ReportController::class, 'historial'])->name('reportes.historial');
        Route::get('/reportes/historial/{id}/print', [\App\Http\Controllers\ReportController::class, 'reprint'])->name('reportes.historial.print');
        Route::get('/reportes/solicitud-material', [\App\Http\Controllers\ReportController::class, 'createMaterialRequest'])->name('reportes.material-request.create');
        Route::post('/reportes/solicitud-material/defaults', [\App\Http\Controllers\ReportController::class, 'saveDefaults'])->name('reportes.material-request.defaults');
        Route::post('/reportes/solicitud-material/print', [\App\Http\Controllers\ReportController::class, 'printMaterialRequest'])->name('reportes.material-request.print');
    });

    // Modulo de Requerimientos
    Route::middleware(['auth'])->group(function () {
        Route::get('requirements/next-number', [\App\Http\Controllers\RequirementController::class, 'getNextNumber'])->name('requirements.next-number');
        Route::post('requirements/parse-xml', [\App\Http\Controllers\RequirementController::class, 'parseXml'])->name('requirements.parse-xml');
        Route::resource('requirements', \App\Http\Controllers\RequirementController::class);
        Route::get('requirements/{requirement}/pdf', [\App\Http\Controllers\RequirementController::class, 'downloadPdf'])->name('requirements.pdf');
        Route::get('requirements/{requirement}/anexo-2/{employee}', [\App\Http\Controllers\RequirementController::class, 'downloadAnexo2'])->name('requirements.anexo-2');
        Route::get('requirements/{requirement}/cfe-relation', [\App\Http\Controllers\RequirementController::class, 'downloadCfeRelation'])->name('requirements.cfe-relation');
        Route::get('requirements/{requirement}/comprobacion-viaticos/{employee}', [\App\Http\Controllers\RequirementController::class, 'downloadComprobacionViaticos'])->name('requirements.comprobacion-viaticos');
        Route::get('requirements/{requirement}/bomberos-oficio', [\App\Http\Controllers\RequirementController::class, 'downloadBomberosOficio'])->name('requirements.bomberos-oficio');
        Route::get('requirements/{requirement}/revolvente-oficio', [\App\Http\Controllers\RequirementController::class, 'downloadRevolventeOficio'])->name('requirements.revolvente-oficio');
        Route::get('requirements/{requirement}/revolvente-anexo4', [\App\Http\Controllers\RequirementController::class, 'downloadRevolventeAnexo4'])->name('requirements.revolvente-anexo4');
        Route::get('requirements/{requirement}/revolvente-cedula', [\App\Http\Controllers\RequirementController::class, 'downloadRevolvanteCedula'])->name('requirements.revolvente-cedula');



        // CFE Query Routes
        Route::get('/cfe/query', [\App\Http\Controllers\CfeQueryController::class, 'index'])->name('cfe.query');
        Route::get('/cfe/export', [\App\Http\Controllers\CfeQueryController::class, 'export'])->name('cfe.export');

        // Modulo de Pagos
        Route::resource('payments', \App\Http\Controllers\PaymentController::class);
        Route::get('payments/{payment}/pdf', [\App\Http\Controllers\PaymentController::class, 'downloadPdf'])->name('payments.pdf');
        Route::get('payments/requirement/{requirement}/pdf', [\App\Http\Controllers\PaymentController::class, 'downloadRequirementPaymentsPdf'])->name('payments.requirement.pdf');

        // Modulo de Comisiones
        Route::resource('commissions', \App\Http\Controllers\CommissionController::class);
        Route::get('commissions/{commission}/pdf', [\App\Http\Controllers\CommissionController::class, 'printPdf'])->name('commissions.pdf');

        // Catalogo de Proveedores
        Route::resource('providers', \App\Http\Controllers\ProviderController::class);
    });

    // Rutas de Configuración (Unificada)
    Route::middleware(['role:Administrador|permission:configurar bomberos'])->group(function () {
        Route::get('/configuracion', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/configuracion', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    });



    // Modulo de Vacaciones
    Route::middleware(['auth', 'role:Administrador|permission:ver vacaciones'])->group(function () {
        Route::get('/vacaciones', [\App\Http\Controllers\VacationController::class, 'index'])->name('vacations.index');
        Route::post('/vacaciones', [\App\Http\Controllers\VacationController::class, 'store'])->name('vacations.store');
        Route::get('/vacaciones/solicitud/{solicitud}/pdf', [\App\Http\Controllers\VacationPdfController::class, 'downloadRequest'])->name('vacations.pdf.request');

        // Evaluation Bonus (Quarterly)
        Route::get('/evaluaciones/bono/check', [\App\Http\Controllers\EvaluationBonusController::class, 'checkStatus'])->name('evaluation.bonus.check');
        Route::post('/evaluaciones/bono', [\App\Http\Controllers\EvaluationBonusController::class, 'store'])->name('evaluation.bonus.store');
    });

    // Rutas de Bomberos (Integración)
    // Capturar - accesible con permiso
    Route::middleware(['role:Administrador|permission:capturar bomberos'])->group(function () {
        Route::get('/firefighters/capture', function () {
            return Inertia::render('Firefighters/Capture', [
                'communities' => \App\Models\Community::all(),
                'firefighters' => \App\Models\Firefighter::with('community')->get(),
            ]);
        })->name('firefighters.capture');
    });


    // Recibir - accesible con permiso
    Route::middleware(['role:Administrador|permission:recibir bomberos'])->get('/firefighters/receive', function () {
        return Inertia::render('Firefighters/Receive', [
            'communities' => \App\Models\Community::all(),
            'settings' => \App\Models\FirefighterSetting::first(),
        ]);
    })->name('firefighters.receive');

    // Reportes - accesible con permiso
    Route::middleware(['role:Administrador|permission:reportes bomberos'])->get('/firefighters/report', function (\Illuminate\Http\Request $request) {
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

    Route::get('/report/firefighters/pdf', [\App\Http\Controllers\FirefighterReportPdfController::class, 'download'])->name('firefighters.report.pdf');

    // Consulta de Historial por Comunidad - accesible con permiso de reportes
    Route::middleware(['role:Administrador|permission:reportes bomberos'])->group(function () {
        Route::get('/firefighters/query', [\App\Http\Controllers\FirefighterQueryController::class, 'index'])->name('firefighters.query');
        Route::get('/firefighters/export', [\App\Http\Controllers\FirefighterQueryController::class, 'export'])->name('firefighters.export');
    });

    // Settings - accesible con permiso
    Route::middleware(['role:Administrador|permission:configurar bomberos'])->group(function () {
        Route::get('/firefighters/settings', [\App\Http\Controllers\FirefighterSettingController::class, 'index'])->name('firefighters.settings');
        Route::post('/firefighters/settings', [\App\Http\Controllers\FirefighterSettingController::class, 'update'])->name('firefighters.settings.update');
    });

    // Comunidades - accesible con permiso
    Route::middleware(['role:Administrador|permission:ver comunidades'])->get('/firefighters/communities', function () {
        return Inertia::render('Firefighters/Communities', [
            'communities' => \App\Models\Community::all(),
        ]);
    })->name('firefighters.communities');

    // Lista de Bomberos - accesible con permiso
    Route::middleware(['role:Administrador|permission:ver bomberos'])->get('/firefighters/list', function () {
        return Inertia::render('Firefighters/Firefighters', [
            'firefighters' => \App\Models\Firefighter::with('community')->get(),
            'communities' => \App\Models\Community::all(),
        ]);
    })->name('firefighters.list');

    // Configuración - accesible con permiso
    Route::middleware(['role:Administrador|permission:configurar bomberos'])->get('/firefighters/settings', function () {
        return Inertia::render('Firefighters/Settings', [
            'settings' => \App\Models\FirefighterSetting::first(),
        ]);
    })->name('firefighters.settings');

    // Modulo de Bomberos (Refactorizado a Web Controllers)
    Route::middleware(['auth'])->group(function () {
        // Communities
        Route::resource('communities', \App\Http\Controllers\CommunityController::class);
        /* Route::post('communities/import', [\App\Http\Controllers\CommunityImportController::class, 'import'])->name('communities.import');
        Route::get('communities/import/template', [\App\Http\Controllers\CommunityImportController::class, 'downloadTemplate'])->name('communities.import.template'); */

        // Firefighters
        Route::resource('firefighters', \App\Http\Controllers\FirefighterController::class);
        /* Route::post('firefighters/import', [\App\Http\Controllers\FirefighterImportController::class, 'import'])->name('firefighters.import');
        Route::get('firefighters/import/template', [\App\Http\Controllers\FirefighterImportController::class, 'downloadTemplate'])->name('firefighters.import.template'); */

        // Captures
        Route::post('captures/assign-requirement', [\App\Http\Controllers\CaptureController::class, 'assignRequirement'])->name('captures.assign-requirement');
        Route::get('captures/requirements', [\App\Http\Controllers\CaptureController::class, 'getRequirements'])->name('captures.requirements');
        Route::get('captures/summary', [\App\Http\Controllers\CaptureController::class, 'getSummaryByAssignment'])->name('captures.summary');
        Route::get('captures/next-requirement', [\App\Http\Controllers\CaptureController::class, 'getNextRequirementNumber'])->name('captures.next-requirement');
        Route::post('captures/import', [\App\Http\Controllers\CaptureImportController::class, 'import'])->name('captures.import');
        Route::get('captures/import/template', [\App\Http\Controllers\CaptureImportController::class, 'downloadTemplate'])->name('captures.import.template');
        Route::resource('captures', \App\Http\Controllers\CaptureController::class);

        // Settings (API-like access for frontend)
        Route::get('/firefighter-settings-json', [\App\Http\Controllers\FirefighterSettingController::class, 'index']);
        Route::post('/firefighter-settings-update', [\App\Http\Controllers\FirefighterSettingController::class, 'update']);
    });
});

require __DIR__ . '/auth.php';
