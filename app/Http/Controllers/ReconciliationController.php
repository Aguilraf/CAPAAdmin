<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\DailyIncome;
use App\Models\IncomePolicy;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReconciliationController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->query('month', date('Y-m'));
        $policyId = $request->query('policy_id');
        $dailyIncomeId = $request->query('daily_income_id');
        $withoutIncome = $request->boolean('without_income');

        // 1. Obtener las Pólizas de Ingresos de este mes
        $policies = IncomePolicy::whereRaw("DATE_FORMAT(start_date, '%Y-%m') = ?", [$month])
            ->orderBy('policy_number')
            ->get();

        // 2. Obtener estadísticas del mes para las facturas
        $totalInvoices = Invoice::whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$month])->count();
        $reconciledInvoices = Invoice::whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$month])->where('is_used', true)->count();
        $pendingInvoices = Invoice::whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$month])->where('is_used', false)->count();

        $selectedPolicy = null;
        $dailyIncomes = [];
        $selectedDailyIncome = null;
        $invoicesList = [];

        // 3. Si hay una póliza seleccionada, cargar los días de cobranza dentro de su rango
        if ($policyId) {
            $selectedPolicy = IncomePolicy::find($policyId);
            if ($selectedPolicy) {
                $dailyIncomes = DailyIncome::whereBetween('income_date', [
                    $selectedPolicy->start_date->toDateString(),
                    $selectedPolicy->end_date->toDateString()
                ])
                ->orderBy('income_date', 'asc')
                ->get();
            }
        }

        // 4. Si hay un día de cobranza seleccionado, cargar las facturas correspondientes (fecha >= fecha_cobranza)
        if ($dailyIncomeId && !$withoutIncome) {
            $selectedDailyIncome = DailyIncome::find($dailyIncomeId);
            if ($selectedDailyIncome) {
                $invoicesList = Invoice::where(function ($query) use ($selectedDailyIncome, $policyId) {
                    $query->where('fecha', '>=', $selectedDailyIncome->income_date)
                        ->orWhere('income_policy_id', $policyId);
                })
                    ->where(function($query) use ($dailyIncomeId, $policyId) {
                        $query->where('is_used', false)
                              ->orWhere('daily_income_id', $dailyIncomeId)
                              ->orWhere('income_policy_id', $policyId);
                    })
                    ->orderBy('fecha', 'asc')
                    ->get();
            }
        }

        // 5. Si se selecciona "Sin día a comprobar", cargar las facturas libres de este mes
        if ($withoutIncome) {
            $invoicesList = Invoice::whereRaw("DATE_FORMAT(fecha, '%Y-%m') = ?", [$month])
                ->where(function($query) {
                    $query->where('is_used', false)
                          ->orWhere('is_reconciled_without_income', true);
                })
                ->orderBy('fecha', 'asc')
                ->get();
        }

        return Inertia::render('Reconciliation/Index', [
            'policies' => $policies,
            'selectedPolicy' => $selectedPolicy,
            'dailyIncomes' => $dailyIncomes,
            'selectedDailyIncome' => $selectedDailyIncome,
            'invoicesList' => $invoicesList,
            'stats' => [
                'total' => $totalInvoices,
                'reconciled' => $reconciledInvoices,
                'pending' => $pendingInvoices,
            ],
            'filters' => [
                'month' => $month,
                'policy_id' => $policyId,
                'daily_income_id' => $dailyIncomeId,
                'without_income' => $withoutIncome,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'daily_income_id' => 'required_without:without_income|nullable|integer',
            'without_income' => 'required_without:daily_income_id|boolean',
            'invoices' => 'array',
            'policy_id' => 'nullable|integer|exists:income_policies,id',
            'save_as_policy' => 'boolean',
        ]);

        $invoiceIds = collect($request->input('invoices', []))->map(fn ($id) => (int) $id);

        \DB::transaction(function() use ($validated, $invoiceIds) {
            if (!empty($validated['without_income'])) {
                // --- SIN DIA A COMPROBAR ---
                // Liberar las que estaban marcadas como sin comprobar de este mes si ya no se mandaron
                Invoice::where('is_reconciled_without_income', true)
                    ->whereNotIn('id', $invoiceIds->all())
                    ->update(['is_used' => false, 'is_reconciled_without_income' => false]);

                // Asignar las nuevas seleccionadas
                if ($invoiceIds->isNotEmpty()) {
                    Invoice::whereIn('id', $invoiceIds->all())->update([
                        'is_used' => true,
                        'is_reconciled_without_income' => true,
                        'daily_income_id' => null
                    ]);
                }
            } else {
                // --- CON CONCILIACION DE COBRANZA ---
                $dayId = $validated['daily_income_id'];

                if (!empty($validated['policy_id'])) {
                    $invoiceIds = $invoiceIds->merge(
                        Invoice::where('income_policy_id', $validated['policy_id'])->pluck('id')
                    )->unique()->values();
                }

                // Liberar las que estaban conciliadas antes con este día pero que ya no se mandaron
                Invoice::where('daily_income_id', $dayId)
                    ->whereNotIn('id', $invoiceIds->all())
                    ->update(['is_used' => false, 'daily_income_id' => null]);

                // Asignar las nuevas seleccionadas
                if ($invoiceIds->isNotEmpty()) {
                    $invoiceUpdate = [
                        'is_used' => true,
                        'daily_income_id' => $dayId,
                        'is_reconciled_without_income' => false
                    ];

                    if (!empty($validated['save_as_policy']) && !empty($validated['policy_id'])) {
                        $invoiceUpdate['income_policy_id'] = $validated['policy_id'];
                    }

                    Invoice::whereIn('id', $invoiceIds->all())->update($invoiceUpdate);
                }
            }
        });

        return redirect()->back()->with('success', 'Conciliación guardada exitosamente.');
    }
}
