<?php

namespace App\Http\Controllers;

use App\Models\IncomePolicyType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomePolicyTypeController extends Controller
{
    public function index()
    {
        return Inertia::render('IncomePolicyTypes/Index', [
            'types' => IncomePolicyType::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:income_policy_types,name',
        ]);

        IncomePolicyType::create($data);

        return back()->with('success', 'Tipo de póliza agregado.');
    }

    public function update(Request $request, IncomePolicyType $incomePolicyType)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100|unique:income_policy_types,name,' . $incomePolicyType->id,
        ]);

        $incomePolicyType->update($data);

        return back()->with('success', 'Tipo de póliza actualizado.');
    }

    public function destroy(IncomePolicyType $incomePolicyType)
    {
        $incomePolicyType->delete();

        return back()->with('success', 'Tipo de póliza eliminado.');
    }
}
