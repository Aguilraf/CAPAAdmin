<?php

namespace App\Http\Controllers;

use App\Models\Puesto;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PuestoController extends Controller
{
    public function index(Request $request)
    {
        $query = Puesto::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('nombre', 'like', "%{$search}%")
                ->orWhere('nivel', 'like', "%{$search}%");
        }

        $puestos = $query->orderBy('nombre')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Catalogs/Puestos/Index', [
            'puestos' => $puestos,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Catalogs/Puestos/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'nivel' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        Puesto::create($validated);

        return redirect()->route('puestos.index')
            ->with('success', 'Puesto creado correctamente.');
    }

    public function edit(Puesto $puesto)
    {
        return Inertia::render('Catalogs/Puestos/Edit', [
            'puesto' => $puesto,
        ]);
    }

    public function update(Request $request, Puesto $puesto)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'nivel' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $puesto->update($validated);

        return redirect()->route('puestos.index')
            ->with('success', 'Puesto actualizado correctamente.');
    }

    public function destroy(Puesto $puesto)
    {
        $puesto->delete();

        return redirect()->back()
            ->with('success', 'Puesto eliminado correctamente.');
    }
}
