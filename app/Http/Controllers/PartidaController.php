<?php

namespace App\Http\Controllers;

use App\Models\Capitulo;
use App\Models\Partida;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartidaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Partida::query()->with('capitulo');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhereHas('capitulo', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                            ->orWhere('codigo', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }

        $partidas = $query->orderBy('codigo')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('Partidas/Index', [
            'partidas' => $partidas,
            'filters' => $request->only(['search', 'activo']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $capitulos = Capitulo::activos()->orderBy('codigo')->get();

        return Inertia::render('Partidas/Create', [
            'capitulos' => $capitulos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'capitulo_id' => 'required|exists:capitulos,id',
            'codigo' => 'required|string|unique:partidas,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        Partida::create($validated);

        return redirect()->route('partidas.index')
            ->with('success', 'Partida creada exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Partida $partida)
    {
        $capitulos = Capitulo::activos()->orderBy('codigo')->get();

        return Inertia::render('Partidas/Edit', [
            'partida' => $partida,
            'capitulos' => $capitulos,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Partida $partida)
    {
        $validated = $request->validate([
            'capitulo_id' => 'required|exists:capitulos,id',
            'codigo' => 'required|string|unique:partidas,codigo,' . $partida->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $partida->update($validated);

        return redirect()->route('partidas.index')
            ->with('success', 'Partida actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partida $partida)
    {
        $partida->delete();

        return redirect()->route('partidas.index')
            ->with('success', 'Partida eliminada exitosamente.');
    }
}
