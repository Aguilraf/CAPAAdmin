<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Entitlement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VacationAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Empleado::activos();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('numero_empleado', 'like', "%{$search}%");
            });
        }

        // We need to fetch entitlements to show "Last Period"
        $empleados = $query->paginate(15)->through(function ($empleado) {
            // Find latest entitlement (ORDINARIO usually defines the period)
            $latest = Entitlement::where('empleado_id', $empleado->id)
                ->where('type', 'ORDINARIO')
                ->orderBy('year', 'desc')
                ->orderBy('meta->period_number', 'desc')
                ->first();

            $ultimoPeriodo = $latest ? "{$latest->meta['period_number']}-{$latest->year}" : 'Sin periodos';

            return [
                'id' => $empleado->id,
                'nombre' => $empleado->nombre,
                'numero_empleado' => $empleado->numero_empleado,
                'fecha_alta' => $empleado->fecha_alta ? $empleado->fecha_alta->format('Y-m-d') : 'N/A',
                'antiguedad' => $empleado->antiguedad, // Helper in Empleado model?
                'es_sindicalizado' => $empleado->es_sindicalizado,
                'ultimo_periodo' => $ultimoPeriodo,
            ];
        });

        return Inertia::render('Vacations/Admin/Index', [
            'empleados' => $empleados,
            'filters' => $request->only(['search']),
        ]);
    }

    // Individual Generation
    public function generatePeriod(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'anio' => 'required|integer|min:2020|max:2030',
            'numero_periodo' => 'required|in:1,2',
        ]);

        $empleado = Empleado::findOrFail($request->empleado_id);

        try {
            DB::transaction(function () use ($empleado, $request) {
                $this->generatePeriodForEmployee($empleado, $request->anio, $request->numero_periodo);
            });

            return redirect()->back()->with('success', "Periodo {$request->numero_periodo}-{$request->anio} generado correctamente.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Bulk Generation
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'anio' => 'required|integer|min:2020|max:2030',
            'numero_periodo' => 'required|in:1,2',
        ]);

        try {
            $count = 0;
            // Iterate all active employees
            // chunk to avoid memory limits if many employees
            Empleado::activos()->chunk(100, function ($empleados) use ($request, &$count) {
                foreach ($empleados as $empleado) {
                    try {
                        DB::transaction(function () use ($empleado, $request) {
                            $this->generatePeriodForEmployee($empleado, $request->anio, $request->numero_periodo);
                        });
                        $count++;
                    } catch (\Exception $e) {
                        // Log error for specific employee but continue?
                        // For now silent fail or check "exists" logic inside shared method
                    }
                }
            });

            return redirect()->back()->with('success', "Generación masiva completada. Procesados: {$count} empleados.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', "Error en generación masiva: " . $e->getMessage());
        }
    }

    // Shared Logic
    private function generatePeriodForEmployee($empleado, $year, $periodNumber)
    {
        // 0. Expiration Logic: Expire Previous Year's Period
        // If generating 1-2025 -> Expire 1-2024
        $prevYear = $year - 1;
        $prevPeriodEntitlements = Entitlement::where('empleado_id', $empleado->id)
            ->where('year', $prevYear)
            ->where('meta->period_number', (int) $periodNumber)
            ->where('status', 'ACTIVE')
            ->get();

        foreach ($prevPeriodEntitlements as $ent) {
            $ent->update([
                'status' => 'EXPIRED',
                'valid_until' => now(), // Close it now
                'pending_days' => 0 // Optional: remove any pending balance preventing usage
            ]);
        }

        // 1. Check if CURRENT period already exists
        $exists = Entitlement::where('empleado_id', $empleado->id)
            ->where('year', $year)
            ->where('type', 'ORDINARIO')
            ->where('meta->period_number', (int) $periodNumber)
            ->exists();

        if ($exists) {
            return; // Skip if exists, don't throw error for bulk to succeed
        }

        // Period Dates
        if ($periodNumber == 1) {
            $validFrom = Carbon::create($year, 1, 1);
            $validUntil = Carbon::create($year, 6, 30);
        } else {
            $validFrom = Carbon::create($year, 7, 1);
            $validUntil = Carbon::create($year, 12, 31);
        }

        // 2. ORDINARIO (10 days)
        Entitlement::create([
            'empleado_id' => $empleado->id,
            'year' => $year,
            'type' => 'ORDINARIO',
            'description' => "Periodo {$periodNumber} - {$year}",
            'total_days' => 10,
            'valid_from' => $validFrom,
            'valid_until' => $validUntil,
            'status' => 'ACTIVE',
            'meta' => ['period_number' => (int) $periodNumber]
        ]);

        // 3. SUTECAPA (5 days if unionized)
        if ($empleado->es_sindicalizado) {
            Entitlement::create([
                'empleado_id' => $empleado->id,
                'year' => $year,
                'type' => 'SUTECAPA',
                'description' => "SUTECAPA Periodo {$periodNumber} - {$year}",
                'total_days' => 5,
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'status' => 'ACTIVE',
                'meta' => ['period_number' => (int) $periodNumber]
            ]);
        }

        // 4. ANTIGUEDAD
        $tenureYears = Carbon::parse($empleado->fecha_alta)->diffInYears(now());
        $diasAntiguedad = ($tenureYears >= 1) ? min($tenureYears, 5) : 0;

        if ($diasAntiguedad > 0) {
            Entitlement::create([
                'empleado_id' => $empleado->id,
                'year' => $year,
                'type' => 'ANTIGUEDAD',
                'description' => "Antigüedad Periodo {$periodNumber} - {$year}",
                'total_days' => $diasAntiguedad,
                'valid_from' => $validFrom,
                'valid_until' => $validUntil,
                'status' => 'ACTIVE',
                'meta' => ['period_number' => (int) $periodNumber]
            ]);
        }
    }

    public function showPeriods($empleadoId)
    {
        $empleado = Empleado::findOrFail($empleadoId);

        // Fetch all non-bonus entitlements
        $entitlements = Entitlement::where('empleado_id', $empleadoId)
            ->where('type', '!=', 'BONO_CUATRIMESTRAL')
            ->orderBy('year', 'desc')
            ->get();

        // Group by Year-Period
        $periodos = $entitlements->groupBy(function ($e) {
            return $e->year . '-' . ($e->meta['period_number'] ?? 0);
        })->map(function ($group) {
            $first = $group->first();
            return [
                'id' => $first->id,
                'anio' => $first->year,
                'numero_periodo' => $first->meta['period_number'] ?? 0,
                'total_dias' => $group->sum('total_days'),
                'dias_usados' => $group->sum('used_days'),
                'status' => $first->status, // Show status
                'can_delete' => $group->sum('used_days') == 0,
                'saldos_desglosados' => $group->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'tipo' => $e->type,
                        'total' => $e->total_days,
                        'usados' => $e->used_days,
                        'status' => $e->status
                    ];
                })
            ];
        })->values();

        return Inertia::render('Vacations/Admin/EmployeePeriods', [
            'empleado' => $empleado,
            'periodos' => $periodos
        ]);
    }

    public function destroyPeriod($empleadoId)
    {
        // NOTE: Fix from previous conceptual confusion. 
        // Logic: Deleting one entitlement effectively deletes the whole period group if we fetch by group logic.
        // Or if we pass 'id' of one entitlement, we find its period and delete siblings.

        $target = Entitlement::findOrFail($empleadoId);

        if ($target->used_days > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar un periodo con días utilizados.');
        }

        $anio = $target->year;
        $periodo = $target->meta['period_number'] ?? 0;

        // Delete all entitlements for this User-Year-Period
        Entitlement::where('empleado_id', $target->empleado_id)
            ->where('year', $anio)
            ->where('meta->period_number', $periodo)
            ->delete();

        return redirect()->back()->with('success', 'Periodo eliminado correctamente.');
    }

    // Cancellation Index (Existing method, ensuring no changes needed or just check)
    public function cancellationIndex(Request $request)
    {
        $query = Empleado::query();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('numero_empleado', 'like', "%{$search}%");
            });
        }

        $employees = $query->paginate(15)->through(function ($empleado) {
            // Calculate active period count (groups)
            $count = Entitlement::where('empleado_id', $empleado->id)
                ->where('type', 'ORDINARIO') // Use one type to count groups
                ->count();

            return [
                'id' => $empleado->id,
                'nombre' => $empleado->nombre,
                'numero_empleado' => $empleado->numero_empleado,
                'periodos_count' => $count
            ];
        });

        return Inertia::render('Vacations/Admin/CancellationIndex', [
            'employees' => $employees,
            'filters' => $request->only(['search']),
        ]);
    }
}
