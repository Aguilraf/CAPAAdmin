<?php

namespace App\Http\Controllers;

use App\Models\IncomePolicy;
use App\Models\IncomeAccount;
use App\Models\IncomePolicyType;
use App\Models\BankMovement;
use App\Models\Bank;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IncomePolicyController extends Controller
{
    const DNI_TYPE = 'DNI (pagos no identificados)';
    const IVA_RATE = 0.16;

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
            'banks' => Bank::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function dniMovements(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'bank_id' => 'nullable|integer|exists:banks,id',
            'income_policy_id' => 'nullable|integer|exists:income_policies,id',
        ]);

        $date = \Carbon\Carbon::parse($request->date);

        $movements = BankMovement::with('bank')
            ->where('is_visible', true)
            ->whereYear('operation_date', $date->year)
            ->whereMonth('operation_date', $date->month)
            ->where(function ($query) use ($request) {
                $query->where('is_used', false);
                if ($request->filled('income_policy_id')) {
                    $query->orWhere('income_policy_id', $request->income_policy_id);
                }
            })
            ->when($request->filled('bank_id'), fn ($query) => $query->where('bank_id', $request->bank_id))
            ->orderBy('bank_id')
            ->orderBy('operation_date', 'asc')
            ->get();

        return response()->json($movements);
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
            'details' => 'required_unless:policy_type,' . self::DNI_TYPE . '|array',
            'details.*.income_account_id' => 'required_with:details|integer|exists:income_accounts,id',
            'details.*.amount' => 'required_with:details|numeric|min:0.01',
            'movement_ids' => 'required_if:policy_type,' . self::DNI_TYPE . '|array|min:1',
            'movement_ids.*' => 'integer|exists:bank_movements,id',
        ]);

        $isDni = $data['policy_type'] === self::DNI_TYPE;

        if (!$isDni) {
            $accountIds = collect($data['details'])->pluck('income_account_id');
            $accounts = IncomeAccount::whereIn('id', $accountIds)->where('visible', true)->get()->keyBy('id');
            if ($accounts->count() !== $accountIds->unique()->count()) {
                return back()->withErrors(['details' => 'Solo puedes utilizar cuentas visibles del catálogo.'])->withInput();
            }
        }

        DB::transaction(function () use ($data, $isDni) {
            if ($isDni) {
                $movements = BankMovement::whereIn('id', $data['movement_ids'])
                    ->where('is_used', false)
                    ->lockForUpdate()
                    ->get();

                if ($movements->count() !== count($data['movement_ids'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'movement_ids' => 'Uno o más movimientos ya fueron utilizados.',
                    ]);
                }

                $total = $movements->sum('credit_amount');
                $iva = round($total - ($total / (1 + self::IVA_RATE)), 2);

                $policy = IncomePolicy::create([
                    'policy_number' => $data['policy_number'],
                    'policy_type' => $data['policy_type'],
                    'concept' => $data['concept'],
                    'amount' => $total,
                    'iva_amount' => $iva,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'observations' => $data['observations'] ?? null,
                ]);

                BankMovement::whereIn('id', $data['movement_ids'])
                    ->update(['income_policy_id' => $policy->id, 'is_used' => true]);

                return;
            }

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
        $incomePolicy->load('details.account', 'bankMovements.bank');
        $selectedIds = $incomePolicy->details->pluck('income_account_id');
        $accounts = IncomeAccount::where('visible', true)
            ->orWhereIn('id', $selectedIds)
            ->orderBy('accounting_account')
            ->get(['id', 'budget_account', 'accounting_account', 'concept']);

        return Inertia::render('IncomePolicies/Edit', [
            'policy' => $incomePolicy,
            'accounts' => $accounts,
            'policyTypes' => IncomePolicyType::where('active', true)->orWhere('name', $incomePolicy->policy_type)->orderBy('name')->get(['id', 'name']),
            'banks' => Bank::orderBy('name')->get(['id', 'name']),
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
            'details' => 'required_unless:policy_type,' . self::DNI_TYPE . '|array',
            'details.*.income_account_id' => 'required_with:details|integer|exists:income_accounts,id',
            'details.*.amount' => 'required_with:details|numeric|min:0.01',
            'movement_ids' => 'required_if:policy_type,' . self::DNI_TYPE . '|array|min:1',
            'movement_ids.*' => 'integer|exists:bank_movements,id',
        ]);

        $isDni = $data['policy_type'] === self::DNI_TYPE;

        DB::transaction(function () use ($data, $incomePolicy, $isDni) {
            // Liberar movimientos previamente ligados que ya no se seleccionaron.
            BankMovement::where('income_policy_id', $incomePolicy->id)
                ->whereNotIn('id', $isDni ? $data['movement_ids'] : [])
                ->update(['income_policy_id' => null, 'is_used' => false]);

            if ($isDni) {
                $movements = BankMovement::whereIn('id', $data['movement_ids'])
                    ->where(function ($query) use ($incomePolicy) {
                        $query->where('is_used', false)->orWhere('income_policy_id', $incomePolicy->id);
                    })
                    ->lockForUpdate()
                    ->get();

                if ($movements->count() !== count($data['movement_ids'])) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'movement_ids' => 'Uno o más movimientos ya fueron utilizados.',
                    ]);
                }

                $total = $movements->sum('credit_amount');
                $iva = round($total - ($total / (1 + self::IVA_RATE)), 2);

                $incomePolicy->update([
                    'policy_number' => $data['policy_number'],
                    'policy_type' => $data['policy_type'],
                    'concept' => $data['concept'],
                    'amount' => $total,
                    'iva_amount' => $iva,
                    'start_date' => $data['start_date'],
                    'end_date' => $data['end_date'],
                    'observations' => $data['observations'] ?? null,
                ]);
                $incomePolicy->details()->delete();

                BankMovement::whereIn('id', $data['movement_ids'])
                    ->update(['income_policy_id' => $incomePolicy->id, 'is_used' => true]);

                return;
            }

            $incomePolicy->update([
                'policy_number' => $data['policy_number'],
                'policy_type' => $data['policy_type'],
                'concept' => $data['concept'],
                'amount' => collect($data['details'])->sum(fn ($detail) => (float) $detail['amount']),
                'iva_amount' => 0,
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
