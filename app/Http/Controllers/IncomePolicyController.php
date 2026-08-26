<?php

namespace App\Http\Controllers;

use App\Models\IncomePolicy;
use App\Models\IncomeAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomePolicyController extends Controller
{
    public function create()
    {
        return Inertia::render('IncomePolicies/Create', [
            'accounts' => IncomeAccount::where('visible', true)->orderBy('accounting_account')->get(['id', 'budget_account', 'accounting_account', 'concept']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'policy_number' => 'required|string|max:100',
            'policy_type' => 'required|string|max:100',
            'account' => 'required|string|max:150',
            'concept' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'observations' => 'nullable|string',
        ]);

        IncomePolicy::create($data);

        return redirect()->route('income-policies.create')
            ->with('success', 'Póliza de ingreso guardada correctamente.');
    }
}
