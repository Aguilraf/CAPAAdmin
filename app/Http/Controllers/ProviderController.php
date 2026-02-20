<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Provider::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('rfc', 'like', "%{$search}%");
            });
        }

        $providers = $query->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Catalogs/Providers/Index', [
            'providers' => $providers,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Catalogs/Providers/Form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'clabe' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        Provider::create($validated);

        return redirect()->route('providers.index')
            ->with('success', 'Proveedor creado exitosamente.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Provider $provider)
    {
        return Inertia::render('Catalogs/Providers/Form', [
            'provider' => $provider,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Provider $provider)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'rfc' => 'nullable|string|max:13',
            'bank_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'clabe' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        $provider->update($validated);

        return redirect()->route('providers.index')
            ->with('success', 'Proveedor actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Provider $provider)
    {
        $provider->delete();

        return redirect()->route('providers.index')
            ->with('success', 'Proveedor eliminado exitosamente.');
    }
}
