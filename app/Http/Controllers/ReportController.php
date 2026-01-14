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
    public function reprint(Request $request, $id)
    {
        $bitacora = \App\Models\ReporteBitacora::findOrFail($id);

        // Fetch Branding Settings
        $keys = ['logo_qroo', 'logo_unidos', 'logo_capa', 'footer_organismo', 'footer_direccion', 'footer_telefono', 'footer_email', 'footer_imagen'];
        $settings = \App\Models\Setting::whereIn('key', $keys)->pluck('value', 'key');

        return Inertia::render('Reports/MaterialRequest/Print', [
            'data' => $bitacora->datos_completos,
            'settings' => $settings,
        ]);
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

        // Fetch default materials (pre-filled in the materials list)
        $materialesDefault = Material::with('unidadMedida')
            ->where('es_default', true)
            ->orderBy('articulo')
            ->get()
            ->map(function ($material) {
                return [
                    'id' => $material->id,
                    'articulo' => $material->articulo,
                    'unidad' => $material->unidadMedida ? $material->unidadMedida->nombre : '',
                    'cantidad' => $material->cantidad ?: 1, // Use stored quantity or default to 1
                ];
            });

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
        $user = auth()->user();
        if ($user && $user->empleado_id) {
            $empleadoActual = \App\Models\Empleado::find($user->empleado_id);
        }

        return Inertia::render('Reports/MaterialRequest/Create', [
            'materiales' => $materiales,
            'materialesDefault' => $materialesDefault,
            'manager' => $manager,
            'empleadoActual' => $empleadoActual ? [
                'nombre' => $empleadoActual->nombre,
                'puesto' => $empleadoActual->puesto,
                'departamento' => $empleadoActual->departamento,
            ] : null,
        ]);
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

        // Process items to ensure they have all display data
        // If we trust the client to send the names, we can use them directly.
        // Or we can re-fetch to be safe. For a print view, using client data 
        // (which might have been edited in the UI) is often flexible.
        // Let's rely on the posted data which should include the display strings.

        return Inertia::render('Reports/MaterialRequest/Print', [
            'data' => $validated,
        ]);
    }
}
