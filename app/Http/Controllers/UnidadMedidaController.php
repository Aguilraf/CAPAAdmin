<?php

namespace App\Http\Controllers;

use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnidadMedidaController extends Controller
{
    public function index(Request $request)
    {
        $query = UnidadMedida::query();

        if ($request->has('search')) {
            $query->where('nombre', 'like', "%{$request->search}%");
        }

        $unidades = $query->orderBy('nombre')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('UnidadMedidas/Index', [
            'unidades' => $unidades,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('UnidadMedidas/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:unidad_medidas',
        ]);

        UnidadMedida::create($validated);

        return redirect()->route('unidades-medida.index')
            ->with('success', 'Unidad de medida creada exitosamente.');
    }

    public function edit(UnidadMedida $unidades_medida)
    {
        return Inertia::render('UnidadMedidas/Edit', [
            'unidad' => $unidades_medida,
        ]);
    }

    public function update(Request $request, UnidadMedida $unidades_medida)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255|unique:unidad_medidas,nombre,' . $unidades_medida->id,
        ]);

        $unidades_medida->update($validated);

        return redirect()->route('unidades-medida.index')
            ->with('success', 'Unidad de medida actualizada exitosamente.');
    }

    public function destroy(UnidadMedida $unidades_medida)
    {
        $unidades_medida->delete();

        return redirect()->route('unidades-medida.index')
            ->with('success', 'Unidad de medida eliminada exitosamente.');
    }
}