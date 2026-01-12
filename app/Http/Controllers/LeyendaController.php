<?php

namespace App\Http\Controllers;

use App\Models\Leyenda;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeyendaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Leyenda::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('anio', 'like', "%{$search}%")
                    ->orWhere('texto', 'like', "%{$search}%");
            });
        }

        if ($request->has('activa')) {
            $query->where('activa', $request->activa);
        }

        $leyendas = $query->orderBy('anio', 'desc')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Leyendas/Index', [
            'leyendas' => $leyendas,
            'filters' => $request->only(['search', 'activa']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Leyendas/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'anio' => 'required|integer|digits:4|unique:leyendas,anio',
            'texto' => 'required|string',
            'activa' => 'boolean',
        ]);

        try {
            if ($validated['activa'] ?? false) {
                // Deactivate other legends for the same year/globally if needed.
                // For now, we assume simple logic.
            }

            $validated['user_id'] = $request->user()->id;

            Leyenda::create($validated);

            return redirect()->route('leyendas.index')
                ->with('success', 'Leyenda creada exitosamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Error al guardar la leyenda: ' . $e->getMessage()])
                ->withInput(); // Return old input so user doesn't lose data
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Leyenda $leyenda)
    {
        return Inertia::render('Leyendas/Edit', [
            'leyenda' => $leyenda,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Leyenda $leyenda)
    {
        $validated = $request->validate([
            'anio' => 'required|integer|digits:4|unique:leyendas,anio,' . $leyenda->id,
            'texto' => 'required|string',
            'activa' => 'boolean',
        ]);

        $leyenda->update($validated);

        return redirect()->route('leyendas.index')
            ->with('success', 'Leyenda actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Leyenda $leyenda)
    {
        $leyenda->delete();

        return redirect()->route('leyendas.index')
            ->with('success', 'Leyenda eliminada exitosamente.');
    }
}
