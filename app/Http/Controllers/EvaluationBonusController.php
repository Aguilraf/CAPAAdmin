<?php

namespace App\Http\Controllers;

use App\Models\Entitlement;
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
        // We look for an entitlement of type BONO_CUATRIMESTRAL for that Year and Cuatrimestre
        $existing = Entitlement::where('empleado_id', $empleado->id)
            ->where('type', 'BONO_CUATRIMESTRAL')
            ->where('year', $targetPeriod['anio'])
            ->where('meta->cuatrimestre', $targetPeriod['cuatrimestre'])
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

        // Calculate valid_until (expiration)
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        if ($currentMonth >= 1 && $currentMonth < 5) {
            $validUntil = Carbon::create($currentYear, 5, 31);
        } elseif ($currentMonth >= 5 && $currentMonth < 9) {
            $validUntil = Carbon::create($currentYear, 9, 30);
        } else {
            $validUntil = Carbon::create($currentYear + 1, 1, 31);
        }

        // Create BonoEvaluacion record (Historical record)
        \App\Models\BonoEvaluacion::create([
            'empleado_id' => $empleado->id,
            'anio' => $request->anio,
            'cuatrimestre' => $request->cuatrimestre,
            'calificacion' => $diasPagados, // Using dias_pagados as score/value
            'dias_otorgados' => $diasOtorgados,
            'fecha_expiracion' => $validUntil,
        ]);

        // Create the Entitlement record (Balance for consumption)
        Entitlement::create([
            'empleado_id' => $empleado->id,
            'year' => $request->anio,
            'type' => 'BONO_CUATRIMESTRAL',
            'description' => "Bono {$request->cuatrimestre}er Cuatrimestre {$request->anio}",
            'total_days' => $diasOtorgados,
            'used_days' => 0,
            'pending_days' => 0,
            'valid_from' => now(), // Valid from registration
            'valid_until' => $validUntil,
            'status' => 'ACTIVE',
            'meta' => [
                'cuatrimestre' => (int) $request->cuatrimestre,
                'calificacion' => $diasPagados
            ],
        ]);

        return response()->json([
            'success' => true,
            'message' => "Bono registrado: {$diasOtorgados} día(s) de descanso otorgado(s).",
            'dias_otorgados' => $diasOtorgados,
        ]);
    }

    private function calculateTargetPeriod()
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        if ($currentMonth >= 2 && $currentMonth < 5) {
            return [
                'anio' => $currentYear - 1,
                'cuatrimestre' => 3,
                'periodo_nombre' => 'Sep-Dic ' . ($currentYear - 1),
            ];
        } elseif ($currentMonth >= 5 && $currentMonth < 9) {
            return [
                'anio' => $currentYear,
                'cuatrimestre' => 1,
                'periodo_nombre' => 'Ene-Abr ' . $currentYear,
            ];
        } elseif ($currentMonth >= 9) {
            return [
                'anio' => $currentYear,
                'cuatrimestre' => 2,
                'periodo_nombre' => 'May-Ago ' . $currentYear,
            ];
        }

        return null;
    }
}
