<?php

namespace App\Http\Controllers;

use App\Models\TravelAllowanceRate;
use App\Models\Partida;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TravelAllowanceRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TravelAllowanceRate::with('partida');

        // Apply filters
        if ($request->filled('year')) {
            $query->forYear($request->year);
        }
        if ($request->filled('cargo')) {
            $query->where('cargo', 'like', '%' . $request->cargo . '%');
        }
        if ($request->filled('nivel')) {
            $query->where('nivel', $request->nivel);
        }
        if ($request->filled('rate_type')) {
            $query->byType($request->rate_type);
        }
        if ($request->filled('active')) {
            $query->where('active', $request->active);
        }

        $rates = $query->orderBy('year', 'desc')
            ->orderBy('cargo')
            ->orderBy('nivel')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('TravelAllowanceRates/Index', [
            'rates' => $rates,
            'filters' => $request->only(['year', 'cargo', 'nivel', 'rate_type', 'active']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $partidas = Partida::whereIn('codigo', ['37201', '37301', '37501', '37502'])
            ->orderBy('codigo')
            ->get();

        // Fetch distinct levels from employees
        $niveles = \App\Models\Empleado::whereNotNull('nivel')
            ->where('nivel', '!=', '')
            ->distinct()
            ->orderBy('nivel')
            ->pluck('nivel');

        return Inertia::render('TravelAllowanceRates/Create', [
            'partidas' => $partidas,
            'niveles' => $niveles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'partida_id' => 'required|exists:partidas,id',
            'cargo' => 'required|string|max:255',
            'nivel' => 'required|array|min:1', // Validate as array
            'nivel.*' => 'string|max:255',     // Validate each item
            'zona_1_amount' => 'required|numeric|min:0',
            'zona_2_amount' => 'required|numeric|min:0',
            'rate_type' => 'required|in:viaticos,pasajes,hospedaje',
            'year' => 'required|integer|min:2020|max:2100',
            'active' => 'boolean',
        ]);

        foreach ($validated['nivel'] as $nivel) {
            TravelAllowanceRate::create([
                'partida_id' => $validated['partida_id'],
                'cargo' => $validated['cargo'],
                'nivel' => $nivel,
                'zona_1_amount' => $validated['zona_1_amount'],
                'zona_2_amount' => $validated['zona_2_amount'],
                'rate_type' => $validated['rate_type'],
                'year' => $validated['year'],
                'active' => $validated['active'] ?? true,
            ]);
        }

        return redirect()->route('travel-allowance-rates.index')
            ->with('success', 'Tarifas creadas exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TravelAllowanceRate $travelAllowanceRate)
    {
        $partidas = Partida::whereIn('codigo', ['37201', '37301', '37501', '37502'])
            ->orderBy('codigo')
            ->get();

        // Fetch distinct levels from employees
        $niveles = \App\Models\Empleado::whereNotNull('nivel')
            ->where('nivel', '!=', '')
            ->distinct()
            ->orderBy('nivel')
            ->pluck('nivel');

        return Inertia::render('TravelAllowanceRates/Edit', [
            'rate' => $travelAllowanceRate->load('partida'),
            'partidas' => $partidas,
            'niveles' => $niveles,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TravelAllowanceRate $travelAllowanceRate)
    {
        $validated = $request->validate([
            'partida_id' => 'required|exists:partidas,id',
            'cargo' => 'required|string|max:255',
            'nivel' => 'required|string|max:255',
            'zona_1_amount' => 'required|numeric|min:0',
            'zona_2_amount' => 'required|numeric|min:0',
            'rate_type' => 'required|in:viaticos,pasajes,hospedaje',
            'year' => 'required|integer|min:2020|max:2100',
            'active' => 'boolean',
        ]);

        $travelAllowanceRate->update($validated);

        return redirect()->route('travel-allowance-rates.index')
            ->with('success', 'Tarifa actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TravelAllowanceRate $travelAllowanceRate)
    {
        $travelAllowanceRate->delete();

        return redirect()->route('travel-allowance-rates.index')
            ->with('success', 'Tarifa eliminada exitosamente.');
    }
}
