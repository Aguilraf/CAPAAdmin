<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
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
            'settings' => $settings,
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
            'settings' => $settings,
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
}
