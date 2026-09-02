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
        $dailyIncomes = collect();
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
                ->with([
                    'details.movement.bank',
                    'invoices' => fn ($query) => $query->where('income_policy_id', $policyId),
                ])
                ->orderBy('income_date', 'asc')
                ->get();
            }
        }

        $dailyIncomeSummaries = $dailyIncomes;
        $dailyIncomes = $dailyIncomes->filter(fn ($income) => $income->invoices->isEmpty())->values();

        // 4. Si hay un día de cobranza seleccionado, cargar las facturas correspondientes (fecha >= fecha_cobranza)
        if ($dailyIncomeId && !$withoutIncome) {
            $selectedDailyIncome = DailyIncome::find($dailyIncomeId);
            if ($selectedDailyIncome) {
                $invoicesList = Invoice::where('fecha', '>=', $selectedDailyIncome->income_date)
                    ->where(function($query) use ($dailyIncomeId) {
                        $query->where('is_used', false)
                              ->orWhere('daily_income_id', $dailyIncomeId);
                    })
                    ->orderByRaw("CASE WHEN numero_factura IS NULL OR numero_factura = '' THEN 1 ELSE 0 END")
                    ->orderBy('numero_factura', 'asc')
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
                ->orderByRaw("CASE WHEN numero_factura IS NULL OR numero_factura = '' THEN 1 ELSE 0 END")
                ->orderBy('numero_factura', 'asc')
                ->orderBy('fecha', 'asc')
                ->get();
        }

        return Inertia::render('Reconciliation/Index', [
            'policies' => $policies,
            'selectedPolicy' => $selectedPolicy,
            'dailyIncomes' => $dailyIncomes,
            'dailyIncomeSummaries' => $dailyIncomeSummaries ?? [],
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

    public function unlinkPolicy(Request $request, $dailyIncomeId)
    {
        $validated = $request->validate([
            'policy_id' => 'required|integer|exists:income_policies,id',
        ]);

        Invoice::where('daily_income_id', $dailyIncomeId)
            ->where('income_policy_id', $validated['policy_id'])
            ->update(['income_policy_id' => null]);

        return redirect()->back()->with('success', 'La relación entre la póliza y la cobranza fue eliminada.');
    }
}
