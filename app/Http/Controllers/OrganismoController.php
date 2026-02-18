<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Organismo;

class OrganismoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Catalogs/Organismos/Index', [
            'organismos' => Organismo::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'foto' => 'nullable|image|max:2048', // 2MB Max
            'ubicacion' => 'nullable|string',
        ]);

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('organismos', 'public');
            $validated['foto'] = $path;
        }

        $organismo = Organismo::create($validated);

        return redirect()->back()->with('success', 'Organismo creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Organismo $organismo)
    {
        return $organismo;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Organismo $organismo)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'direccion' => 'nullable|string',
            'telefono' => 'nullable|string|max:20',
            'correo' => 'nullable|email|max:255',
            'foto' => 'nullable|image|max:2048',
            'ubicacion' => 'nullable|string',
        ]);

        if ($request->hasFile('foto')) {
            // Delete old photo if exists
            if ($organismo->foto && \Illuminate\Support\Facades\Storage::disk('public')->exists($organismo->foto)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($organismo->foto);
            }
            $path = $request->file('foto')->store('organismos', 'public');
            $validated['foto'] = $path;
        }

        $organismo->update($validated);

        return redirect()->back()->with('success', 'Organismo actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Organismo $organismo)
    {
        if ($organismo->id === 1) {
            return redirect()->back()->with('error', 'No se puede eliminar el organismo por defecto.');
        }

        $organismo->delete();

        return redirect()->back()->with('success', 'Organismo eliminado exitosamente.');
    }
}
