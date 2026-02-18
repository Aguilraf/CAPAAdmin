<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Organismo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class VehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vehicles = Vehicle::with('organismo')
            ->orderBy('inventory_number')
            ->paginate(10);

        return Inertia::render('Catalogs/Vehicles/Index', [
            'vehicles' => $vehicles
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organismos = Organismo::orderBy('nombre')->get();
        return Inertia::render('Catalogs/Vehicles/Create', [
            'organismos' => $organismos
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'organismo_id' => 'nullable|exists:organismos,id',
            'inventory_number' => 'required|string|unique:vehicles,inventory_number',
            'unit_type' => 'required|string',
            'brand' => 'required|string',
            'vehicle_type' => 'required|string',
            'color' => 'nullable|string',
            'model_year' => 'required|string',
            'serial_number' => 'required|string|unique:vehicles,serial_number',
            'engine_number' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'supplier' => 'nullable|string',
            'policy_number' => 'nullable|string',
            'area' => 'nullable|string',
            'location' => 'nullable|string',
            'sub_location' => 'nullable|string',
            'custodian' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'photo' => 'nullable|image|max:2048', // 2MB max
            'active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('vehicles', 'public');
            $validated['photo_path'] = $path;
        }

        Vehicle::create($validated);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehículo creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vehicle $vehicle)
    {
        $organismos = Organismo::orderBy('nombre')->get();
        return Inertia::render('Catalogs/Vehicles/Edit', [
            'vehicle' => $vehicle,
            'organismos' => $organismos
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'organismo_id' => 'nullable|exists:organismos,id',
            'inventory_number' => [
                'required',
                'string',
                Rule::unique('vehicles')->ignore($vehicle->id),
            ],
            'unit_type' => 'required|string',
            'brand' => 'required|string',
            'vehicle_type' => 'required|string',
            'color' => 'nullable|string',
            'model_year' => 'required|string',
            'serial_number' => [
                'required',
                'string',
                Rule::unique('vehicles')->ignore($vehicle->id),
            ],
            'engine_number' => 'nullable|string',
            'invoice_number' => 'nullable|string',
            'supplier' => 'nullable|string',
            'policy_number' => 'nullable|string',
            'area' => 'nullable|string',
            'location' => 'nullable|string',
            'sub_location' => 'nullable|string',
            'custodian' => 'nullable|string',
            'plate_number' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'active' => 'boolean',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($vehicle->photo_path) {
                Storage::disk('public')->delete($vehicle->photo_path);
            }
            $path = $request->file('photo')->store('vehicles', 'public');
            $validated['photo_path'] = $path;
        }

        $vehicle->update($validated);

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehículo actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vehicle $vehicle)
    {
        // Delete photo if exists
        if ($vehicle->photo_path) {
            Storage::disk('public')->delete($vehicle->photo_path);
        }

        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', 'Vehículo eliminado.');
    }
}
