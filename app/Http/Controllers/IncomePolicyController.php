<?php

namespace App\Http\Controllers;

use App\Models\IncomePolicy;
use App\Models\IncomeAccount;
use App\Models\IncomePolicyType;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomePolicyController extends Controller
{
    public function index(Request $request)
    {
        $policies = IncomePolicy::with('details.account')
            ->when($request->filled('search'), function ($query) use ($request) {
                $query->where(function ($subQuery) use ($request) {
                    $subQuery->where('policy_number', 'like', '%' . $request->search . '%')
                        ->orWhere('policy_type', 'like', '%' . $request->search . '%')
                        ->orWhere('concept', 'like', '%' . $request->search . '%');
                });
            })
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('IncomePolicies/Index', [
            'policies' => $policies,
            'filters' => $request->only('search'),
        ]);
    }

    public function create()
    {
        return Inertia::render('IncomePolicies/Create', [
            'accounts' => IncomeAccount::where('visible', true)->orderBy('accounting_account')->get(['id', 'budget_account', 'accounting_account', 'concept']),
            'policyTypes' => IncomePolicyType::where('active', true)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'policy_number' => 'required|string|max:100',
            'policy_type' => 'required|string|max:100',
            'concept' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'observations' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.income_account_id' => 'required|integer|exists:income_accounts,id',
            'details.*.amount' => 'required|numeric|min:0.01',
        ]);

        $accountIds = collect($data['details'])->pluck('income_account_id');
        $accounts = IncomeAccount::whereIn('id', $accountIds)->where('visible', true)->get()->keyBy('id');
        if ($accounts->count() !== $accountIds->unique()->count()) {
            return back()->withErrors(['details' => 'Solo puedes utilizar cuentas visibles del catálogo.'])->withInput();
        }

        DB::transaction(function () use ($data) {
            $amount = collect($data['details'])->sum(fn ($detail) => (float) $detail['amount']);
            $policy = IncomePolicy::create([
                'policy_number' => $data['policy_number'],
                'policy_type' => $data['policy_type'],
                'concept' => $data['concept'],
                'amount' => $amount,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'observations' => $data['observations'] ?? null,
            ]);
            $policy->details()->createMany($data['details']);
        });

        return redirect()->route('income-policies.create')
            ->with('success', 'Póliza de ingreso guardada correctamente.');
    }

    public function edit(IncomePolicy $incomePolicy)
    {
        $incomePolicy->load('details.account');
        $selectedIds = $incomePolicy->details->pluck('income_account_id');
        $accounts = IncomeAccount::where('visible', true)
            ->orWhereIn('id', $selectedIds)
            ->orderBy('accounting_account')
            ->get(['id', 'budget_account', 'accounting_account', 'concept']);

        return Inertia::render('IncomePolicies/Edit', [
            'policy' => $incomePolicy,
            'accounts' => $accounts,
            'policyTypes' => IncomePolicyType::where('active', true)->orWhere('name', $incomePolicy->policy_type)->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, IncomePolicy $incomePolicy)
    {
        $data = $request->validate([
            'policy_number' => 'required|string|max:100',
            'policy_type' => 'required|string|max:100',
            'concept' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'observations' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.income_account_id' => 'required|integer|exists:income_accounts,id',
            'details.*.amount' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function () use ($data, $incomePolicy) {
            $incomePolicy->update([
                'policy_number' => $data['policy_number'],
                'policy_type' => $data['policy_type'],
                'concept' => $data['concept'],
                'amount' => collect($data['details'])->sum(fn ($detail) => (float) $detail['amount']),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
                'observations' => $data['observations'] ?? null,
            ]);
            $incomePolicy->details()->delete();
            $incomePolicy->details()->createMany($data['details']);
        });

        return redirect()->route('income-policies.index')->with('success', 'Póliza actualizada correctamente.');
    }

    public function destroy(IncomePolicy $incomePolicy)
    {
        $incomePolicy->delete();

        return back()->with('success', 'Póliza eliminada correctamente.');
    }
}
