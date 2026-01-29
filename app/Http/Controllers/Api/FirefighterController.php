<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Firefighter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FirefighterController extends Controller
{
    public function index()
    {
        return Firefighter::with('community')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'community_id' => 'required|exists:communities,id',
            'active' => 'boolean',
            'contact_number' => 'nullable|string',
            'photo' => 'nullable|image|max:2048', // Expecting 'photo' file
            'geolocation' => 'nullable|string',
            'previous_firefighter' => 'nullable|string',
            'change_date' => 'nullable|date',
            'max_rounding_amount' => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('photo')) {
            $validated['credential_photo_path'] = $request->file('photo')->store('photos', 'public');
        }

        // Remove 'photo' from validated array since it's not a database field
        unset($validated['photo']);

        $firefighter = Firefighter::create($validated);

        return response()->json($firefighter, 201);
    }

    public function show(Firefighter $firefighter)
    {
        return $firefighter->load('community');
    }

    public function update(Request $request, Firefighter $firefighter)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'community_id' => 'sometimes|exists:communities,id',
            'active' => 'boolean',
            'contact_number' => 'nullable|string',
            'photo' => 'nullable|image|max:2048',
            'geolocation' => 'nullable|string',
            'previous_firefighter' => 'nullable|string',
            'change_date' => 'nullable|date',
            'max_rounding_amount' => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($firefighter->credential_photo_path) {
                Storage::disk('public')->delete($firefighter->credential_photo_path);
            }
            $validated['credential_photo_path'] = $request->file('photo')->store('photos', 'public');
        }

        // Remove 'photo' from validated array since it's not a database field
        unset($validated['photo']);

        $firefighter->update($validated);

        return $firefighter;
    }

    public function destroy(Firefighter $firefighter)
    {
        if ($firefighter->credential_photo_path) {
            Storage::disk('public')->delete($firefighter->credential_photo_path);
        }
        $firefighter->delete();
        return response()->noContent();
    }
}
