<?php

namespace App\Http\Controllers;

use App\Models\Capitulo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CapituloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Capitulo::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('codigo', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%");
            });
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }

        $capitulos = $query->orderBy('codigo')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Capitulos/Index', [
            'capitulos' => $capitulos,
            'filters' => $request->only(['search', 'activo']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Capitulos/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:capitulos,codigo',
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        Capitulo::create($validated);

        return redirect()->route('capitulos.index')
            ->with('success', 'Capítulo creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Capitulo $capitulo)
    {
        return Inertia::render('Capitulos/Edit', [
            'capitulo' => $capitulo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Capitulo $capitulo)
    {
        $validated = $request->validate([
            'codigo' => 'required|string|unique:capitulos,codigo,' . $capitulo->id,
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean',
        ]);

        $capitulo->update($validated);

        return redirect()->route('capitulos.index')
            ->with('success', 'Capítulo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Capitulo $capitulo)
    {
        $capitulo->delete();

        return redirect()->route('capitulos.index')
            ->with('success', 'Capítulo eliminado exitosamente.');
    }
}
