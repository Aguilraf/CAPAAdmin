<?php

namespace App\Http\Controllers;

use App\Models\TravelExpenseRate;
use Illuminate\Http\Request;

class TravelExpenseRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rates = TravelExpenseRate::all();
        return response()->json($rates);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_level' => 'required|string',
            'concept' => 'required|string',
            'zone_i_limit' => 'numeric',
            'zone_ii_limit' => 'numeric',
            'effective_date' => 'nullable|date',
        ]);

        $rate = TravelExpenseRate::create($validated);

        return response()->json($rate, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TravelExpenseRate $travelExpenseRate)
    {
        $validated = $request->validate([
            'role_level' => 'sometimes|string',
            'concept' => 'sometimes|string',
            'zone_i_limit' => 'numeric',
            'zone_ii_limit' => 'numeric',
            'effective_date' => 'nullable|date',
        ]);

        $travelExpenseRate->update($validated);

        return response()->json($travelExpenseRate);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TravelExpenseRate $travelExpenseRate)
    {
        $travelExpenseRate->delete();
        return response()->json(null, 204);
    }
}
