<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = Vehicle::all();
        return response()->json($vehicles);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'inventory_number' => 'nullable|string',
            'unit_number' => 'nullable|string',
            'brand' => 'nullable|string',
            'type' => 'nullable|string',
            'color' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'motor_number' => 'nullable|string',
            'assignee_area' => 'nullable|string',
            'plate' => 'nullable|string',
            'resguardante' => 'nullable|string',
        ]);

        $vehicle = Vehicle::create($validated);

        return response()->json($vehicle, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'inventory_number' => 'nullable|string',
            'unit_number' => 'nullable|string',
            'brand' => 'nullable|string',
            'type' => 'nullable|string',
            'color' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'motor_number' => 'nullable|string',
            'assignee_area' => 'nullable|string',
            'plate' => 'nullable|string',
            'resguardante' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $vehicle->update($validated);

        return response()->json($vehicle);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return response()->json(null, 204);
    }
}
