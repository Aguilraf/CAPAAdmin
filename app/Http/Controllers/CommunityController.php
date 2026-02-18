<?php

namespace App\Http\Controllers;

use App\Models\Community;
use Illuminate\Http\Request;

class CommunityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Community::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'geolocation' => 'nullable|string',
            'location_image' => 'nullable|image|max:5120', // 5MB max
            'percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->hasFile('location_image')) {
            $path = $request->file('location_image')->store('communities', 'public');
            $validated['location_image_path'] = $path;
        }

        $community = Community::create($validated);

        return response()->json($community, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Community $community)
    {
        return $community;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Community $community)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'geolocation' => 'nullable|string',
            'location_image' => 'nullable|image|max:5120', // 5MB max
            'percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($request->hasFile('location_image')) {
            // Eliminar imagen anterior si existe
            if ($community->location_image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($community->location_image_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($community->location_image_path);
            }

            $path = $request->file('location_image')->store('communities', 'public');
            $validated['location_image_path'] = $path;
        }

        $community->update($validated);

        return $community;
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Community $community)
    {
        if ($community->location_image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($community->location_image_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($community->location_image_path);
        }

        $community->delete();

        return response()->noContent();
    }
}
