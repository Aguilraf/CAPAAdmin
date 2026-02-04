<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Imports\EmpleadosImport;
use Maatwebsite\Excel\Facades\Excel;

class EmpleadoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Empleado::query();

        // Búsqueda
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('clave', 'like', "%{$search}%")
                    ->orWhere('puesto', 'like', "%{$search}%")
                    ->orWhere('departamento', 'like', "%{$search}%");
            });
        }

        // Filtro por activos
        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }

        $empleados = $query->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Empleados/Index', [
            'empleados' => $empleados,
            'filters' => $request->only(['search', 'activo']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $posiblesJefes = Empleado::where('activo', true)
            ->where(function ($query) {
                $query->where('puesto', 'LIKE', '%JEFE%')
                    ->orWhere('puesto', 'LIKE', '%GERENTE%')
                    ->orWhere('puesto', 'LIKE', '%SUBGERENTE%');
            })
            ->orderBy('nombre')
            ->get()
            ->map(function ($empleado) {
                return $empleado->nombre . ' - ' . $empleado->puesto;
            })
            ->values();

        return Inertia::render('Empleados/Create', [
            'posiblesJefes' => $posiblesJefes
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug: Log incoming data
        \Log::info('Empleado Store - Request Data:', $request->all());

        $validated = $request->validate([
            'clave' => 'required|string|unique:empleados,clave',
            'nombre' => 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'categoria' => 'required|in:BASE,CONFIANZA',
            'fecha_alta' => 'required|date',
            'fecha_nacimiento' => 'nullable|date',
            'nivel' => 'nullable|string|max:255',
            'salario_diario' => 'nullable|numeric|min:0',
            'salario_mensual' => 'nullable|numeric|min:0',
            'curp' => 'nullable|string|max:18',
            'nss' => 'nullable|string|max:20',
            'afiliacion' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'numero_empleado' => 'nullable|string|max:255',
            'activo' => 'boolean',
            'es_gerente' => 'boolean',
            'jefe_inmediato' => 'nullable|string|max:255',
        ]);

        // Regla de Negocio: BASE = Sindicalizado
        $validated['es_sindicalizado'] = $validated['categoria'] === 'BASE';

        Empleado::create($validated);

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Empleado $empleado)
    {
        return Inertia::render('Empleados/Show', [
            'empleado' => $empleado,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Empleado $empleado)
    {
        $posiblesJefes = Empleado::where('activo', true)
            ->where(function ($query) {
                $query->where('puesto', 'LIKE', '%JEFE%')
                    ->orWhere('puesto', 'LIKE', '%GERENTE%')
                    ->orWhere('puesto', 'LIKE', '%SUBGERENTE%');
            })
            ->orderBy('nombre')
            ->get()
            ->map(function ($empleado) {
                return $empleado->nombre . ' - ' . $empleado->puesto;
            })
            ->values();

        return Inertia::render('Empleados/Edit', [
            'empleado' => $empleado,
            'posiblesJefes' => $posiblesJefes
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Empleado $empleado)
    {
        $validated = $request->validate([
            'clave' => 'required|string|unique:empleados,clave,' . $empleado->id,
            'nombre' => 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'categoria' => 'required|in:BASE,CONFIANZA',
            'fecha_alta' => 'required|date',
            'fecha_nacimiento' => 'nullable|date',
            'nivel' => 'nullable|string|max:255',
            'salario_diario' => 'nullable|numeric|min:0',
            'salario_mensual' => 'nullable|numeric|min:0',
            'curp' => 'nullable|string|max:18',
            'nss' => 'nullable|string|max:20',
            'afiliacion' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'numero_empleado' => 'nullable|string|max:255',
            'activo' => 'boolean',
            'es_gerente' => 'boolean',
            'jefe_inmediato' => 'nullable|string|max:255',
        ]);

        // Regla de Negocio: BASE = Sindicalizado
        $validated['es_sindicalizado'] = $validated['categoria'] === 'BASE';

        $empleado->update($validated);

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Empleado $empleado)
    {
        $empleado->delete();

        return redirect()->route('empleados.index')
            ->with('success', 'Empleado eliminado exitosamente.');
    }

    /**
     * Import employees from Excel file.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240', // Max 10MB
        ]);

        try {
            Excel::import(new EmpleadosImport, $request->file('file'));

            return redirect()->route('empleados.index')
                ->with('success', 'Empleados importados exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al importar: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template for importing employees.
     */
    public function downloadTemplate()
    {
        return Excel::download(new \App\Exports\EmpleadosTemplateExport, 'plantilla_empleados.xlsx');
    }
}
