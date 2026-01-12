<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        return Inertia::render('Empleados/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'clave' => 'required|string|unique:empleados,clave',
            'nombre' => 'required|string|max:255',
            'puesto' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'categoria' => 'nullable|string|max:255',
            'fecha_alta' => 'nullable|date',
            'salario_diario' => 'nullable|numeric|min:0',
            'salario_mensual' => 'nullable|numeric|min:0',
            'curp' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'numero_empleado' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

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
        return Inertia::render('Empleados/Edit', [
            'empleado' => $empleado,
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
            'categoria' => 'nullable|string|max:255',
            'fecha_alta' => 'nullable|date',
            'salario_diario' => 'nullable|numeric|min:0',
            'salario_mensual' => 'nullable|numeric|min:0',
            'curp' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'numero_empleado' => 'nullable|string|max:255',
            'activo' => 'boolean',
        ]);

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
}
