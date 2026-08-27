<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Requirement;
use App\Models\RequirementItem;
use App\Models\DailyIncome;
use App\Models\IncomePolicy;
use App\Models\Empleado;
use App\Exports\RevolventeReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    public function cobranzaReport(Request $request)
    {
        $validated = $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        $incomes = collect();
        $policies = collect();
        if (!empty($validated['fecha_desde']) && !empty($validated['fecha_hasta'])) {
            $from = $validated['fecha_desde'];
            $to = $validated['fecha_hasta'];
            $incomes = DailyIncome::with('details.movement.bank')
                ->whereBetween('income_date', [$from, $to])
                ->orderBy('income_date')
                ->get();
            $policies = IncomePolicy::with('details')
                ->whereDate('start_date', '<=', $to)
                ->whereDate('end_date', '>=', $from)
                ->where('policy_type', 'Ingreso')
                ->orderBy('start_date')
                ->get();
        }

        $banks = $this->buildBankColumns($incomes);

        $rows = $incomes->flatMap(function ($income) use ($policies, $banks) {
            $incomeDate = $income->income_date instanceof \Carbon\CarbonInterface
                ? $income->income_date->toDateString()
                : (string) $income->income_date;
            $policy = $policies->first(fn ($item) => $incomeDate >= $item->start_date->toDateString() && $incomeDate <= $item->end_date->toDateString());
            $policyValues = $policy ? $policy->details->pluck('amount')->map(fn ($value) => (float) $value)->values() : collect();

            return $income->details->map(function ($detail) use ($incomeDate, $policy, $banks, $policyValues) {
                $amount = (float) ($detail->movement?->credit_amount ?? 0);
                $bankId = (string) ($detail->movement?->bank_id ?? '');
                $bankAmounts = $banks->mapWithKeys(fn ($bank) => [(string) $bank['id'] => $bank['id'] === $bankId ? $amount : 0])->all();

                return ['id' => $detail->id, 'date' => $incomeDate, 'concept' => 'REC DEL MAC', 'cashier_difference' => 0, 'banks' => $bankAmounts, 'total_banks' => $amount, 'policy_number' => $policy?->policy_number ?: 'Sin póliza', 'policy_amount' => $policy ? (float) $policy->amount : 0, 'policy_values' => $policyValues];
            });
        })->values();

        $rows = $this->distributePolicyLines($rows, $banks);

        $draefTotal = $incomes->sum(fn ($income) => (float) $income->draef_amount);
        $draefSubtotalTotal = $incomes->sum(fn ($income) => (float) $income->draef_subtotal);
        $draefIvaTotal = $incomes->sum(fn ($income) => (float) $income->draef_iva);
        $policyTotal = $policies->where('policy_type', 'Ingreso')->sum(fn ($policy) => (float) $policy->amount);
        $draefPayments = $incomes->filter(fn ($income) => (float) $income->draef_amount > 0)->map(fn ($income) => [
            'date' => $income->income_date instanceof \Carbon\CarbonInterface ? $income->income_date->toDateString() : (string) $income->income_date,
            'subtotal' => (float) $income->draef_subtotal,
            'iva' => (float) $income->draef_iva,
            'amount' => (float) $income->draef_amount,
        ])->values();

        return Inertia::render('Reports/Cobranza/Index', [
            'rows' => $rows,
            'banks' => $banks,
            'draefTotal' => $draefTotal,
            'draefSubtotalTotal' => $draefSubtotalTotal,
            'draefIvaTotal' => $draefIvaTotal,
            'policyTotal' => $policyTotal,
            'draefPayments' => $draefPayments,
            'filters' => $request->only(['fecha_desde', 'fecha_hasta']),
        ]);
    }

    public function cobranzaReportPdf(Request $request)
    {
        $data = $request->validate([
            'fecha_desde' => 'required|date',
            'fecha_hasta' => 'required|date|after_or_equal:fecha_desde',
        ]);

        $incomes = DailyIncome::with('details.movement.bank')
            ->whereBetween('income_date', [$data['fecha_desde'], $data['fecha_hasta']])
            ->orderBy('income_date')
            ->get();
        $policies = IncomePolicy::with('details')
            ->whereDate('start_date', '<=', $data['fecha_hasta'])
            ->whereDate('end_date', '>=', $data['fecha_desde'])
            ->where('policy_type', 'Ingreso')
            ->orderBy('start_date')
            ->get();
        $banks = $this->buildBankColumns($incomes);
        $rows = $incomes->flatMap(function ($income) use ($banks, $policies) {
            $incomeDate = $income->income_date instanceof \Carbon\CarbonInterface
                ? $income->income_date->toDateString()
                : (string) $income->income_date;
            $policy = $policies->first(fn ($item) => $incomeDate >= $item->start_date->toDateString() && $incomeDate <= $item->end_date->toDateString());
            $policyValues = $policy ? $policy->details->pluck('amount')->map(fn ($value) => (float) $value)->values() : collect();

            return $income->details->map(function ($detail) use ($incomeDate, $policy, $banks, $policyValues) {
                $amount = (float) ($detail->movement?->credit_amount ?? 0);
                $bankId = (string) ($detail->movement?->bank_id ?? '');
                $amounts = $banks->mapWithKeys(fn ($bank) => [(string) $bank['id'] => $bank['id'] === $bankId ? $amount : 0])->all();

                return ['date' => $incomeDate, 'amounts' => $amounts, 'total' => $amount, 'policy' => $policy?->amount ?? 0, 'policy_number' => $policy?->policy_number ?? 'Sin póliza', 'policy_values' => $policyValues];
            });
        })->values();

        $rows = $this->distributePolicyLines($rows, $banks, true);
        $bankTotals = $banks->mapWithKeys(fn ($bank) => [(string) $bank['id'] => $rows->sum(fn ($row) => $row['amounts'][(string) $bank['id']] ?? 0)])->all();
        $totalBanks = $rows->sum('total');
        $policyTotal = $policies->sum(fn ($policy) => (float) $policy->amount);
        $draefTotal = $incomes->sum(fn ($income) => (float) $income->draef_amount);
        $draefSubtotalTotal = $incomes->sum(fn ($income) => (float) $income->draef_subtotal);
        $draefIvaTotal = $incomes->sum(fn ($income) => (float) $income->draef_iva);
        $draefPayments = $incomes->filter(fn ($income) => (float) $income->draef_amount > 0)->map(fn ($income) => [
            'date' => $income->income_date instanceof \Carbon\CarbonInterface ? $income->income_date->toDateString() : (string) $income->income_date,
            'subtotal' => (float) $income->draef_subtotal,
            'iva' => (float) $income->draef_iva,
            'amount' => (float) $income->draef_amount,
        ])->values();

        $subgerenteAdministrativo = Empleado::activos()->where('puesto', 'LIKE', '%SUBGERENTE ADMINISTRATIVO%')->first();
        $subgerenteComercial = Empleado::activos()->where('puesto', 'LIKE', '%SUBGERENTE COMERCIAL%')->first();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.cobranza', compact('rows', 'banks', 'bankTotals', 'totalBanks', 'policyTotal', 'draefTotal', 'draefSubtotalTotal', 'draefIvaTotal', 'draefPayments', 'data', 'subgerenteAdministrativo', 'subgerenteComercial'));
        $pdf->setPaper('letter', 'landscape');
        return $pdf->download('Relacion_Ingresos_Recaudacion_' . $data['fecha_desde'] . '_' . $data['fecha_hasta'] . '.pdf');
    }

    private function buildBankColumns($incomes)
    {
        $banks = $incomes->flatMap(fn ($income) => $income->details->map(fn ($detail) => $detail->movement?->bank))
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values()
            ->map(fn ($bank) => [
                'id' => (string) $bank->id,
                'name' => $bank->name ?: 'Sin banco',
                'account_number' => $bank->account_number,
            ]);

        // Siempre se muestran mínimo 4 columnas de banco, aunque no tengan movimientos.
        for ($i = $banks->count(); $i < 4; $i++) {
            $banks->push(['id' => 'sin-banco-' . $i, 'name' => 'Sin banco', 'account_number' => null]);
        }

        return $banks;
    }

    /**
     * Reparte los valores de cada póliza uno por renglón: el primero muestra el número,
     * los siguientes un valor cada uno, y en cero cuando ya no hay valores pendientes.
     */
    private function distributePolicyLines($rows, $banks, bool $forPdf = false)
    {
        $bankIds = $banks->pluck('id')->all();
        $rowsArray = $rows->values()->all();
        $result = collect();
        $index = 0;
        $count = count($rowsArray);

        while ($index < $count) {
            $policyNumber = $rowsArray[$index]['policy_number'];
            $groupStart = $index;
            while ($index < $count && $rowsArray[$index]['policy_number'] === $policyNumber) {
                $index++;
            }
            $groupRows = array_slice($rowsArray, $groupStart, $index - $groupStart);

            $values = $groupRows[0]['policy_values'] ?? collect();
            $values = $values instanceof \Illuminate\Support\Collection ? $values->all() : $values;

            $lines = [];
            if ($policyNumber !== 'Sin póliza') {
                $lines[] = ['label' => true, 'text' => $policyNumber];
            }
            foreach ($values as $value) {
                $lines[] = ['label' => false, 'value' => $value];
            }

            foreach ($groupRows as $position => $groupRow) {
                unset($groupRow['policy_values']);
                $groupRow['policy_line'] = $lines[$position] ?? ['label' => false, 'value' => 0];
                $result->push($groupRow);
            }

            for ($extra = count($groupRows); $extra < count($lines); $extra++) {
                $filler = $forPdf
                    ? ['date' => null, 'amounts' => array_fill_keys($bankIds, null), 'total' => null, 'policy' => 0, 'policy_number' => $policyNumber]
                    : ['id' => 'poliza-' . $policyNumber . '-' . $extra, 'date' => null, 'concept' => '', 'cashier_difference' => null, 'banks' => array_fill_keys($bankIds, null), 'total_banks' => null, 'policy_number' => $policyNumber, 'policy_amount' => 0];
                $filler['policy_line'] = $lines[$extra];
                $result->push($filler);
            }
        }

        return $result->values();
    }

    public function revolventeReport(Request $request)
    {
        $requirements = Requirement::where('type', 'revolvente')
            ->select('id', 'year', 'requirement_number', 'revolving_fund_number', 'description', 'total')
            ->orderBy('year', 'desc')
            ->orderBy('requirement_number', 'desc')
            ->get();

        $selectedRequirementId = $request->requirement_id;
        $items = [];
        $selectedRequirement = null;

        if ($selectedRequirementId) {
            $selectedRequirement = Requirement::with(['items.partida', 'coordinator', 'director', 'manager', 'elaborator'])
                ->findOrFail($selectedRequirementId);
            $items = $selectedRequirement->items;
        }

        return Inertia::render('Reports/RevolventeReport/Index', [
            'requirements' => $requirements,
            'items' => $items,
            'selectedRequirement' => $selectedRequirement,
            'filters' => $request->only(['requirement_id']),
        ]);
    }

    public function exportRevolvente(Request $request)
    {
        $request->validate([
            'requirement_id' => 'required|exists:requirements,id',
        ]);

        $selectedRequirement = Requirement::with(['items.partida'])->findOrFail($request->requirement_id);
        $items = $selectedRequirement->items;

        return Excel::download(
            new RevolventeReportExport($items, $selectedRequirement),
            'Reporte_Revolvente_' . $selectedRequirement->revolving_fund_number . '.xlsx'
        );
    }

    /**
     * Display a listing of the available reports.
     */
    public function index()
    {
        return Inertia::render('Reports/Index');
    }

    /**
     * Show the report history/audit log.
     */
    public function historial(Request $request)
    {
        $query = \App\Models\ReporteBitacora::with(['user', 'empleado'])
            ->orderBy('created_at', 'desc');

        // Filter: Regular users only see their own reports
        $user = auth()->user();
        if (!$user->hasRole('Administrador')) {
            $query->where('user_id', $user->id);
        }

        // Search filter
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('destinatario_nombre', 'like', "%{$search}%")
                    ->orWhere('solicitante_nombre', 'like', "%{$search}%")
                    ->orWhere('solicitante_departamento', 'like', "%{$search}%");
            });
        }

        // Date range filter
        if ($request->filled('fecha_desde')) {
            $query->where('fecha_reporte', '>=', $request->fecha_desde);
        }
        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_reporte', '<=', $request->fecha_hasta);
        }

        $reportes = $query->paginate(20)->withQueryString();

        return Inertia::render('Reports/Historial/Index', [
            'reportes' => $reportes,
            'filters' => $request->only(['search', 'fecha_desde', 'fecha_hasta']),
        ]);
    }

    /**
     * Re-print a specific report from history.
     */
    /**
     * Re-print a specific report from history.
     */
    public function reprint(Request $request, $id)
    {
        $bitacora = \App\Models\ReporteBitacora::findOrFail($id);

        // Fetch Branding Settings
        $keys = ['logo_qroo', 'logo_unidos', 'logo_capa', 'footer_organismo', 'footer_direccion', 'footer_telefono', 'footer_email', 'footer_imagen'];
        $settings = \App\Models\Setting::whereIn('key', $keys)->pluck('value', 'key');

        $data = $bitacora->datos_completos;
        $fecha_formateada = $this->formatDate($data['fecha'] ?? $bitacora->fecha_reporte);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.material_request', [
            'data' => $data,
            'settings' => $this->processImagesForPdf($settings),
            'fecha_formateada' => $fecha_formateada,
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('Solicitud_Material_' . ($data['fecha'] ?? 'date') . '.pdf');
    }

    /**
     * Show the form for creating a new Material Request report.
     */
    public function createMaterialRequest()
    {
        // Fetch materials with their units for the selection dropdown
        $materiales = Material::with('unidadMedida')
            ->orderBy('articulo')
            ->get()
            ->map(function ($material) {
                return [
                    'id' => $material->id,
                    'articulo' => $material->articulo,
                    'unidad' => $material->unidadMedida ? $material->unidadMedida->nombre : '',
                ];
            });

        // Fetch user's default materials
        $user = auth()->user();
        $userDefaults = [];
        $materialesDefault = [];
        $hasDefaults = false;

        if ($user) {
            $userDefaults = $user->defaultMaterials()
                ->with('unidadMedida')
                ->get()
                ->map(function ($material) {
                    return [
                        'id' => $material->id,
                        'articulo' => $material->articulo,
                        'unidad' => $material->unidadMedida ? $material->unidadMedida->nombre : '',
                        'cantidad' => $material->pivot->cantidad,
                    ];
                });

            if ($userDefaults->isNotEmpty()) {
                $hasDefaults = true;
                $materialesDefault = $userDefaults;
            }
        }

        // Fetch default manager (active employee marked as gerente)
        $manager = null;
        $gerenteEmpleado = \App\Models\Empleado::where('activo', true)
            ->where('es_gerente', true)
            ->first();

        if ($gerenteEmpleado) {
            $manager = ['nombre' => $gerenteEmpleado->nombre, 'puesto' => $gerenteEmpleado->puesto];
        }

        // Get authenticated user's employee data
        $empleadoActual = null;
        if ($user && $user->empleado_id) {
            $empleadoActual = \App\Models\Empleado::find($user->empleado_id);
        }

        return Inertia::render('Reports/MaterialRequest/Create', [
            'materiales' => $materiales,
            'materialesDefault' => $materialesDefault,
            'hasDefaults' => $hasDefaults,
            'manager' => $manager,
            'empleadoActual' => $empleadoActual ? [
                'nombre' => $empleadoActual->nombre,
                'puesto' => $empleadoActual->puesto,
                'departamento' => $empleadoActual->departamento,
            ] : null,
        ]);
    }

    /**
     * Save user's default materials preference.
     */
    public function saveDefaults(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.cantidad' => 'nullable|numeric|min:0.01',
        ]);

        $user = auth()->user();
        $defaults = [];

        foreach ($validated['items'] as $item) {
            $defaults[$item['material_id']] = ['cantidad' => $item['cantidad'] ?? 1];
        }

        $user->defaultMaterials()->sync($defaults);

        return redirect()->back()->with('success', 'Lista de materiales favoritos guardada correctamente.');
    }


    /**
     * Render the print view for the Material Request.
     */
    public function printMaterialRequest(Request $request)
    {
        $validated = $request->validate([
            'fecha' => 'required|date',
            'destinatario_nombre' => 'required|string',
            'destinatario_cargo' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            // We can also pass custom names if the user wants to override the DB name
            'items.*.custom_articulo' => 'nullable|string',
            'items.*.custom_unidad' => 'nullable|string',
            'solicitante_nombre' => 'required|string',
            'solicitante_cargo' => 'required|string',
            'solicitante_departamento' => 'nullable|string',
        ]);

        // Guardar en bitácora
        \App\Models\ReporteBitacora::create([
            'user_id' => auth()->id(),
            'empleado_id' => auth()->user()->empleado_id,
            'fecha_reporte' => $validated['fecha'],
            'destinatario_nombre' => $validated['destinatario_nombre'],
            'destinatario_cargo' => $validated['destinatario_cargo'],
            'solicitante_nombre' => $validated['solicitante_nombre'],
            'solicitante_cargo' => $validated['solicitante_cargo'],
            'solicitante_departamento' => $validated['solicitante_departamento'] ?? null,
            'materiales' => array_map(function ($item) {
                return [
                    'articulo' => $item['custom_articulo'] ?? 'N/A',
                    'cantidad' => $item['cantidad'],
                    'unidad' => $item['custom_unidad'] ?? 'N/A',
                ];
            }, $validated['items']),
            'datos_completos' => $validated,
        ]);

        // Fetch Branding Settings
        $keys = ['logo_qroo', 'logo_unidos', 'logo_capa', 'footer_organismo', 'footer_direccion', 'footer_telefono', 'footer_email', 'footer_imagen'];
        $settings = \App\Models\Setting::whereIn('key', $keys)->pluck('value', 'key');

        $fecha_formateada = $this->formatDate($validated['fecha']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.material_request', [
            'data' => $validated,
            'settings' => $this->processImagesForPdf($settings),
            'fecha_formateada' => $fecha_formateada,
        ]);

        $pdf->setPaper('letter', 'portrait');

        return $pdf->download('Solicitud_Material_' . $validated['fecha'] . '.pdf');
    }

    private function formatDate($dateString)
    {
        $date = \Carbon\Carbon::parse($dateString);
        $monthNames = [
            "enero",
            "febrero",
            "marzo",
            "abril",
            "mayo",
            "junio",
            "julio",
            "agosto",
            "septiembre",
            "octubre",
            "noviembre",
            "diciembre"
        ];
        $month = $monthNames[$date->month - 1]; // Carbon month is 1-indexed
        return "José María Morelos, Quintana Roo, " . $date->day . " de " . $month . " del " . $date->year;
    }

    /**
     * Convert image paths in settings to Base64 data URIs for robust PDF generation.
     */
    private function processImagesForPdf($settings)
    {
        $imageKeys = ['logo_qroo', 'logo_unidos', 'logo_capa', 'footer_imagen'];

        foreach ($imageKeys as $key) {
            if (isset($settings[$key]) && $settings[$key]) {
                $path = storage_path('app/public/' . $settings[$key]);
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $settings[$key] = $base64;
                }
            }
        }

        return $settings;
    }
}
