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
        $lastDate = DailyIncome::max('income_date');
        $nextDate = $lastDate ? Carbon::parse($lastDate)->addDay() : Carbon::today();

        while ($nextDate->isWeekend()) {
            $nextDate->addDay();
        }

        return Inertia::render('DailyIncomes/Create', [
            'nextDate' => $nextDate->toDateString(),
        ]);
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

    public function dniMovements(Request $request)
    {
        $movements = BankMovement::with('bank')
            ->whereNotNull('income_policy_id')
            ->where('is_visible', true)
            ->whereNotExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('daily_income_details')
                    ->whereColumn('daily_income_details.bank_movement_id', 'bank_movements.id');
            })
            ->orderBy('operation_date', 'asc')
            ->get();

        return response()->json($movements);
    }

    public function getMovements(Request $request)
    {
        $date = Carbon::parse($request->date);
        $endDate = $date->copy()->addDays(5)->toDateString();

        $query = BankMovement::with('bank')
            ->where('is_visible', true)
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
            'movements' => 'nullable|array',
            'dni_movements' => 'nullable|array',
            'has_draef' => 'boolean',
            'draef_subtotal' => 'nullable|required_if:has_draef,1|numeric|min:0.01',
            'draef_iva' => 'nullable|required_if:has_draef,1|numeric|min:0',
        ]);

        $movementIds = $data['movements'] ?? [];
        $dniMovementIds = $data['dni_movements'] ?? [];

        if (empty($movementIds) && empty($dniMovementIds)) {
            throw ValidationException::withMessages([
                'movements' => 'Debes seleccionar al menos un movimiento.',
            ]);
        }

        if (DailyIncome::whereDate('income_date', $data['date'])->exists()) {
            return redirect()->back()->withErrors([
                'date' => 'Ya existe una cobranza registrada para este día.',
            ])->withInput();
        }

        return DB::transaction(function () use ($data, $movementIds, $dniMovementIds) {
            $requestedMovementIds = array_values(array_unique($movementIds));
            $movements = collect();

            if (!empty($requestedMovementIds)) {
                $movements = BankMovement::whereIn('id', $requestedMovementIds)
                    ->where('is_visible', true)
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
            }

            $requestedDniIds = array_values(array_unique($dniMovementIds));
            $dniMovements = collect();

            if (!empty($requestedDniIds)) {
                $dniMovements = BankMovement::whereIn('id', $requestedDniIds)
                    ->whereNotNull('income_policy_id')
                    ->where('is_visible', true)
                    ->whereNotExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('daily_income_details')
                            ->whereColumn('daily_income_details.bank_movement_id', 'bank_movements.id');
                    })
                    ->lockForUpdate()
                    ->get();

                if ($dniMovements->count() !== count($requestedDniIds)) {
                    throw ValidationException::withMessages([
                        'dni_movements' => 'Uno o más pagos no identificados ya fueron utilizados.',
                    ]);
                }
            }

            $draefSubtotal = !empty($data['has_draef']) ? (float) $data['draef_subtotal'] : 0;
            $draefIva = !empty($data['has_draef']) ? (float) $data['draef_iva'] : 0;
            $draefAmount = $draefSubtotal + $draefIva;
            $dniAmount = $dniMovements->sum('credit_amount');
            $totalAmount = $movements->sum('credit_amount');
            $totalGeneral = $totalAmount + $dniAmount + $draefAmount;

            $dailyIncome = DailyIncome::create([
                'income_date' => $data['date'],
                'total_amount' => $totalAmount,
                'total_general' => $totalGeneral,
                'total_movements' => $movements->count() + $dniMovements->count(),
                'draef_amount' => $draefAmount,
                'draef_subtotal' => $draefSubtotal,
                'draef_iva' => $draefIva,
                'dni_amount' => $dniAmount,
            ]);

            foreach ($movements as $movement) {
                DailyIncomeDetail::create([
                    'daily_income_id' => $dailyIncome->id,
                    'bank_movement_id' => $movement->id,
                ]);

                $movement->update(['is_used' => true]);
            }

            foreach ($dniMovements as $movement) {
                DailyIncomeDetail::create([
                    'daily_income_id' => $dailyIncome->id,
                    'bank_movement_id' => $movement->id,
                    'is_dni' => true,
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

            if ($movement && !$dailyIncomeDetail->is_dni) {
                $movement->update(['is_used' => false]);
            }

            $dailyIncomeDetail->delete();

            $dailyIncome->refresh();

            if ($dailyIncome->details()->count() === 0) {
                $dailyIncome->delete();
                return redirect()->route('daily-incomes.index')->with('success', 'Se eliminó la cobranza del día porque ya no quedó ningún movimiento asociado.');
            }

            $dayAmount = $dailyIncome->details()->with('movement')->where('is_dni', false)->get()->sum(fn ($detail) => (float) ($detail->movement?->credit_amount ?? 0));
            $dniAmount = $dailyIncome->details()->with('movement')->where('is_dni', true)->get()->sum(fn ($detail) => (float) ($detail->movement?->credit_amount ?? 0));

            $dailyIncome->update([
                'total_amount' => $dayAmount,
                'total_general' => $dayAmount + $dniAmount + (float) $dailyIncome->draef_amount,
                'total_movements' => $dailyIncome->details()->count(),
                'dni_amount' => $dniAmount,
            ]);

            return redirect()->route('daily-incomes.index')->with('success', 'Movimiento quitado y liberado para otra cobranza.');
        });
    }

    public function destroy(DailyIncome $dailyIncome)
    {
        return DB::transaction(function () use ($dailyIncome) {
            foreach ($dailyIncome->details as $detail) {
                if ($detail->movement && !$detail->is_dni) {
                    $detail->movement->update(['is_used' => false]);
                }
            }

            $dailyIncome->details()->delete();
            $dailyIncome->delete();

            return redirect()->route('daily-incomes.index')->with('success', 'Cobranza del día eliminada y movimientos liberados.');
        });
    }
}
