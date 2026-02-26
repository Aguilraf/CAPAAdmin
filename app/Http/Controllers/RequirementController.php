<?php

namespace App\Http\Controllers;

use App\Models\Requirement;
use App\Models\RequirementItem;
use App\Models\Empleado;
use App\Models\Partida;
use App\Models\Capitulo;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class RequirementController extends Controller
{
    public function index(Request $request)
    {
        $query = Requirement::query()
            ->with(['coordinator', 'director', 'manager', 'elaborator', 'travelAllowance.commissioners'])
            ->orderBy('year', 'desc')
            ->orderBy('requirement_number', 'desc');

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('requirement_number', 'like', "%{$search}%");
            });
        }

        return Inertia::render('Requirements/Index', [
            'requirements' => $query->paginate(15)->withQueryString(),
            'filters' => $request->only(['year', 'type', 'search']),
            'types' => Requirement::TYPES,
        ]);
    }

    public function create(Request $request)
    {
        $year = date('Y');
        $type = $request->get('type', 'bomberos');
        $group = Requirement::getNumberingGroup($type);
        $typesInGroup = Requirement::getTypesByGroup($group);

        $latest = Requirement::where('year', $year)
            ->whereIn('type', $typesInGroup)
            ->max('requirement_number');
        $nextNumber = $latest ? $latest + 1 : 1;

        // Fetch Data Directly for Props (No API)
        $employees = Empleado::activos()->select('id', 'nombre', 'primer_nombre', 'primer_apellido', 'segundo_apellido', 'puesto', 'cargo', 'rfc', 'clave', 'nivel', 'departamento', 'categoria', 'tipo_plaza', 'jefe_inmediato', 'banco', 'clabe', 'organismo_id')->get();
        $capitulos = Capitulo::activos()->select('id', 'codigo', 'nombre')->get(); // Fetch chapters

        // Optimización: Si partidas son muchas, podríamos mandar solo las más usadas o categorías, 
        // pero "No API" implica mandar todo o manejar filtrado por recarga de página.
        // Mandaremos todo seleccionado campos mínimos.
        // Mandaremos todo seleccionado campos mínimos.
        $partidas = Partida::activos()->with('capitulo')->select('id', 'codigo', 'nombre', 'partida_generica', 'capitulo_id')->get();
        $vehicles = \App\Models\Vehicle::where('active', true)->select('id', 'brand', 'model_year', 'plate_number', 'organismo_id')->get();

        // Default Signatories by Job Title
        $defaultCoordinador = Empleado::where('puesto', 'LIKE', '%COORDINADOR ADMINISTRATIVO, FINANCIERO Y DE ARCHIVO%')->first();
        // Use flexible matching for Director to handle potential typos ("RECUROS" vs "RECURSOS")
        $defaultDirector = Empleado::where('puesto', 'LIKE', '%DIRECTOR DE REC%MATERIALES%')->first();

        // Fetch year legend for current year (Create)
        $leyenda = \App\Models\Leyenda::where('anio', $year)->first();
        $defaultLegend = $leyenda ? $leyenda->texto : '';

        // Bomberos Specific Defaults
        $bomberosCoordinador = Empleado::where('puesto', 'LIKE', '%COORDINADOR COMERCIAL%')
            ->where('activo', true)
            ->orderByDesc('id')
            ->first();
        $bomberosSubgerente = Empleado::where('puesto', 'LIKE', '%SUBGERENTE COMERCIAL%')
            ->where('activo', true)
            ->orderByDesc('id')
            ->first();

        // Fetch active travel allowance rates for current year
        $travelAllowanceRates = \App\Models\TravelAllowanceRate::with('partida')
            ->active()
            ->forYear($year)
            ->get();

        // Set default months based on logic: we pay one month delayed.
        // If current is February, default should be January.
        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];
        $currentMonthNum = (int) date('n');
        $prevMonthNum = $currentMonthNum === 1 ? 12 : $currentMonthNum - 1;
        $prevMonthYear = $currentMonthNum === 1 ? $year - 1 : $year;

        return Inertia::render('Requirements/Create', [
            'nextNumber' => $nextNumber,
            'year' => $year,
            'employees' => $employees,
            'capitulos' => $capitulos,
            'partidas' => $partidas,
            'types' => Requirement::TYPES,
            'defaultSignatories' => [
                'coordinator_id' => $defaultCoordinador ? $defaultCoordinador->id : '',
                'director_id' => $defaultDirector ? $defaultDirector->id : '',
            ],
            'defaultBomberos' => [
                'coordinator_id' => $bomberosCoordinador ? $bomberosCoordinador->id : '',
                'subgerente_id' => $bomberosSubgerente ? $bomberosSubgerente->id : '',
            ],
            'defaultLegend' => $defaultLegend, // Pass legend
            'vehicles' => $vehicles,
            'travelAllowanceRates' => $travelAllowanceRates,
            'defaultMonths' => [
                'month_billed' => $months[$prevMonthNum],
                'year_billed' => $prevMonthYear,
                'month_charged' => $months[$currentMonthNum],
                'year_charged' => $year,
            ],
            'monthsList' => array_values($months)
        ]);
    }

    public function getNextNumber(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $type = $request->get('type', 'estandard');

        $group = Requirement::getNumberingGroup($type);
        $typesInGroup = Requirement::getTypesByGroup($group);

        $latest = Requirement::where('year', $year)
            ->whereIn('type', $typesInGroup)
            ->max('requirement_number');

        return response()->json([
            'nextNumber' => $latest ? $latest + 1 : 1
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'requirement_number' => 'required|integer',
            'type' => 'required|string',
            'assignment_date' => 'nullable|date',
            'oficio_number' => 'nullable|string',
            'coordinator_id' => 'nullable|exists:empleados,id',
            'director_id' => 'nullable|exists:empleados,id',
            'manager_id' => 'nullable|exists:empleados,id',
            'elaborator_id' => 'nullable|exists:empleados,id',
            'month_charged' => 'nullable|string',
            'year_charged' => 'nullable|integer',
            'month_billed' => 'nullable|string',
            'year_billed' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.partida_id' => 'required|exists:partidas,id',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'items.*.employee_id' => 'nullable|exists:empleados,id',
            'items.*.uuid' => 'nullable|string',
            'items.*.invoice_folio' => 'nullable|string',
            'items.*.invoice_date' => 'nullable|date',
            'items.*.provider_rfc' => 'nullable|string',
            'items.*.provider_name' => 'nullable|string',
            'items.*.invoice_subtotal' => 'nullable|numeric|min:0',
            'items.*.invoice_iva' => 'nullable|numeric|min:0',
            'items.*.invoice_retention_isr' => 'nullable|numeric|min:0',
            'items.*.invoice_retention_iva' => 'nullable|numeric|min:0',
            'items.*.invoice_total' => 'nullable|numeric|min:0',
            'cfe_receipts' => 'nullable|array',
            'cfe_receipts.*.uuid' => 'nullable|string',
            'cfe_receipts.*.rpu' => 'nullable|string',
            'cfe_receipts.*.description' => 'nullable|string',
            'cfe_receipts.*.period_start' => 'nullable|date',
            'cfe_receipts.*.period_end' => 'nullable|date',
            'cfe_receipts.*.subtotal' => 'nullable|numeric',
            'cfe_receipts.*.iva' => 'nullable|numeric',
            'cfe_receipts.*.rounding' => 'nullable|numeric',
            'cfe_receipts.*.total' => 'nullable|numeric',

            // Viaticos Validation
            'commission_summary_legend' => 'nullable|string',
            'exercise_year' => 'nullable|integer',
            'quarter' => 'nullable|string|in:I,II,III,IV',
            'commissioner_id' => 'nullable|exists:empleados,id',
            'commissioner_ids' => 'nullable|array',
            'commissioner_ids.*' => 'exists:empleados,id',
            'origin_country' => 'nullable|string',
            'origin_state' => 'nullable|string',
            'origin_city' => 'nullable|string',
            'destination_country' => 'nullable|string',
            'destination_state' => 'nullable|string',
            'destination_city' => 'nullable|string',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'days_duration' => 'nullable|integer',
            'half_day_payment' => 'nullable|boolean',
            'justification' => 'nullable|string',
            'report_date' => 'nullable|date',
            'report_link' => 'nullable|string',
            'has_viaticos' => 'nullable|boolean',
            'viaticos_partida_id' => 'nullable|exists:partidas,id',
            'has_pasaje' => 'nullable|boolean',
            'pasaje_partida_id' => 'nullable|exists:partidas,id',
            'has_hospedaje' => 'nullable|boolean',
            'hospedaje_partida_id' => 'nullable|exists:partidas,id',
            'transport_type' => 'nullable|string|in:Oficial,Particular,Publico',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'invoice_folio' => 'nullable|string',
            'invoice_date' => 'nullable|date',
            'provider_rfc' => 'nullable|string',
            'provider_name' => 'nullable|string',
            'uuid' => 'nullable|string',
            'viaticos_amount' => 'nullable|numeric|min:0',
            'pasaje_amount' => 'nullable|numeric|min:0',
            'hospedaje_amount' => 'nullable|numeric|min:0',
            'invoice_subtotal' => 'nullable|numeric',
            'invoice_iva' => 'nullable|numeric',
            'invoice_isr' => 'nullable|numeric',
            'invoice_retention_iva' => 'nullable|numeric',
            'invoice_total' => 'nullable|numeric',
            'commissioners_details' => 'nullable|array',
            'commissioners_details.*.id' => 'required|exists:empleados,id',
            'commissioners_details.*.oficio_number' => 'nullable|string',
            'commissioners_details.*.report_date' => 'nullable|date',
            'commissioners_details.*.report_link' => 'nullable|string',
            'firefighter_folio' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated) {
            // Calculation Logic
            if ($validated['type'] === 'viaticos' || $validated['type'] === 'bomberos') {
                // Viaticos Logic: Amounts are considered total or specific as entered
                $subtotal = collect($validated['items'])->sum('amount');
                $iva = 0; // We do not auto-calculate IVA for Viaticos
                $total = $subtotal;
            } elseif (!empty($validated['cfe_receipts'])) {
                // CFE Logic: Sum from receipts table for exact precision
                $subtotal = collect($validated['cfe_receipts'])->sum('subtotal');
                $iva = collect($validated['cfe_receipts'])->sum('iva');
                $total = collect($validated['cfe_receipts'])->sum('total');
            } else {
                // Standard Logic
                $subtotal = collect($validated['items'])->sum('amount');
                $iva = $subtotal * 0.16;
                $total = $subtotal + $iva;
            }

            $requirement = Requirement::create([
                ...$validated,
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
                'status' => 'pending'
            ]);

            // Save Items
            foreach ($validated['items'] as $item) {
                $requirement->items()->create([
                    'partida_id' => $item['partida_id'],
                    'description' => $item['description'] ?? null,
                    'amount' => $item['amount'],
                    'employee_id' => $item['employee_id'] ?? null,
                    'uuid' => $item['uuid'] ?? null,
                    'invoice_folio' => $item['invoice_folio'] ?? null,
                    'invoice_date' => $item['invoice_date'] ?? null,
                    'provider_rfc' => $item['provider_rfc'] ?? null,
                    'provider_name' => $item['provider_name'] ?? null,
                    'invoice_subtotal' => $item['invoice_subtotal'] ?? null,
                    'invoice_iva' => $item['invoice_iva'] ?? null,
                    'invoice_retention_isr' => $item['invoice_retention_isr'] ?? null,
                    'invoice_retention_iva' => $item['invoice_retention_iva'] ?? null,
                    'invoice_total' => $item['invoice_total'] ?? null,
                ]);
            }

            // Save CFE Receipts
            if (!empty($validated['cfe_receipts'])) {
                foreach ($validated['cfe_receipts'] as $receipt) {
                    $requirement->cfeReceipts()->create($receipt);
                }
            }

            // Save Viaticos (Travel Allowance)
            if ($validated['type'] === 'viaticos') {
                $travelAllowance = $requirement->travelAllowance()->create([
                    'oficio_number' => $validated['oficio_number'] ?? null,
                    'commission_summary_legend' => $validated['commission_summary_legend'] ?? null,
                    'exercise_year' => $validated['exercise_year'] ?? null,
                    'quarter' => $validated['quarter'] ?? null,
                    'commissioner_id' => $validated['commissioner_id'] ?? null, // Keep for backward compatibility or primary
                    'origin_country' => $validated['origin_country'] ?? 'México',
                    'origin_state' => $validated['origin_state'] ?? 'Quintana Roo',
                    'origin_city' => $validated['origin_city'] ?? 'José María Morelos',
                    'destination_country' => $validated['destination_country'] ?? null,
                    'destination_state' => $validated['destination_state'] ?? null,
                    'destination_city' => $validated['destination_city'] ?? null,
                    'departure_date' => $validated['departure_date'] ?? null,
                    'return_date' => $validated['return_date'] ?? null,
                    'days_duration' => $validated['days_duration'] ?? null,
                    'half_day_payment' => $validated['half_day_payment'] ?? false,
                    'justification' => $validated['justification'] ?? null,
                    'report_date' => $validated['report_date'] ?? null,
                    'report_link' => $validated['report_link'] ?? null,
                    'has_viaticos' => $validated['has_viaticos'] ?? false,
                    'viaticos_partida_id' => $validated['viaticos_partida_id'] ?? null,
                    'has_pasaje' => $validated['has_pasaje'] ?? false,
                    'pasaje_partida_id' => $validated['pasaje_partida_id'] ?? null,
                    'has_hospedaje' => $validated['has_hospedaje'] ?? false,
                    'hospedaje_partida_id' => $validated['hospedaje_partida_id'] ?? null,
                    'transport_type' => $validated['transport_type'] ?? 'Oficial',
                    'vehicle_id' => $validated['vehicle_id'] ?? null,
                    'invoice_folio' => $validated['invoice_folio'] ?? null,
                    'invoice_date' => $validated['invoice_date'] ?? null,
                    'provider_rfc' => $validated['provider_rfc'] ?? null,
                    'provider_name' => $validated['provider_name'] ?? null,
                    'uuid' => $validated['uuid'] ?? null,
                    'total_viaticos' => $validated['viaticos_amount'] ?? 0,
                    'total_pasaje' => $validated['pasaje_amount'] ?? 0,
                    'total_hospedaje' => $validated['hospedaje_amount'] ?? 0,
                    'subtotal' => $validated['invoice_subtotal'] ?? 0,
                    'iva' => $validated['invoice_iva'] ?? 0,
                    'isr' => $validated['invoice_isr'] ?? 0,
                    'retention_iva' => $validated['invoice_retention_iva'] ?? 0,
                    'total' => $validated['invoice_total'] ?? 0,
                ]);


                // Sync Multiple Commissioners with Pivot Data
                if (!empty($validated['commissioners_details'])) {
                    $syncData = [];
                    foreach ($validated['commissioners_details'] as $comm) {
                        $syncData[$comm['id']] = [
                            'oficio_number' => $comm['oficio_number'] ?? null,
                            'report_date' => $comm['report_date'] ?? null,
                            'report_link' => $comm['report_link'] ?? null,
                        ];
                    }
                    $travelAllowance->commissioners()->sync($syncData);
                } elseif (!empty($validated['commissioner_ids'])) {
                    // Fallback for backward compatibility
                    $travelAllowance->commissioners()->sync($validated['commissioner_ids']);
                } elseif ($validated['commissioner_id']) {
                    // Fallback: if only single ID is sent
                    $travelAllowance->commissioners()->sync([$validated['commissioner_id']]);
                }
            }

            // Link Firefighter Captures if applicable
            if ($validated['type'] === 'bomberos') {
                $folio = $validated['firefighter_folio'] ?? $validated['requirement_number'];
                \App\Models\Capture::where('requirement_number', $folio)
                    ->where('year', $validated['year'])
                    ->whereNull('requirement_id')
                    ->update(['requirement_id' => $requirement->id]);
            }
        });

        return redirect()->route('requirements.index')->with('success', 'Requerimiento creado exitosamente.');
    }

    public function edit(Requirement $requirement)
    {
        $requirement->load(['items', 'cfeReceipts', 'travelAllowance.commissioners']); // Load receipts and travel allowance with commissioners

        // Find linked firefighter folio if any
        $linkedCapture = \App\Models\Capture::where('requirement_id', $requirement->id)->first();
        $requirement->firefighter_folio = $linkedCapture ? $linkedCapture->requirement_number : null;

        $employees = Empleado::activos()->select('id', 'nombre', 'primer_nombre', 'primer_apellido', 'segundo_apellido', 'puesto', 'cargo', 'rfc', 'clave', 'nivel', 'departamento', 'categoria', 'tipo_plaza', 'jefe_inmediato', 'banco', 'clabe', 'organismo_id')->get(); // Added fields for Viaticos auto-fill
        $capitulos = Capitulo::activos()->select('id', 'codigo', 'nombre')->get();
        $partidas = Partida::activos()->with('capitulo')->select('id', 'codigo', 'nombre', 'partida_generica', 'capitulo_id')->get();
        $vehicles = \App\Models\Vehicle::where('active', true)->select('id', 'brand', 'model_year', 'plate_number', 'organismo_id')->get();

        // Fetch year legend for edit (based on requirement year)
        $leyenda = \App\Models\Leyenda::where('anio', $requirement->year)->first();
        $defaultLegend = $leyenda ? $leyenda->texto : '';

        // Fetch active travel allowance rates for requirement year
        $travelAllowanceRates = \App\Models\TravelAllowanceRate::with('partida')
            ->active()
            ->forYear($requirement->year)
            ->get();

        return Inertia::render('Requirements/Edit', [
            'requirement' => $requirement,
            'employees' => $employees,
            'capitulos' => $capitulos,
            'partidas' => $partidas,
            'vehicles' => $vehicles,
            'types' => Requirement::TYPES,
            'defaultLegend' => $defaultLegend, // Pass legend
            'travelAllowanceRates' => $travelAllowanceRates,
            'monthsList' => [
                'Enero',
                'Febrero',
                'Marzo',
                'Abril',
                'Mayo',
                'Junio',
                'Julio',
                'Agosto',
                'Septiembre',
                'Octubre',
                'Noviembre',
                'Diciembre'
            ]
        ]);
    }

    public function update(Request $request, Requirement $requirement)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'requirement_number' => 'required|integer',
            'type' => 'required|string',
            'assignment_date' => 'nullable|date',
            'oficio_number' => 'nullable|string',
            'coordinator_id' => 'nullable|exists:empleados,id',
            'director_id' => 'nullable|exists:empleados,id',
            'manager_id' => 'nullable|exists:empleados,id',
            'elaborator_id' => 'nullable|exists:empleados,id',
            'month_charged' => 'nullable|string',
            'year_charged' => 'nullable|integer',
            'month_billed' => 'nullable|string',
            'year_billed' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.partida_id' => 'required|exists:partidas,id',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
            'items.*.employee_id' => 'nullable|exists:empleados,id',
            'items.*.uuid' => 'nullable|string',
            'items.*.invoice_folio' => 'nullable|string',
            'items.*.invoice_date' => 'nullable|date',
            'items.*.provider_rfc' => 'nullable|string',
            'items.*.provider_name' => 'nullable|string',
            'items.*.invoice_subtotal' => 'nullable|numeric|min:0',
            'items.*.invoice_iva' => 'nullable|numeric|min:0',
            'items.*.invoice_retention_isr' => 'nullable|numeric|min:0',
            'items.*.invoice_retention_iva' => 'nullable|numeric|min:0',
            'items.*.invoice_total' => 'nullable|numeric|min:0',
            'cfe_receipts' => 'nullable|array',
            'cfe_receipts.*.uuid' => 'nullable|string',
            'cfe_receipts.*.rpu' => 'nullable|string',
            'cfe_receipts.*.description' => 'nullable|string',
            'cfe_receipts.*.period_start' => 'nullable|date',
            'cfe_receipts.*.period_end' => 'nullable|date',
            'cfe_receipts.*.subtotal' => 'nullable|numeric',
            'cfe_receipts.*.iva' => 'nullable|numeric',
            'cfe_receipts.*.rounding' => 'nullable|numeric',
            'cfe_receipts.*.total' => 'nullable|numeric',

            // Viaticos Validation
            'commission_summary_legend' => 'nullable|string',
            'exercise_year' => 'nullable|integer',
            'quarter' => 'nullable|string|in:I,II,III,IV',
            'commissioner_id' => 'nullable|exists:empleados,id',
            'commissioner_ids' => 'nullable|array',
            'commissioner_ids.*' => 'exists:empleados,id',
            'origin_country' => 'nullable|string',
            'origin_state' => 'nullable|string',
            'origin_city' => 'nullable|string',
            'destination_country' => 'nullable|string',
            'destination_state' => 'nullable|string',
            'destination_city' => 'nullable|string',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'days_duration' => 'nullable|integer',
            'half_day_payment' => 'nullable|boolean',
            'justification' => 'nullable|string',
            'report_date' => 'nullable|date',
            'report_link' => 'nullable|string',
            'has_viaticos' => 'nullable|boolean',
            'viaticos_partida_id' => 'nullable|exists:partidas,id',
            'has_pasaje' => 'nullable|boolean',
            'pasaje_partida_id' => 'nullable|exists:partidas,id',
            'has_hospedaje' => 'nullable|boolean',
            'hospedaje_partida_id' => 'nullable|exists:partidas,id',
            'transport_type' => 'nullable|string|in:Oficial,Particular,Publico',
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'invoice_folio' => 'nullable|string',
            'invoice_date' => 'nullable|date',
            'provider_rfc' => 'nullable|string',
            'provider_name' => 'nullable|string',
            'uuid' => 'nullable|string',
            'viaticos_amount' => 'nullable|numeric|min:0',
            'pasaje_amount' => 'nullable|numeric|min:0',
            'hospedaje_amount' => 'nullable|numeric|min:0',
            'invoice_subtotal' => 'nullable|numeric',
            'invoice_iva' => 'nullable|numeric',
            'invoice_isr' => 'nullable|numeric',
            'invoice_retention_iva' => 'nullable|numeric',
            'invoice_total' => 'nullable|numeric',
            'commissioners_details' => 'nullable|array',
            'commissioners_details.*.id' => 'required|exists:empleados,id',
            'commissioners_details.*.oficio_number' => 'nullable|string',
            'commissioners_details.*.report_date' => 'nullable|date',
            'commissioners_details.*.report_link' => 'nullable|string',
            'firefighter_folio' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $requirement) {
            // Calculation Logic
            if ($validated['type'] === 'viaticos' || $validated['type'] === 'bomberos') {
                $subtotal = collect($validated['items'])->sum('amount');
                $iva = 0;
                $total = $subtotal;
            } elseif (!empty($validated['cfe_receipts'])) {
                $subtotal = collect($validated['cfe_receipts'])->sum('subtotal');
                $iva = collect($validated['cfe_receipts'])->sum('iva');
                $total = collect($validated['cfe_receipts'])->sum('total');
            } else {
                $subtotal = collect($validated['items'])->sum('amount');
                $iva = $subtotal * 0.16;
                $total = $subtotal + $iva;
            }

            $requirement->update([
                ...$validated,
                'subtotal' => $subtotal,
                'iva' => $iva,
                'total' => $total,
            ]);

            // Create or update Travel Allowance specific data
            if ($validated['type'] === 'viaticos') {
                $travelAllowance = $requirement->travelAllowance()->updateOrCreate(
                    ['requirement_id' => $requirement->id],
                    [
                        'oficio_number' => $validated['oficio_number'] ?? null,
                        'commission_summary_legend' => $validated['commission_summary_legend'] ?? null,
                        'exercise_year' => $validated['exercise_year'] ?? null,
                        'quarter' => $validated['quarter'] ?? null,
                        'commissioner_id' => $validated['commissioner_id'] ?? null,
                        'origin_country' => $validated['origin_country'] ?? 'México',
                        'origin_state' => $validated['origin_state'] ?? 'Quintana Roo',
                        'origin_city' => $validated['origin_city'] ?? 'José María Morelos',
                        'destination_country' => $validated['destination_country'] ?? null,
                        'destination_state' => $validated['destination_state'] ?? null,
                        'destination_city' => $validated['destination_city'] ?? null,
                        'departure_date' => $validated['departure_date'] ?? null,
                        'return_date' => $validated['return_date'] ?? null,
                        'days_duration' => $validated['days_duration'] ?? null,
                        'half_day_payment' => $validated['half_day_payment'] ?? false,
                        'justification' => $validated['justification'] ?? null,
                        'report_date' => $validated['report_date'] ?? null,
                        'report_link' => $validated['report_link'] ?? null,
                        'has_viaticos' => $validated['has_viaticos'] ?? false,
                        'viaticos_partida_id' => $validated['viaticos_partida_id'] ?? null,
                        'has_pasaje' => $validated['has_pasaje'] ?? false,
                        'pasaje_partida_id' => $validated['pasaje_partida_id'] ?? null,
                        'has_hospedaje' => $validated['has_hospedaje'] ?? false,
                        'hospedaje_partida_id' => $validated['hospedaje_partida_id'] ?? null,
                        'transport_type' => $validated['transport_type'] ?? 'Oficial',
                        'vehicle_id' => $validated['vehicle_id'] ?? null,
                        'invoice_folio' => $validated['invoice_folio'] ?? null,
                        'invoice_date' => $validated['invoice_date'] ?? null,
                        'provider_rfc' => $validated['provider_rfc'] ?? null,
                        'provider_name' => $validated['provider_name'] ?? null,
                        'uuid' => $validated['uuid'] ?? null,
                        'total_viaticos' => $validated['viaticos_amount'] ?? 0,
                        'total_pasaje' => $validated['pasaje_amount'] ?? 0,
                        'total_hospedaje' => $validated['hospedaje_amount'] ?? 0,
                        'subtotal' => $validated['invoice_subtotal'] ?? 0,
                        'iva' => $validated['invoice_iva'] ?? 0,
                        'isr' => $validated['invoice_isr'] ?? 0,
                        'retention_iva' => $validated['invoice_retention_iva'] ?? 0,
                        'total' => $validated['invoice_total'] ?? 0,
                    ]
                );

                if (!empty($validated['commissioners_details'])) {
                    $syncData = [];
                    foreach ($validated['commissioners_details'] as $comm) {
                        $syncData[$comm['id']] = [
                            'oficio_number' => $comm['oficio_number'] ?? null,
                            'report_date' => $comm['report_date'] ?? null,
                            'report_link' => $comm['report_link'] ?? null,
                        ];
                    }
                    $travelAllowance->commissioners()->sync($syncData);
                } elseif (!empty($validated['commissioner_ids'])) {
                    $travelAllowance->commissioners()->sync($validated['commissioner_ids']);
                } elseif (!empty($validated['commissioner_id'])) {
                    $travelAllowance->commissioners()->sync([$validated['commissioner_id']]);
                }
            }

            // Sync Items
            $requirement->items()->delete();
            foreach ($validated['items'] as $item) {
                $requirement->items()->create([
                    'partida_id' => $item['partida_id'],
                    'description' => $item['description'] ?? null,
                    'amount' => $item['amount'],
                    'employee_id' => $item['employee_id'] ?? null,
                    'uuid' => $item['uuid'] ?? null,
                    'invoice_folio' => $item['invoice_folio'] ?? null,
                    'invoice_date' => $item['invoice_date'] ?? null,
                    'provider_rfc' => $item['provider_rfc'] ?? null,
                    'provider_name' => $item['provider_name'] ?? null,
                    'invoice_subtotal' => $item['invoice_subtotal'] ?? null,
                    'invoice_iva' => $item['invoice_iva'] ?? null,
                    'invoice_retention_isr' => $item['invoice_retention_isr'] ?? null,
                    'invoice_retention_iva' => $item['invoice_retention_iva'] ?? null,
                    'invoice_total' => $item['invoice_total'] ?? null,
                ]);
            }

            // Sync CFE Receipts
            $requirement->cfeReceipts()->delete();
            if (!empty($validated['cfe_receipts'])) {
                foreach ($validated['cfe_receipts'] as $receipt) {
                    $requirement->cfeReceipts()->create($receipt);
                }
            }

            // Link Firefighter Captures if applicable
            if ($validated['type'] === 'bomberos') {
                // Clear previous links first if any
                \App\Models\Capture::where('requirement_id', $requirement->id)->update(['requirement_id' => null]);

                $folio = $validated['firefighter_folio'] ?? $validated['requirement_number'];
                \App\Models\Capture::where('requirement_number', $folio)
                    ->where('year', $validated['year'])
                    ->where(function ($q) use ($requirement) {
                        $q->whereNull('requirement_id')->orWhere('requirement_id', $requirement->id);
                    })
                    ->update(['requirement_id' => $requirement->id]);
            }
        });

        return redirect()->route('requirements.index')->with('success', 'Requerimiento actualizado exitosamente.');
    }

    public function destroy(Requirement $requirement)
    {
        $requirement->delete();
        return redirect()->route('requirements.index')->with('success', 'Requerimiento eliminado exitosamente.');
    }

    public function downloadBomberosOficio(Requirement $requirement)
    {
        if ($requirement->type !== 'bomberos') {
            abort(404);
        }

        $requirement->load(['items.partida.capitulo', 'coordinator', 'director', 'manager', 'elaborator']);

        // Fetch settings
        $settings = $this->getSettingsForPdf();

        // Fetch specific Coordinador Comercial for the addressee
        $bomberosCoordinador = \App\Models\Empleado::where('puesto', 'LIKE', '%COORDINADOR COMERCIAL%')
            ->where('activo', true)
            ->orderByDesc('id')
            ->first();
        $destinatario = $bomberosCoordinador ?? $requirement->coordinator;

        $fecha = \Carbon\Carbon::parse($requirement->assignment_date);
        $fecha_formateada = $fecha->day . ' DE ' . strtoupper($fecha->translatedFormat('F')) . ' DEL ' . $fecha->year;

        $importe_letras = \App\Helpers\NumberHelper::convert($requirement->total);

        $pdf = Pdf::loadView('reports.bomberos_oficio', [
            'requirement' => $requirement,
            'settings' => $settings,
            'fecha_formateada' => $fecha_formateada,
            'importe_letras' => $importe_letras,
            'destinatario' => $destinatario
        ])->setPaper('letter', 'portrait');

        return $pdf->download('Oficio_Bomberos_' . $requirement->requirement_number . '.pdf');
    }

    private function getSettingsForPdf()
    {
        $rawSettings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $settings = [];

        foreach ($rawSettings as $key => $value) {
            if (in_array($key, ['logo_qroo', 'logo_unidos', 'logo_capa_header', 'logo_capa', 'footer_imagen']) && $value) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($value)) {
                    $path = \Illuminate\Support\Facades\Storage::disk('public')->path($value);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $settings[$key] = $base64;
                } else {
                    $settings[$key] = $value;
                }
            } else {
                $settings[$key] = $value;
            }
        }

        // Year Legend
        $leyenda = \App\Models\Leyenda::orderBy('anio', 'desc')->first();
        $settings['leyenda_anio'] = $leyenda ? $leyenda->texto : '';

        return $settings;
    }

    public function downloadPdf(Requirement $requirement)
    {
        $requirement->load(['items.partida.capitulo', 'items.employee', 'coordinator', 'director', 'manager', 'elaborator', 'travelAllowance']);

        // Group items by partida_id to consolidate amounts for the report (except for specific cases)
        if ($requirement->type !== 'bomberos') {
            $groupedItems = $requirement->items->groupBy('partida_id')->map(function ($group) {
                $firstItem = $group->first();
                $firstItem->amount = $group->sum('amount');
                return $firstItem;
            })->values();
            $requirement->setRelation('items', $groupedItems);
        }

        // Fetch settings using helper
        $settings = $this->getSettingsForPdf();

        // Prepare data for the view
        $fecha = \Carbon\Carbon::parse($requirement->assignment_date);
        $fecha_formateada = $fecha->day . ' de ' . $fecha->translatedFormat('F') . ' ' . $fecha->year;

        $importe_letras = \App\Helpers\NumberHelper::convert($requirement->total);

        if ($requirement->type === 'bomberos') {
            // Find Subgerente for "ENTERÉ" signature in Anexo 15
            $subgerente = Empleado::where('puesto', 'LIKE', '%SUBGERENTE COMERCIAL%')
                ->where('activo', true)
                ->orderByDesc('id')
                ->first();

            return Pdf::loadView('reports.bomberos_anexo_15', [
                'requirement' => $requirement,
                'settings' => $settings,
                'fecha_formateada' => $fecha_formateada,
                'importe_letras' => $importe_letras,
                'subgerente' => $subgerente
            ])->setPaper('letter', 'portrait')->download('Anexo_15_Bomberos_' . $requirement->requirement_number . '.pdf');
        }

        // Standard Logic for other types
        $adminCoordinator = \App\Models\Empleado::where('puesto', 'LIKE', '%COORDINADOR ADMINISTRATIVO, FINANCIERO Y DE ARCHIVO%')->first();
        $finalCoordinator = $adminCoordinator ?? $requirement->coordinator;

        $data = [
            'destinatario_nombre' => $finalCoordinator->nombre ?? 'N/A',
            'destinatario_cargo' => $finalCoordinator->puesto ?? 'COORDINADOR ADMINISTRATIVO, FINANCIERO Y DE ARCHIVO',
            'solicitante_departamento' => 'ORGANISMO OPERADOR JOSÉ MARÍA MORELOS',
        ];

        $pdf = Pdf::loadView('reports.requirement', [
            'requirement' => $requirement,
            'settings' => $settings,
            'fecha_formateada' => $fecha_formateada,
            'importe_letras' => $importe_letras,
            'data' => $data
        ])->setPaper('letter', 'portrait');

        $filename = 'Requerimiento_' . str_replace('/', '-', $requirement->formatted_number) . '.pdf';
        return $pdf->download($filename);
    }

    public function downloadAnexo2(Requirement $requirement, Empleado $employee)
    {
        $requirement->load([
            'travelAllowance.commissioners',
            'items' => function ($query) use ($employee) {
                $query->where('employee_id', $employee->id);
            }
        ]);

        // Get the specific budget code for this employee's level from the catalog
        $rate = \App\Models\TravelAllowanceRate::where('year', $requirement->year)
            ->where('nivel', $employee->nivel)
            ->where('rate_type', 'viaticos')
            ->first();

        $budgetCode = $rate ? $rate->budget_code : 'N/A';

        // Solve specific commissioner details from pivot
        $pivot = $requirement->travelAllowance->commissioners->where('id', $employee->id)->first();
        $baseOficioNumber = $pivot && $pivot->pivot ? $pivot->pivot->oficio_number : 'N/A';
        $year = \Carbon\Carbon::parse($requirement->assignment_date)->year;
        $oficioNumber = "CAPA/JMM/G/{$baseOficioNumber}/{$year}";
        $reportDate = $pivot && $pivot->pivot ? $pivot->pivot->report_date : null;
        $reportLink = $pivot && $pivot->pivot ? $pivot->pivot->report_link : null;

        // Signatories logic
        // 1. Persona Comisionada: $employee
        // 2. Titular Superior & 3. Titular Autorizador depend on whether employee is a Gerente
        $superior = null;
        $autorizador = null;

        if ($employee->es_gerente) {
            // When the comisionado IS a Gerente:
            //   - Titular Superior    → Director General
            //   - Titular Autorizador → Subgerente Administrativo
            $superior = \App\Models\Empleado::where('puesto', 'LIKE', '%DIRECTOR GENERAL%')
                ->where('activo', true)->first();
            $autorizador = \App\Models\Empleado::where('puesto', 'LIKE', '%SUBGERENTE ADMINISTRATIVO%')
                ->where('activo', true)->first();
        } else {
            // Regular employee:
            //   - Titular Superior  → jefe_inmediato (by name match)
            //   - Titular Autorizador → active Gerente
            $superior = \App\Models\Empleado::where('nombre', $employee->jefe_inmediato)
                ->orWhere(DB::raw("CONCAT(nombre, ' ', primer_apellido, ' ', segundo_apellido)"), $employee->jefe_inmediato)
                ->first();

            $autorizador = \App\Models\Empleado::where('es_gerente', true)->where('activo', true)->first();
        }

        // Pernoctas logic: days_duration - 1 if > 1
        $pernoctas = 0;
        if ($requirement->travelAllowance && $requirement->travelAllowance->days_duration > 1) {
            $pernoctas = $requirement->travelAllowance->days_duration - 1;
        }

        // Fetch settings (Logos, etc.)
        $rawSettings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $settings = [];
        foreach ($rawSettings as $key => $value) {
            if (in_array($key, ['logo_qroo', 'logo_unidos', 'logo_capa_header', 'logo_capa', 'footer_imagen']) && $value) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($value)) {
                    $path = \Illuminate\Support\Facades\Storage::disk('public')->path($value);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $settings[$key] = $base64;
                } else {
                    $settings[$key] = $value;
                }
            } else {
                $settings[$key] = $value;
            }
        }

        $limitPerDay = $rate ? (float) $rate->zona_1_amount : 0;
        $formattedDays = ($requirement->travelAllowance && $requirement->travelAllowance->half_day_payment) ? '0.5' : ($requirement->travelAllowance->days_duration ?? 1);

        $pdf = Pdf::loadView('reports.anexo_2', [
            'requirement' => $requirement,
            'employee' => $employee,
            'oficioNumber' => $oficioNumber,
            'budgetCode' => $budgetCode,
            'limitPerDay' => $limitPerDay,
            'formattedDays' => $formattedDays,
            'superior' => $superior,
            'autorizador' => $autorizador,
            'pernoctas' => $pernoctas,
            'reportDate' => $reportDate,
            'reportLink' => $reportLink,
            'settings' => $settings,
        ]);

        $filename = 'Anexo_2_' . $employee->primer_apellido . '_' . $requirement->requirement_number . '.pdf';
        return $pdf->setPaper('letter', 'portrait')->download($filename);
    }

    public function downloadCfeRelation(Requirement $requirement)
    {
        $requirement->load(['cfeReceipts', 'elaborator', 'manager']);

        // Fetch settings (same as downloadPdf)
        $rawSettings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $settings = [];

        // Process images for DomPDF (Must be Base64 or Absolute Path)
        foreach ($rawSettings as $key => $value) {
            if (in_array($key, ['logo_qroo', 'logo_unidos', 'logo_capa_header', 'logo_capa', 'footer_imagen']) && $value) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($value)) {
                    $path = \Illuminate\Support\Facades\Storage::disk('public')->path($value);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $settings[$key] = $base64;
                } else {
                    $settings[$key] = $value;
                }
            } else {
                $settings[$key] = $value;
            }
        }

        // Pagination logic: 26 receipts per page
        $receiptsPerPage = 26;
        $allReceipts = collect($requirement->cfeReceipts);
        $totalReceipts = $allReceipts->count();
        $totalPages = max(1, ceil($totalReceipts / $receiptsPerPage));

        // Calculate grand totals
        $grandSubtotal = $allReceipts->sum('subtotal');
        $grandIva = $allReceipts->sum('iva');
        $grandTotal = $allReceipts->sum('total');

        // Chunk receipts into pages
        $pages = [];
        $receiptChunks = $allReceipts->chunk($receiptsPerPage);
        $pageNumber = 1;

        foreach ($receiptChunks as $chunk) {
            $pageSubtotal = $chunk->sum('subtotal');
            $pageIva = $chunk->sum('iva');
            $pageTotal = $chunk->sum('total');

            $pages[] = [
                'receipts' => $chunk,
                'pageNumber' => $pageNumber,
                'totalPages' => $totalPages,
                'isLastPage' => ($pageNumber === $totalPages),
                'pageSubtotal' => $pageSubtotal,
                'pageIva' => $pageIva,
                'pageTotal' => $pageTotal,
            ];

            $pageNumber++;
        }

        $pdf = Pdf::loadView('reports.cfe_relation', [
            'requirement' => $requirement,
            'settings' => $settings,
            'pages' => $pages,
            'grandSubtotal' => $grandSubtotal,
            'grandIva' => $grandIva,
            'grandTotal' => $grandTotal,
        ]);

        return $pdf->setPaper('letter', 'portrait')->download('Relacion_CFE_' . str_replace('/', '-', $requirement->formatted_number) . '.pdf');
    }
    public function parseXml(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xml',
        ]);

        try {
            $xmlString = file_get_contents($request->file('file')->getRealPath());
            $xml = simplexml_load_string($xmlString);
            $ns = $xml->getNamespaces(true);
            $xml->registerXPathNamespace('cfdi', $ns['cfdi']);
            $xml->registerXPathNamespace('tfd', $ns['tfd'] ?? 'http://www.sat.gob.mx/TimbreFiscalDigital');

            // Comprobante Data
            $attributes = $xml->attributes();
            $total = (float) $attributes['Total'];
            $subtotal = isset($attributes['SubTotal']) ? (float) $attributes['SubTotal'] : (float) ($attributes['Subtotal'] ?? 0);
            $fecha = (string) $attributes['Fecha'];
            $folio = isset($attributes['Folio']) ? (string) $attributes['Folio'] : '';
            $serie = isset($attributes['Serie']) ? (string) $attributes['Serie'] : '';

            // Emisor Data
            $emisor = $xml->xpath('//cfdi:Emisor');
            if (empty($emisor)) {
                throw new \Exception('No se encontró el nodo Emisor');
            }
            $emisorAttributes = $emisor[0]->attributes();
            $rfcEmisor = (string) $emisorAttributes['Rfc'];
            $nombreEmisor = (string) $emisorAttributes['Nombre'];

            // Timbre Fiscal Data (UUID)
            $tfd = $xml->xpath('//tfd:TimbreFiscalDigital');
            // Manual namespace registration fallback if needed (rare but possible)
            if (empty($tfd)) {
                $xml->registerXPathNamespace('tfd', 'http://www.sat.gob.mx/TimbreFiscalDigital');
                $tfd = $xml->xpath('//tfd:TimbreFiscalDigital');
            }
            $uuid = !empty($tfd) ? (string) $tfd[0]['UUID'] : '';

            // Conceptos (Get first description)
            $conceptos = $xml->xpath('//cfdi:Conceptos/cfdi:Concepto');
            $descripcion = '';
            if (!empty($conceptos)) {
                $descripcion = (string) $conceptos[0]['Descripcion'];
            }

            // Impuestos (IVA, Retenciones)
            // Use direct child selector to avoid summing concept-level taxes + global taxes (Double counting)
            $traslados = $xml->xpath('cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');
            $iva = 0;
            foreach ($traslados as $traslado) {
                if ((string) $traslado['Impuesto'] === '002') { // IVA
                    $iva += (float) $traslado['Importe'];
                }
            }

            $retenciones = $xml->xpath('cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion');
            $retencionIsr = 0;
            $retencionIva = 0;
            foreach ($retenciones as $retencion) {
                if ((string) $retencion['Impuesto'] === '001') { // ISR
                    $retencionIsr += (float) $retencion['Importe'];
                }
                if ((string) $retencion['Impuesto'] === '002') { // IVA
                    $retencionIva += (float) $retencion['Importe'];
                }
            }

            // Fallback for Folio if empty: First 2 groups of UUID
            $invoiceNumber = $folio ? ($serie ? "$serie-$folio" : $folio) : '';
            if (empty($invoiceNumber) && $uuid) {
                $uuidParts = explode('-', $uuid);
                if (count($uuidParts) >= 2) {
                    $invoiceNumber = $uuidParts[0] . '-' . $uuidParts[1];
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'invoice_folio' => $invoiceNumber,
                    'invoice_date' => substr($fecha, 0, 10), // YYYY-MM-DD
                    'provider_rfc' => $rfcEmisor,
                    'provider_name' => $nombreEmisor, // Optional, might need to store in different field if exists
                    'description' => $descripcion,
                    'subtotal' => $subtotal,
                    'iva' => $iva,
                    'retention_isr' => $retencionIsr,
                    'retention_iva' => $retencionIva,
                    'total' => $total,
                    'uuid' => $uuid,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el XML: ' . $e->getMessage()
            ], 400);
        }
    }

    public function downloadComprobacionViaticos(Requirement $requirement, Empleado $employee)
    {
        $requirement->load(['items.partida', 'travelAllowance.commissioners', 'coordinator', 'director', 'manager', 'elaborator']);

        // Filter items for THIS employee only
        $employeeItems = $requirement->items->where('employee_id', $employee->id);

        // Group items by category keys (based on partida code or description logic)
        $transporteItems = $employeeItems->filter(function ($item) {
            $code = $item->partida->codigo ?? '';
            $name = $item->partida->nombre ?? '';
            return str_contains($name, 'Pasajes') || str_contains($code, '371') || str_contains($code, '372');
        });

        $hospedajeItems = $employeeItems->filter(function ($item) {
            $code = $item->partida->codigo ?? '';
            $name = $item->partida->nombre ?? '';
            // Stricter check: Only "Hospedaje" in name, or specific 37504 if we knew it. 
            // Removing generic 375 check to avoid catching "Viaticos en el pais" (37501)
            return str_contains($name, 'Hospedaje');
        });

        // Viaticos = Everything else (Food, etc)
        $viaticosItems = $employeeItems->diff($transporteItems)->diff($hospedajeItems);

        // Pre-calculate totals for summary
        $totalTransporteComprobado = $transporteItems->sum('invoice_total');
        $totalHospedajeComprobado = $hospedajeItems->sum('invoice_total');
        $totalViaticosComprobado = $viaticosItems->sum('invoice_total');

        // Cuotas (Assigned Amounts) - Need to clarify if these are per-employee or total.
        // For now, assuming proportional division or total if not specified per employee in structure.
        // Ideally, TravelAllowance should have per-employee allocation.
        // Given earlier conversation, we have 'limitPerDay' * 'days'.


        $zone = $requirement->travelAllowance->zona ?? 'A'; // Default to A

        // We need to find the rate based on the employee's level/position if possible, 
        // or just use a general rate if that's how it's set up. 
        // The previous code was trying to match 'zona' column which doesn't exist.
        // Rates table has 'zona_1_amount' (Zone A) and 'zona_2_amount' (Zone B).

        // Let's try to match by level if available, otherwise just get the first applicable rate.
        $rateQuery = \App\Models\TravelAllowanceRate::where('year', $requirement->year);

        if ($employee->nivel) {
            $rateQuery->where('nivel', $employee->nivel);
        }

        $rate = $rateQuery->first();

        // Fallback if specific level rate not found, try generic or just use default
        if (!$rate) {
            $rate = \App\Models\TravelAllowanceRate::where('year', $requirement->year)->first();
        }

        $limitPerDay = 938.00; // Default fallback
        if ($rate) {
            $limitPerDay = ($zone === 'A') ? (float) $rate->zona_1_amount : (float) $rate->zona_2_amount;
        }

        // Check if this specific employee has logic for half-day? 
        // Logic from Anexo 2: ($requirement->travelAllowance && $requirement->travelAllowance->half_day_payment) ? '0.5' : ...
        $days = ($requirement->travelAllowance && $requirement->travelAllowance->half_day_payment) ? 0.5 : ($requirement->travelAllowance->days_duration ?? 1);

        // Calculate expected quotas per employee based on days * limit
        // WARNING: This is an estimation. If logic involves specific amounts per field, we might need adjustment.
        // For now, mirroring Anexo 2 logic:
        $cuotaViaticos = $limitPerDay * $days;

        // Pasaje and Hospedaje are usually 'Asignado' on the items.
        // If we don't have per-employee quota fields, we might compare against validated items or leave as 0/Total.
        // Re-reading user request: "la comprobacion es por trabajador".
        // Let's use the sums of *Assigned* items for this employee as the quota if available, 
        // OR simply use the 'Total' from the requirement divided by commissioners?
        // BETTER APPROACH: Use the same logic as Anexo 2 Item Table "Importe asignado" column sum?

        // Actually, for "Cuota de Viáticos", it's usually (Daily Rate * Days).
        // "Cuota pasaje" and "Cuota Hospedaje" are often specific budget items.
        // Let's try to sum the 'amount' (assigned) of the items for this employee to get their specific budget?
        // But 'amount' on items is usually the budget.

        $cuoteTransporteAssigned = $transporteItems->sum('amount');
        $cuotaHospedajeAssigned = $hospedajeItems->sum('amount');
        // Viaticos items might be generic.

        // Let's stick to the $cuotaViaticos = rate * days for Viaticos.
        // And assigned amounts for others.

        $cuotaPasaje = $cuoteTransporteAssigned;
        $cuotaHospedaje = $cuotaHospedajeAssigned;

        // Get Oficio Number/Date from Pivot
        $commissioner = $requirement->travelAllowance->commissioners->firstWhere('id', $employee->id);
        $oficioNumber = $commissioner ? ($commissioner->pivot->oficio_number ?? $requirement->formatted_number) : $requirement->formatted_number;
        // If we want the full formatted string like in Anexo 2: "CAPA/JMM/G/NUMERO/AÑO"
        // But the user just said "numero de viatico es el numero de oficio". Let's assume just the number or the full string if available.
        // In Anexo 2 we constructed it. Let's construct it here too if needed, or just pass the raw number if that's what's stored.
        // Checking Anexo 2 logic: just uses $oficio_number from pivot? No, let's check Anexo 2 blade.
        // Re-reading previous Anexo 2 work: we added logic to Format "Oficio de Comisión" as `CAPA/JMM/G/NUMERO/AÑO`.
        // Let's replicate that format if $oficioNumber is just a number.

        $year = $requirement->year;
        // Assuming oficio_number is just the integer part. 
        // If it's already formatted, use it. If numeric, format it.
        if (is_numeric($oficioNumber)) {
            $oficioNumber = "CAPA/JMM/G/" . str_pad($oficioNumber, 3, '0', STR_PAD_LEFT) . "/$year";
        }

        // Sanitize filename
        $filename = 'Comprobacion_Viaticos_' . str_replace(['/', '\\'], '-', $requirement->formatted_number) . '_' . str_replace(' ', '_', $employee->primer_apellido) . '.pdf';

        $pdf = Pdf::loadView('reports.comprobacion_viaticos', [
            'requirement' => $requirement,
            'employee' => $employee, // Pass employee to view
            'travelAllowance' => $requirement->travelAllowance,
            'transporteItems' => $transporteItems,
            'hospedajeItems' => $hospedajeItems,
            'viaticosItems' => $viaticosItems,
            'totalTransporteComprobado' => $totalTransporteComprobado,
            'totalHospedajeComprobado' => $totalHospedajeComprobado,
            'totalViaticosComprobado' => $totalViaticosComprobado,
            'cuotaViaticos' => $cuotaViaticos,
            'cuotaPasaje' => $cuotaPasaje,
            'cuotaHospedaje' => $cuotaHospedaje,
            'oficioNumber' => $oficioNumber,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream($filename);
    }
}
