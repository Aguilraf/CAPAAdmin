<?php

namespace App\Http\Controllers;

use App\Models\BankMovement;
use App\Models\DailyIncome;
use App\Models\DailyIncomeDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DailyIncomeController extends Controller
{
    public function index()
    {
        $dailyIncomes = DailyIncome::with([
            'details.movement.bank',
        ])
            ->orderBy('income_date', 'desc')
            ->get();

        return Inertia::render('DailyIncomes/Index', [
            'dailyIncomes' => $dailyIncomes,
        ]);
    }

    public function create()
    {
        return Inertia::render('DailyIncomes/Create');
    }

    public function checkDate(Request $request)
    {
        $date = $request->query('date');

        if (!$date) {
            return response()->json(['exists' => false]);
        }

        return response()->json([
            'exists' => DailyIncome::whereDate('income_date', $date)->exists(),
        ]);
    }

    public function getMovements(Request $request)
    {
        $date = Carbon::parse($request->date);
        $endDate = $date->copy()->addDays(5)->toDateString();

        $query = BankMovement::with('bank')
            ->where('is_used', false)
            ->whereNotExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('daily_income_details')
                    ->whereColumn('daily_income_details.bank_movement_id', 'bank_movements.id');
            })
            ->where('credit_amount', '>', 0)
            ->whereRaw("UPPER(description) NOT LIKE '%ABONO POR LIQUIDACION DE INTERESES%'")
            ->whereRaw("UPPER(description) NOT LIKE '%PAGO DE INTERES NOMINAL%'")
            ->where(function ($q) use ($date, $endDate) {
                // Todos los movimientos anteriores o iguales a la fecha de cobranza.
                $q->where('operation_date', '<=', $date->toDateString())
                  // Y solo los depósitos en efectivo desde la fecha hasta 5 días después.
                  ->orWhere(function ($subQuery) use ($date, $endDate) {
                      $subQuery->where('description', 'like', '%DEPOSITO%EFECTIVO%')
                          ->whereBetween('operation_date', [
                              $date->toDateString(),
                              $endDate,
                          ]);
                  });
            });

        return response()->json($query
            ->orderByRaw("CASE
                WHEN bank_id IN (SELECT id FROM banks WHERE name LIKE '%AZTECA%') THEN 1
                WHEN bank_id IN (SELECT id FROM banks WHERE name LIKE '%HSBC%') THEN 2
                WHEN bank_id IN (SELECT id FROM banks WHERE name LIKE '%BBVA%') THEN 3
                ELSE 4
            END ASC")
            ->orderByRaw("CASE WHEN bank_id IN (SELECT id FROM banks WHERE name LIKE '%BBVA%') THEN CAST(reference AS UNSIGNED) ELSE CAST(movement_number AS UNSIGNED) END ASC")
            ->orderBy('operation_date', 'asc')
            ->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date' => 'required|date',
            'movements' => 'required|array|min:1',
            'has_draef' => 'boolean',
            'draef_amount' => 'nullable|required_if:has_draef,1|numeric|min:0.01',
        ]);

        if (DailyIncome::whereDate('income_date', $data['date'])->exists()) {
            return redirect()->back()->withErrors([
                'date' => 'Ya existe una cobranza registrada para este día.',
            ])->withInput();
        }

        return DB::transaction(function () use ($data) {
            $requestedMovementIds = array_values(array_unique($data['movements']));
            $movements = BankMovement::whereIn('id', $requestedMovementIds)
                ->where('is_used', false)
                ->whereRaw("UPPER(description) NOT LIKE '%ABONO POR LIQUIDACION DE INTERESES%'")
                ->whereRaw("UPPER(description) NOT LIKE '%PAGO DE INTERES NOMINAL%'")
                ->whereNotExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('daily_income_details')
                        ->whereColumn('daily_income_details.bank_movement_id', 'bank_movements.id');
                })
                ->lockForUpdate()
                ->get();

            if ($movements->count() !== count($requestedMovementIds)) {
                throw ValidationException::withMessages([
                    'movements' => 'Uno o más movimientos ya fueron utilizados en otra cobranza.',
                ]);
            }

            $draefAmount = !empty($data['has_draef']) ? (float) $data['draef_amount'] : 0;
            $totalAmount = $movements->sum('credit_amount') + $draefAmount;

            $dailyIncome = DailyIncome::create([
                'income_date' => $data['date'],
                'total_amount' => $totalAmount,
                'total_movements' => $movements->count(),
                'draef_amount' => $draefAmount,
            ]);

            foreach ($movements as $movement) {
                DailyIncomeDetail::create([
                    'daily_income_id' => $dailyIncome->id,
                    'bank_movement_id' => $movement->id,
                ]);

                $movement->update(['is_used' => true]);
            }

            return redirect()->route('daily-incomes.index')->with('success', 'Ingreso registrado correctamente.');
        });
    }

    public function removeMovement(DailyIncome $dailyIncome, DailyIncomeDetail $dailyIncomeDetail)
    {
        return DB::transaction(function () use ($dailyIncome, $dailyIncomeDetail) {
            $movement = $dailyIncomeDetail->movement;

            if ($movement) {
                $movement->update(['is_used' => false]);
            }

            $dailyIncomeDetail->delete();

            $dailyIncome->refresh();

            if ($dailyIncome->details()->count() === 0) {
                $dailyIncome->delete();
                return redirect()->route('daily-incomes.index')->with('success', 'Se eliminó la cobranza del día porque ya no quedó ningún movimiento asociado.');
            }

            $dailyIncome->update([
                'total_amount' => $dailyIncome->details()->with('movement')->get()->sum(fn ($detail) => (float) ($detail->movement?->credit_amount ?? 0)) + (float) $dailyIncome->draef_amount,
                'total_movements' => $dailyIncome->details()->count(),
            ]);

            return redirect()->route('daily-incomes.index')->with('success', 'Movimiento quitado y liberado para otra cobranza.');
        });
    }

    public function destroy(DailyIncome $dailyIncome)
    {
        return DB::transaction(function () use ($dailyIncome) {
            foreach ($dailyIncome->details as $detail) {
                if ($detail->movement) {
                    $detail->movement->update(['is_used' => false]);
                }
            }

            $dailyIncome->details()->delete();
            $dailyIncome->delete();

            return redirect()->route('daily-incomes.index')->with('success', 'Cobranza del día eliminada y movimientos liberados.');
        });
    }
}
