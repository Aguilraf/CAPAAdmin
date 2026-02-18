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
            ->with(['coordinator', 'director', 'manager', 'elaborator'])
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

    public function create()
    {
        $year = date('Y');
        $latest = Requirement::where('year', $year)->max('requirement_number');
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

        // Fetch active travel allowance rates for current year
        $travelAllowanceRates = \App\Models\TravelAllowanceRate::with('partida')
            ->active()
            ->forYear($year)
            ->get();

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
            'defaultLegend' => $defaultLegend, // Pass legend
            'vehicles' => $vehicles,
            'travelAllowanceRates' => $travelAllowanceRates,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'requirement_number' => 'required|integer',
            'type' => 'required|string',
            'assignment_date' => 'nullable|date',
            'coordinator_id' => 'nullable|exists:empleados,id',
            'director_id' => 'nullable|exists:empleados,id',
            'manager_id' => 'nullable|exists:empleados,id',
            'elaborator_id' => 'nullable|exists:empleados,id',
            'month_charged' => 'nullable|string',
            'month_billed' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.partida_id' => 'required|exists:partidas,id',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
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
            'oficio_number' => 'nullable|string',
            'commission_summary_legend' => 'nullable|string',
            'exercise_year' => 'nullable|integer',
            'quarter' => 'nullable|string|in:I,II,III,IV',
            'commissioner_id' => 'nullable|exists:empleados,id',
            'origin_country' => 'nullable|string',
            'origin_state' => 'nullable|string',
            'origin_city' => 'nullable|string',
            'destination_country' => 'nullable|string',
            'destination_state' => 'nullable|string',
            'destination_city' => 'nullable|string',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'days_duration' => 'nullable|integer',
            'justification' => 'nullable|string',
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
            'viaticos_amount' => 'nullable|numeric|min:0',
            'pasaje_amount' => 'nullable|numeric|min:0',
            'hospedaje_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            // Calculation Logic
            if ($validated['type'] === 'viaticos') {
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
                $requirement->travelAllowance()->create([
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
                    'justification' => $validated['justification'] ?? null,
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
                    'total_viaticos' => $validated['viaticos_amount'] ?? 0,
                    'total_pasaje' => $validated['pasaje_amount'] ?? 0,
                    'total_hospedaje' => $validated['hospedaje_amount'] ?? 0,
                    'subtotal' => $subtotal,
                    'iva' => $iva,
                    'total' => $total,
                    // For now, Requirement total is calculated from Items (expenses breakdown), so we might redundancy check here or leave 0 if unused on this table directly vs Items.
                    // Actually, the user asked for total breakdown in requirement items too? 
                    // "necesito que se pregunte si se le pagaran viaticos... y cada uno tiene su partida presupuestal"
                    // Usually these are stored as RequirementItems. 
                    // Let's assume the amounts are passed as Items for budget impact, and we just store metadata here.
                ]);
            }
        });

        return redirect()->route('requirements.index')->with('success', 'Requerimiento creado exitosamente.');
    }

    public function edit(Requirement $requirement)
    {
        $requirement->load(['items', 'cfeReceipts', 'travelAllowance']); // Load receipts and travel allowance

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
        ]);
    }

    public function update(Request $request, Requirement $requirement)
    {
        $validated = $request->validate([
            'year' => 'required|integer',
            'requirement_number' => 'required|integer',
            'type' => 'required|string',
            'assignment_date' => 'nullable|date',
            'coordinator_id' => 'nullable|exists:empleados,id',
            'director_id' => 'nullable|exists:empleados,id',
            'manager_id' => 'nullable|exists:empleados,id',
            'elaborator_id' => 'nullable|exists:empleados,id',
            'month_charged' => 'nullable|string',
            'month_billed' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'description' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.partida_id' => 'required|exists:partidas,id',
            'items.*.amount' => 'required|numeric|min:0',
            'items.*.description' => 'nullable|string',
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
            'oficio_number' => 'nullable|string',
            'commission_summary_legend' => 'nullable|string',
            'exercise_year' => 'nullable|integer',
            'quarter' => 'nullable|string|in:I,II,III,IV',
            'commissioner_id' => 'nullable|exists:empleados,id',
            'origin_country' => 'nullable|string',
            'origin_state' => 'nullable|string',
            'origin_city' => 'nullable|string',
            'destination_country' => 'nullable|string',
            'destination_state' => 'nullable|string',
            'destination_city' => 'nullable|string',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date',
            'days_duration' => 'nullable|integer',
            'justification' => 'nullable|string',
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
            'viaticos_amount' => 'nullable|numeric|min:0',
            'pasaje_amount' => 'nullable|numeric|min:0',
            'hospedaje_amount' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $requirement) {
            if ($validated['type'] === 'viaticos') {
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

            // Sync Viaticos
            if ($validated['type'] === 'viaticos') {
                $requirement->travelAllowance()->updateOrCreate(
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
                        'justification' => $validated['justification'] ?? null,
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
                        'total_viaticos' => $validated['viaticos_amount'] ?? 0,
                        'total_pasaje' => $validated['pasaje_amount'] ?? 0,
                        'total_hospedaje' => $validated['hospedaje_amount'] ?? 0,
                        'subtotal' => $subtotal,
                        'iva' => $iva,
                        'total' => $total,
                    ]
                );
            }

            // Sync Items
            $requirement->items()->delete();
            foreach ($validated['items'] as $item) {
                $requirement->items()->create([
                    'partida_id' => $item['partida_id'],
                    'description' => $item['description'] ?? null,
                    'amount' => $item['amount'],
                ]);
            }

            // Sync CFE Receipts
            $requirement->cfeReceipts()->delete();
            if (!empty($validated['cfe_receipts'])) {
                foreach ($validated['cfe_receipts'] as $receipt) {
                    $requirement->cfeReceipts()->create($receipt);
                }
            }
        });

        return redirect()->route('requirements.index')->with('success', 'Requerimiento actualizado exitosamente.');
    }

    public function destroy(Requirement $requirement)
    {
        $requirement->delete();
        return redirect()->back()->with('success', 'Requerimiento eliminado.');
    }

    public function downloadPdf(Requirement $requirement)
    {
        $requirement->load(['items.partida.capitulo', 'coordinator', 'director', 'manager', 'elaborator']);

        // Fetch settings
        $rawSettings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $settings = [];

        // Process images for DomPDF (Must be Base64 or Absolute Path)
        foreach ($rawSettings as $key => $value) {
            if (in_array($key, ['logo_qroo', 'logo_unidos', 'footer_imagen']) && $value) {
                // Assuming values are stored as 'logos/filename.png' in public disk
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($value)) {
                    $path = \Illuminate\Support\Facades\Storage::disk('public')->path($value);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $settings[$key] = $base64;
                } else {
                    // Fallback or leave as is if not found (might be external URL though unlikely)
                    $settings[$key] = $value;
                }
            } else {
                $settings[$key] = $value;
            }
        }

        // Fetch year legend from leyendas catalog based on requirement year
        $requirementYear = \Carbon\Carbon::parse($requirement->assignment_date)->year;
        $leyenda = \App\Models\Leyenda::where('anio', $requirementYear)->first();
        $settings['leyenda_anio'] = $leyenda ? $leyenda->texto : '';

        // Prepare data for the view
        $fecha = \Carbon\Carbon::parse($requirement->assignment_date);
        $fecha_formateada = $fecha->day . ' de ' . $fecha->translatedFormat('F') . ' ' . $fecha->year;

        $importe_letras = \App\Helpers\NumberHelper::convert($requirement->total);

        // Logic for Signatures/Addresses based on specific Job Title (User Request)
        // Always look for the employee with this specific title via catalog
        $adminCoordinator = \App\Models\Empleado::where('puesto', 'LIKE', '%COORDINADOR ADMINISTRATIVO, FINANCIERO Y DE ARCHIVO%')->first();

        // Fallback to the one selected in the requirement if no match found by title
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

    public function downloadCfeRelation(Requirement $requirement)
    {
        $requirement->load(['cfeReceipts', 'elaborator', 'manager']);

        // Fetch settings (same as downloadPdf)
        $rawSettings = \App\Models\Setting::pluck('value', 'key')->toArray();
        $settings = [];

        // Process images for DomPDF (Must be Base64 or Absolute Path)
        foreach ($rawSettings as $key => $value) {
            if (in_array($key, ['logo_qroo', 'logo_unidos', 'footer_imagen']) && $value) {
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
            $traslados = $xml->xpath('//cfdi:Impuestos/cfdi:Traslados/cfdi:Traslado');
            $iva = 0;
            foreach ($traslados as $traslado) {
                if ((string) $traslado['Impuesto'] === '002') { // IVA
                    $iva += (float) $traslado['Importe'];
                }
            }

            $retenciones = $xml->xpath('//cfdi:Impuestos/cfdi:Retenciones/cfdi:Retencion');
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
}
