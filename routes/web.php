<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| DASHBOARD
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


/*
|--------------------------------------------------------------------------
| MEDIA
|--------------------------------------------------------------------------
*/

Route::get('/media/{path}', [
    \App\Http\Controllers\MediaController::class,
    'show'
])->where('path', '.*')->name('media.show');


/*
|--------------------------------------------------------------------------
| RUTAS AUTENTICADAS
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | PERFIL
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [
        ProfileController::class,
        'edit'
    ])->name('profile.edit');

    Route::patch('/profile', [
        ProfileController::class,
        'update'
    ])->name('profile.update');

    Route::delete('/profile', [
        ProfileController::class,
        'destroy'
    ])->name('profile.destroy');


    /*
    |--------------------------------------------------------------------------
    | CATÁLOGOS - SOLO ADMINISTRADOR
    |--------------------------------------------------------------------------
    */

    Route::middleware(['role:Administrador'])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Empleados
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'empleados',
            \App\Http\Controllers\EmpleadoController::class
        );

        /*
        |--------------------------------------------------------------------------
        | Catálogos generales
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'capitulos',
            \App\Http\Controllers\CapituloController::class
        );

        Route::resource(
            'partidas',
            \App\Http\Controllers\PartidaController::class
        );

        Route::resource(
            'leyendas',
            \App\Http\Controllers\LeyendaController::class
        );

        Route::resource(
            'materiales',
            \App\Http\Controllers\MaterialController::class
        );

        Route::resource(
            'unidades-medida',
            \App\Http\Controllers\UnidadMedidaController::class
        );

        Route::resource(
            'puestos',
            \App\Http\Controllers\PuestoController::class
        );

        Route::resource(
            'organismos',
            \App\Http\Controllers\OrganismoController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Viáticos
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'vehicles',
            \App\Http\Controllers\VehicleController::class
        );

        Route::resource(
            'travel-allowance-rates',
            \App\Http\Controllers\TravelAllowanceRateController::class
        );

        Route::resource(
            'banks',
            \App\Http\Controllers\BankController::class
        )->except(['show']);


        /*
        |--------------------------------------------------------------------------
        | Ingresos
        |--------------------------------------------------------------------------
        */

        Route::get(
            'ingresos/plantilla',
            [
                \App\Http\Controllers\BankMovementController::class,
                'downloadTemplate'
            ]
        )->name('incomes.template');

        Route::get(
            'ingresos/plantilla-azteca',
            [
                \App\Http\Controllers\BankMovementController::class,
                'downloadAztecaTemplate'
            ]
        )->name('incomes.azteca-template');

        Route::get(
            'ingresos',
            [
                \App\Http\Controllers\BankMovementController::class,
                'index'
            ]
        )->name('incomes.index');

        Route::post(
            'ingresos/importar',
            [
                \App\Http\Controllers\BankMovementController::class,
                'import'
            ]
        )->name('incomes.import');

        Route::get(
            'polizas-ingreso/crear',
            [
                \App\Http\Controllers\IncomePolicyController::class,
                'create'
            ]
        )->name('income-policies.create');

        Route::get('polizas-ingreso', [
            \App\Http\Controllers\IncomePolicyController::class,
            'index'
        ])->name('income-policies.index');
        Route::get('polizas-ingreso/{incomePolicy}/editar', [
            \App\Http\Controllers\IncomePolicyController::class,
            'edit'
        ])->name('income-policies.edit');

        Route::post(
            'polizas-ingreso',
            [
                \App\Http\Controllers\IncomePolicyController::class,
                'store'
            ]
        )->name('income-policies.store');
        Route::put('polizas-ingreso/{incomePolicy}', [
            \App\Http\Controllers\IncomePolicyController::class,
            'update'
        ])->name('income-policies.update');
        Route::delete('polizas-ingreso/{incomePolicy}', [
            \App\Http\Controllers\IncomePolicyController::class,
            'destroy'
        ])->name('income-policies.destroy');

        Route::get('catalogo-tipos-poliza', [
            \App\Http\Controllers\IncomePolicyTypeController::class,
            'index'
        ])->name('income-policy-types.index');
        Route::post('catalogo-tipos-poliza', [
            \App\Http\Controllers\IncomePolicyTypeController::class,
            'store'
        ])->name('income-policy-types.store');
        Route::patch('catalogo-tipos-poliza/{incomePolicyType}', [
            \App\Http\Controllers\IncomePolicyTypeController::class,
            'update'
        ])->name('income-policy-types.update');
        Route::delete('catalogo-tipos-poliza/{incomePolicyType}', [
            \App\Http\Controllers\IncomePolicyTypeController::class,
            'destroy'
        ])->name('income-policy-types.destroy');

        Route::get(
            'catalogo-cuentas-ingreso',
            [
                \App\Http\Controllers\IncomeAccountController::class,
                'index'
            ]
        )->name('income-accounts.index');

        Route::post(
            'catalogo-cuentas-ingreso',
            [
                \App\Http\Controllers\IncomeAccountController::class,
                'store'
            ]
        )->name('income-accounts.store');

        Route::get(
            'catalogo-cuentas-ingreso/plantilla',
            [
                \App\Http\Controllers\IncomeAccountController::class,
                'downloadTemplate'
            ]
        )->name('income-accounts.template');

        Route::post(
            'catalogo-cuentas-ingreso/importar',
            [
                \App\Http\Controllers\IncomeAccountController::class,
                'import'
            ]
        )->name('income-accounts.import');

        Route::delete(
            'catalogo-cuentas-ingreso/{incomeAccount}',
            [
                \App\Http\Controllers\IncomeAccountController::class,
                'destroy'
            ]
        )->name('income-accounts.destroy');

        Route::patch(
            'catalogo-cuentas-ingreso/{incomeAccount}/visibilidad',
            [
                \App\Http\Controllers\IncomeAccountController::class,
                'toggleVisibility'
            ]
        )->name('income-accounts.visibility');


        /*
        |--------------------------------------------------------------------------
        | Importación / Exportación
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/importar',
            [
                \App\Http\Controllers\ImportController::class,
                'index'
            ]
        )->name('import.index');

        Route::post(
            '/importar',
            [
                \App\Http\Controllers\ImportController::class,
                'store'
            ]
        )->name('import.store');

        Route::get(
            '/exportar',
            [
                \App\Http\Controllers\ImportController::class,
                'export'
            ]
        )->name('import.export');

        Route::get(
            '/importar/plantilla',
            [
                \App\Http\Controllers\ImportController::class,
                'downloadTemplate'
            ]
        )->name('import.template');


        /*
        |--------------------------------------------------------------------------
        | Usuarios y roles
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'roles',
            \App\Http\Controllers\RoleController::class
        );

        Route::resource(
            'users',
            \App\Http\Controllers\UserController::class
        );


        /*
        |--------------------------------------------------------------------------
        | Administración de vacaciones
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/admin/vacaciones',
            [
                \App\Http\Controllers\VacationAdminController::class,
                'index'
            ]
        )->name('vacations.admin.index');

        Route::post(
            '/admin/vacaciones/generar',
            [
                \App\Http\Controllers\VacationAdminController::class,
                'generatePeriod'
            ]
        )->name('vacations.admin.generate');

        Route::post(
            '/admin/vacaciones/generar-masivo',
            [
                \App\Http\Controllers\VacationAdminController::class,
                'bulkGenerate'
            ]
        )->name('vacations.admin.generate-bulk');


        /*
        |--------------------------------------------------------------------------
        | Cancelación de vacaciones
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/admin/vacaciones/cancelar',
            [
                \App\Http\Controllers\VacationAdminController::class,
                'cancellationIndex'
            ]
        )->name('vacations.admin.cancellation');

        Route::get(
            '/admin/vacaciones/cancelar/{empleado}',
            [
                \App\Http\Controllers\VacationAdminController::class,
                'showPeriods'
            ]
        )->name('vacations.admin.periods');

        Route::delete(
            '/admin/vacaciones/periodo/{periodo}',
            [
                \App\Http\Controllers\VacationAdminController::class,
                'destroyPeriod'
            ]
        )->name('vacations.admin.periods.destroy');


        /*
        |--------------------------------------------------------------------------
        | Administración de solicitudes
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/admin/solicitudes',
            [
                \App\Http\Controllers\VacationRequestAdminController::class,
                'index'
            ]
        )->name('vacations.admin.requests');

        Route::post(
            '/admin/solicitudes/{id}/approve',
            [
                \App\Http\Controllers\VacationRequestAdminController::class,
                'approve'
            ]
        )->name('vacations.admin.requests.approve');

        Route::post(
            '/admin/solicitudes/{id}/reject',
            [
                \App\Http\Controllers\VacationRequestAdminController::class,
                'reject'
            ]
        )->name('vacations.admin.requests.reject');
    });


    /*
    |--------------------------------------------------------------------------
    | REPORTES
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|generar reportes|gestionar reportes'
    ])->group(function () {

        Route::get(
            '/reportes',
            [
                \App\Http\Controllers\ReportController::class,
                'index'
            ]
        )->name('reportes.index');

        Route::get(
            '/reportes/historial',
            [
                \App\Http\Controllers\ReportController::class,
                'historial'
            ]
        )->name('reportes.historial');

        Route::get(
            '/reportes/cobranza',
            [
                \App\Http\Controllers\ReportController::class,
                'cobranzaReport'
            ]
        )->name('reportes.cobranza');
        Route::get('/reportes/cobranza/pdf', [
            \App\Http\Controllers\ReportController::class,
            'cobranzaReportPdf'
        ])->name('reportes.cobranza.pdf');

        Route::get(
            '/reportes/historial/{id}/print',
            [
                \App\Http\Controllers\ReportController::class,
                'reprint'
            ]
        )->name('reportes.historial.print');

        Route::get(
            '/reportes/solicitud-material',
            [
                \App\Http\Controllers\ReportController::class,
                'createMaterialRequest'
            ]
        )->name('reportes.material-request.create');

        Route::post(
            '/reportes/solicitud-material/defaults',
            [
                \App\Http\Controllers\ReportController::class,
                'saveDefaults'
            ]
        )->name('reportes.material-request.defaults');

        Route::post(
            '/reportes/solicitud-material/print',
            [
                \App\Http\Controllers\ReportController::class,
                'printMaterialRequest'
            ]
        )->name('reportes.material-request.print');
    });


    /*
    |--------------------------------------------------------------------------
    | REPORTE REVOLVENTE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/reportes/revolvente',
        [
            \App\Http\Controllers\ReportController::class,
            'revolventeReport'
        ]
    )->name('reportes.revolvente.index');

    Route::get(
        '/reportes/revolvente/export',
        [
            \App\Http\Controllers\ReportController::class,
            'exportRevolvente'
        ]
    )->name('reportes.revolvente.export');


    /*
    |--------------------------------------------------------------------------
    | REQUERIMIENTOS
    |--------------------------------------------------------------------------
    |
    | IMPORTANTE:
    | Las rutas específicas se colocan ANTES del resource.
    |--------------------------------------------------------------------------
    */

    Route::get(
        'requirements/next-number',
        [
            \App\Http\Controllers\RequirementController::class,
            'getNextNumber'
        ]
    )->name('requirements.next-number');

    Route::post(
        'requirements/parse-xml',
        [
            \App\Http\Controllers\RequirementController::class,
            'parseXml'
        ]
    )->name('requirements.parse-xml');

    Route::get(
        'requirements/{requirement}/pdf',
        [
            \App\Http\Controllers\RequirementController::class,
            'downloadPdf'
        ]
    )->name('requirements.pdf');

    Route::get(
        'requirements/{requirement}/anexo-2/{employee}',
        [
            \App\Http\Controllers\RequirementController::class,
            'downloadAnexo2'
        ]
    )->name('requirements.anexo-2');

    Route::get(
        'requirements/{requirement}/cfe-relation',
        [
            \App\Http\Controllers\RequirementController::class,
            'downloadCfeRelation'
        ]
    )->name('requirements.cfe-relation');

    Route::get(
        'requirements/{requirement}/comprobacion-viaticos/{employee}',
        [
            \App\Http\Controllers\RequirementController::class,
            'downloadComprobacionViaticos'
        ]
    )->name('requirements.comprobacion-viaticos');

    Route::get(
        'requirements/{requirement}/bomberos-oficio',
        [
            \App\Http\Controllers\RequirementController::class,
            'downloadBomberosOficio'
        ]
    )->name('requirements.bomberos-oficio');

    Route::get(
        'requirements/{requirement}/revolvente-oficio',
        [
            \App\Http\Controllers\RequirementController::class,
            'downloadRevolventeOficio'
        ]
    )->name('requirements.revolvente-oficio');

    Route::get(
        'requirements/{requirement}/revolvente-anexo4',
        [
            \App\Http\Controllers\RequirementController::class,
            'downloadRevolventeAnexo4'
        ]
    )->name('requirements.revolvente-anexo4');

    Route::get(
        'requirements/{requirement}/revolvente-cedula',
        [
            \App\Http\Controllers\RequirementController::class,
            'downloadRevolvanteCedula'
        ]
    )->name('requirements.revolvente-cedula');

    Route::post(
        'requirements/import-bank-commissions',
        [
            \App\Http\Controllers\RequirementController::class,
            'importBankCommissions'
        ]
    )->name('requirements.import-bank-commissions');

    Route::get(
        'requirements/{requirement}/bank-commissions-pdf',
        [
            \App\Http\Controllers\RequirementController::class,
            'downloadBankCommissionsPdf'
        ]
    )->name('requirements.bank-commissions-pdf');


    /*
    |--------------------------------------------------------------------------
    | RESOURCE DE REQUIREMENTS
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'requirements',
        \App\Http\Controllers\RequirementController::class
    );


    /*
    |--------------------------------------------------------------------------
    | CFE
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/cfe/query',
        [
            \App\Http\Controllers\CfeQueryController::class,
            'index'
        ]
    )->name('cfe.query');

    Route::get(
        '/cfe/export',
        [
            \App\Http\Controllers\CfeQueryController::class,
            'export'
        ]
    )->name('cfe.export');


    /*
    |--------------------------------------------------------------------------
    | PAGOS
    |--------------------------------------------------------------------------
    */

    Route::get(
        'payments/requirement/{requirement}/pdf',
        [
            \App\Http\Controllers\PaymentController::class,
            'downloadRequirementPaymentsPdf'
        ]
    )->name('payments.requirement.pdf');

    Route::get(
        'payments/{payment}/pdf',
        [
            \App\Http\Controllers\PaymentController::class,
            'downloadPdf'
        ]
    )->name('payments.pdf');

    Route::resource(
        'payments',
        \App\Http\Controllers\PaymentController::class
    );


    /*
    |--------------------------------------------------------------------------
    | COMISIONES
    |--------------------------------------------------------------------------
    */

    Route::get(
        'commissions/{commission}/pdf',
        [
            \App\Http\Controllers\CommissionController::class,
            'printPdf'
        ]
    )->name('commissions.pdf');

    Route::resource(
        'commissions',
        \App\Http\Controllers\CommissionController::class
    );


    /*
    |--------------------------------------------------------------------------
    | PROVEEDORES
    |--------------------------------------------------------------------------
    */

    Route::resource(
        'providers',
        \App\Http\Controllers\ProviderController::class
    );


    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN GENERAL
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|configurar bomberos'
    ])->group(function () {

        Route::get(
            '/configuracion',
            [
                \App\Http\Controllers\SettingController::class,
                'index'
            ]
        )->name('settings.index');

        Route::post(
            '/configuracion',
            [
                \App\Http\Controllers\SettingController::class,
                'update'
            ]
        )->name('settings.update');
    });


    /*
    |--------------------------------------------------------------------------
    | VACACIONES
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|ver vacaciones'
    ])->group(function () {

        Route::get(
            '/vacaciones',
            [
                \App\Http\Controllers\VacationController::class,
                'index'
            ]
        )->name('vacations.index');

        Route::post(
            '/vacaciones',
            [
                \App\Http\Controllers\VacationController::class,
                'store'
            ]
        )->name('vacations.store');

        Route::get(
            '/vacaciones/solicitud/{solicitud}/pdf',
            [
                \App\Http\Controllers\VacationPdfController::class,
                'downloadRequest'
            ]
        )->name('vacations.pdf.request');


        /*
        |--------------------------------------------------------------------------
        | Bono de evaluación
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/evaluaciones/bono/check',
            [
                \App\Http\Controllers\EvaluationBonusController::class,
                'checkStatus'
            ]
        )->name('evaluation.bonus.check');

        Route::post(
            '/evaluaciones/bono',
            [
                \App\Http\Controllers\EvaluationBonusController::class,
                'store'
            ]
        )->name('evaluation.bonus.store');
    });


    /*
    |--------------------------------------------------------------------------
    | MÓDULO DE BOMBEROS
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | CAPTURA DE BOMBEROS
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|capturar bomberos'
    ])->get('/firefighters/capture', function () {

        return Inertia::render('Firefighters/Capture', [
            'communities' =>
                \App\Models\Community::all(),

            'firefighters' =>
                \App\Models\Firefighter::with('community')->get(),
        ]);

    })->name('firefighters.capture');


    /*
    |--------------------------------------------------------------------------
    | RECIBIR BOMBEROS
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|recibir bomberos'
    ])->get('/firefighters/receive', function () {

        return Inertia::render('Firefighters/Receive', [
            'communities' =>
                \App\Models\Community::all(),

            'settings' =>
                \App\Models\FirefighterSetting::first(),
        ]);

    })->name('firefighters.receive');


    /*
    |--------------------------------------------------------------------------
    | REPORTE DE BOMBEROS
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|reportes bomberos'
    ])->get('/firefighters/report', function (
        \Illuminate\Http\Request $request
    ) {

        $requirements = \App\Models\Capture::select(
            'year',
            'requirement_number',
            'requirement_type'
        )
            ->whereNotNull('year')
            ->whereNotNull('requirement_number')
            ->where('requirement_type', 'bomberos')
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('requirement_number', 'desc')
            ->get();


        $captures = [];

        if (
            $request->filled('year') &&
            $request->filled('requirement_number')
        ) {

            $captures = \App\Models\Capture::with([
                'community',
                'firefighter'
            ])
                ->where('year', $request->year)
                ->where(
                    'requirement_number',
                    $request->requirement_number
                )
                ->where('requirement_type', 'bomberos')
                ->get();
        }


        return Inertia::render('Firefighters/Report', [
            'requirements' =>
                $requirements,

            'captures' =>
                $captures,

            'filters' =>
                $request->only([
                    'year',
                    'requirement_number'
                ]),

            'settings' =>
                \App\Models\FirefighterSetting::first(),

            'requirementTypes' =>
                \App\Models\Capture::REQUIREMENT_TYPES,
        ]);

    })->name('firefighters.report');


    /*
    |--------------------------------------------------------------------------
    | PDF DE BOMBEROS
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|reportes bomberos'
    ])->get(
        '/report/firefighters/pdf',
        [
            \App\Http\Controllers\FirefighterReportPdfController::class,
            'download'
        ]
    )->name('firefighters.report.pdf');


    /*
    |--------------------------------------------------------------------------
    | CONSULTA DE BOMBEROS
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|reportes bomberos'
    ])->group(function () {

        Route::get(
            '/firefighters/query',
            [
                \App\Http\Controllers\FirefighterQueryController::class,
                'index'
            ]
        )->name('firefighters.query');

        Route::get(
            '/firefighters/export',
            [
                \App\Http\Controllers\FirefighterQueryController::class,
                'export'
            ]
        )->name('firefighters.export');
    });


    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN DE BOMBEROS
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|configurar bomberos'
    ])->group(function () {

        Route::get(
            '/firefighters/settings',
            [
                \App\Http\Controllers\FirefighterSettingController::class,
                'index'
            ]
        )->name('firefighters.settings');

        Route::post(
            '/firefighters/settings',
            [
                \App\Http\Controllers\FirefighterSettingController::class,
                'update'
            ]
        )->name('firefighters.settings.update');
    });


    /*
    |--------------------------------------------------------------------------
    | COMUNIDADES
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|ver comunidades'
    ])->get('/firefighters/communities', function () {

        return Inertia::render('Firefighters/Communities', [
            'communities' =>
                \App\Models\Community::all(),
        ]);

    })->name('firefighters.communities');


    /*
    |--------------------------------------------------------------------------
    | LISTA DE BOMBEROS
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|ver bomberos'
    ])->get('/firefighters/list', function () {

        return Inertia::render('Firefighters/Firefighters', [
            'firefighters' =>
                \App\Models\Firefighter::with('community')->get(),

            'communities' =>
                \App\Models\Community::all(),
        ]);

    })->name('firefighters.list');


    /*
    |--------------------------------------------------------------------------
    | CRUD DE COMUNIDADES
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|ver comunidades'
    ])->group(function () {

        Route::resource(
            'communities',
            \App\Http\Controllers\CommunityController::class
        );
    });


    /*
    |--------------------------------------------------------------------------
    | CRUD DE BOMBEROS
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|ver bomberos'
    ])->group(function () {

        Route::resource(
            'firefighters',
            \App\Http\Controllers\FirefighterController::class
        );
    });


    /*
    |--------------------------------------------------------------------------
    | CAPTURAS
    |--------------------------------------------------------------------------
    */

    Route::middleware([
        'role_or_permission:Administrador|capturar bomberos'
    ])->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Rutas específicas
        |--------------------------------------------------------------------------
        */

        Route::post(
            'captures/assign-requirement',
            [
                \App\Http\Controllers\CaptureController::class,
                'assignRequirement'
            ]
        )->name('captures.assign-requirement');

        Route::get(
            'captures/requirements',
            [
                \App\Http\Controllers\CaptureController::class,
                'getRequirements'
            ]
        )->name('captures.requirements');

        Route::get(
            'captures/summary',
            [
                \App\Http\Controllers\CaptureController::class,
                'getSummaryByAssignment'
            ]
        )->name('captures.summary');

        Route::get(
            'captures/next-requirement',
            [
                \App\Http\Controllers\CaptureController::class,
                'getNextRequirementNumber'
            ]
        )->name('captures.next-requirement');

        Route::post(
            'captures/import',
            [
                \App\Http\Controllers\CaptureImportController::class,
                'import'
            ]
        )->name('captures.import');

        Route::get(
            'captures/import/template',
            [
                \App\Http\Controllers\CaptureImportController::class,
                'downloadTemplate'
            ]
        )->name('captures.import.template');


        /*
        |--------------------------------------------------------------------------
        | Resource
        |--------------------------------------------------------------------------
        */

        Route::resource(
            'captures',
            \App\Http\Controllers\CaptureController::class
        );
    });


    /*
    |--------------------------------------------------------------------------
    | CONFIGURACIÓN JSON PARA FRONTEND
    |--------------------------------------------------------------------------
    |
    | Se mantiene autenticada porque puede ser utilizada por
    | las pantallas de Bomberos.
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/firefighter-settings-json',
        [
            \App\Http\Controllers\FirefighterSettingController::class,
            'index'
        ]
    );
});


/*
|--------------------------------------------------------------------------
| AUTENTICACIÓN
|--------------------------------------------------------------------------
*/

require __DIR__ . '/daily_incomes.php';
require __DIR__ . '/auth.php';

