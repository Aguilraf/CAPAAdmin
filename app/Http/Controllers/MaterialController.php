<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $query = Material::query()->with('unidadMedida');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('articulo', 'like', "%{$search}%");
        }

        $materiales = $query->orderBy('articulo')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Materiales/Index', [
            'materiales' => $materiales,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Materiales/Create', [
            'unidades' => UnidadMedida::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'articulo' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'es_default' => 'boolean',
            'unidad_medida_id' => 'required|exists:unidad_medidas,id',
        ]);

        Material::create($validated);

        return redirect()->route('materiales.index')
            ->with('success', 'Material creado exitosamente.');
    }

    public function edit(Material $materiale)
    {
        return Inertia::render('Materiales/Edit', [
            'material' => $materiale->load('unidadMedida'),
            'unidades' => UnidadMedida::orderBy('nombre')->get(),
        ]);
    }

    public function update(Request $request, Material $materiale)
    {
        $validated = $request->validate([
            'articulo' => 'required|string|max:255',
            'cantidad' => 'required|integer|min:0',
            'es_default' => 'boolean',
            'unidad_medida_id' => 'required|exists:unidad_medidas,id',
        ]);

        $materiale->update($validated);

        return redirect()->route('materiales.index')
            ->with('success', 'Material actualizado exitosamente.');
    }

    public function destroy(Material $materiale)
    {
        $materiale->delete();

        return redirect()->route('materiales.index')
            ->with('success', 'Material eliminado exitosamente.');
    }
}
