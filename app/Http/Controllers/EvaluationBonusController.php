<?php

namespace App\Http\Controllers;

use App\Models\BonoEvaluacion;
use App\Models\Empleado;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationBonusController extends Controller
{
    /**
     * Check if the authenticated employee needs to register their quarterly bonus.
     */
    public function checkStatus(Request $request)
    {
        $user = Auth::user();

        if (!$user->empleado_id) {
            return response()->json(['pending' => false, 'message' => 'No employee linked']);
        }

        $empleado = Empleado::find($user->empleado_id);

        if (!$empleado || !$empleado->fecha_alta) {
            return response()->json(['pending' => false, 'message' => 'No hire date']);
        }

        // Check tenure: Must have > 9 months
        $tenureMonths = Carbon::parse($empleado->fecha_alta)->diffInMonths(Carbon::now());

        if ($tenureMonths <= 9) {
            return response()->json(['pending' => false, 'message' => 'Insufficient tenure']);
        }

        // Determine the "target bonus" period based on current date
        $targetPeriod = $this->calculateTargetPeriod();

        if (!$targetPeriod) {
            return response()->json(['pending' => false, 'message' => 'No payable period yet']);
        }

        // Check if already registered
        $existing = BonoEvaluacion::where('empleado_id', $empleado->id)
            ->where('anio', $targetPeriod['anio'])
            ->where('cuatrimestre', $targetPeriod['cuatrimestre'])
            ->first();

        if ($existing) {
            return response()->json(['pending' => false, 'message' => 'Already registered']);
        }

        // Needs to register
        return response()->json([
            'pending' => true,
            'periodo' => $targetPeriod,
        ]);
    }

    /**
     * Calculate which bonus period should be paid based on current date.
     * 
     * Logic:
     * - February (month 2): Pays for Sep-Dec of previous year (Q3)
     * - May (month 5): Pays for Jan-Apr of current year (Q1)
     * - September (month 9): Pays for May-Aug of current year (Q2)
     * 
     * If current date is BEFORE February, no bonus is payable yet (return null).
     * If between Feb-Apr: Target is Q3 of previous year.
     * If between May-Aug: Target is Q1 of current year.
     * If between Sep-Jan: Target is Q2 of current year.
     */
    private function calculateTargetPeriod()
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        if ($currentMonth >= 2 && $currentMonth < 5) {
            // Feb-Apr: Pay Q3 of previous year
            return [
                'anio' => $currentYear - 1,
                'cuatrimestre' => 3,
                'periodo_nombre' => 'Sep-Dic ' . ($currentYear - 1),
            ];
        } elseif ($currentMonth >= 5 && $currentMonth < 9) {
            // May-Aug: Pay Q1 of current year
            return [
                'anio' => $currentYear,
                'cuatrimestre' => 1,
                'periodo_nombre' => 'Ene-Abr ' . $currentYear,
            ];
        } elseif ($currentMonth >= 9) {
            // Sep-Dec: Pay Q2 of current year
            return [
                'anio' => $currentYear,
                'cuatrimestre' => 2,
                'periodo_nombre' => 'May-Ago ' . $currentYear,
            ];
        }

        // January: No bonus payable yet (Feb is the first payment month)
        return null;
    }

    /**
     * Store the employee's bonus declaration.
     */
    public function store(Request $request)
    {
        $request->validate([
            'dias_pagados' => 'required|in:0,5,10,15',
            'anio' => 'required|integer',
            'cuatrimestre' => 'required|integer|between:1,3',
        ]);

        $user = Auth::user();
        $empleado = Empleado::find($user->empleado_id);

        if (!$empleado) {
            return response()->json(['error' => 'Employee not found'], 404);
        }

        // Calculate dias_otorgados based on dias_pagados
        $diasPagados = (int) $request->dias_pagados;
        $diasOtorgados = match ($diasPagados) {
            0 => 0,
            5 => 1,
            10 => 2,
            15 => 3,
            default => 0,
        };

        // Calculate expiration date: Last day of next expiration month (Jan, May, Sep)
        // Logic: Find the next occurrence of Jan 31, May 31, or Sep 30
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Determine next expiration month
        if ($currentMonth >= 1 && $currentMonth < 5) {
            // Jan-Apr: Expires May 31
            $fechaExpiracion = Carbon::create($currentYear, 5, 31);
        } elseif ($currentMonth >= 5 && $currentMonth < 9) {
            // May-Aug: Expires Sep 30
            $fechaExpiracion = Carbon::create($currentYear, 9, 30);
        } else {
            // Sep-Dec: Expires Jan 31 of next year
            $fechaExpiracion = Carbon::create($currentYear + 1, 1, 31);
        }

        // Create the bonus record
        BonoEvaluacion::create([
            'empleado_id' => $empleado->id,
            'anio' => $request->anio,
            'cuatrimestre' => $request->cuatrimestre,
            'calificacion' => $diasPagados, // Store the "dias pagados" as the score
            'dias_otorgados' => $diasOtorgados,
            'dias_usados' => 0,
            'fecha_expiracion' => $fechaExpiracion,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Bono registrado: {$diasOtorgados} día(s) de descanso otorgado(s).",
            'dias_otorgados' => $diasOtorgados,
        ]);
    }
}
