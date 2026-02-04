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

    public function generatePeriod(Request $request)
    {
        $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'anio' => 'required|integer|min:2020|max:2030',
            'numero_periodo' => 'required|in:1,2',
        ]);

        $empleado = Empleado::findOrFail($request->empleado_id);

        try {
            DB::transaction(function () use ($request, $empleado) {
                // Check if already exists (Check for ORDINARIO of this period)
                $exists = Entitlement::where('empleado_id', $empleado->id)
                    ->where('year', $request->anio)
                    ->where('type', 'ORDINARIO')
                    ->where('meta->period_number', $request->numero_periodo)
                    ->exists();

                if ($exists) {
                    throw new \Exception("El periodo {$request->numero_periodo}-{$request->anio} ya existe.");
                }

                // Period Dates
                if ($request->numero_periodo == 1) {
                    $validFrom = Carbon::create($request->anio, 1, 1);
                    $validUntil = Carbon::create($request->anio, 6, 30);
                } else {
                    $validFrom = Carbon::create($request->anio, 7, 1);
                    $validUntil = Carbon::create($request->anio, 12, 31);
                }

                // 1. ORDINARIO (10 days)
                Entitlement::create([
                    'empleado_id' => $empleado->id,
                    'year' => $request->anio,
                    'type' => 'ORDINARIO',
                    'description' => "Periodo {$request->numero_periodo} - {$request->anio}",
                    'total_days' => 10,
                    'valid_from' => $validFrom,
                    'valid_until' => $validUntil, // Or longer validity? Keeping period dates for now.
                    'status' => 'ACTIVE',
                    'meta' => ['period_number' => (int) $request->numero_periodo]
                ]);

                // 2. SUTECAPA (5 days if unionized)
                if ($empleado->es_sindicalizado) {
                    Entitlement::create([
                        'empleado_id' => $empleado->id,
                        'year' => $request->anio,
                        'type' => 'SUTECAPA',
                        'description' => "SUTECAPA Periodo {$request->numero_periodo} - {$request->anio}",
                        'total_days' => 5,
                        'valid_from' => $validFrom,
                        'valid_until' => $validUntil,
                        'status' => 'ACTIVE',
                        'meta' => ['period_number' => (int) $request->numero_periodo]
                    ]);
                }

                // 3. ANTIGUEDAD
                // Assuming logic: 1 day per year > 1, max 5? (Logic from prev controller check)
                // For now, simple assumption based on prev code:
                $tenureYears = Carbon::parse($empleado->fecha_alta)->diffInYears(now());
                $diasAntiguedad = ($tenureYears >= 1) ? min($tenureYears, 5) : 0;

                if ($diasAntiguedad > 0) {
                    Entitlement::create([
                        'empleado_id' => $empleado->id,
                        'year' => $request->anio,
                        'type' => 'ANTIGUEDAD',
                        'description' => "Antigüedad Periodo {$request->numero_periodo} - {$request->anio}",
                        'total_days' => $diasAntiguedad,
                        'valid_from' => $validFrom,
                        'valid_until' => $validUntil,
                        'status' => 'ACTIVE',
                        'meta' => ['period_number' => (int) $request->numero_periodo]
                    ]);
                }
            });

            return redirect()->back()->with('success', "Periodo {$request->numero_periodo}-{$request->anio} generado correctamente.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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
                'id' => $first->id, // Use ID of first entitlement as representative ID for deletion? No, deletion needs custom logic.
                'anio' => $first->year,
                'numero_periodo' => $first->meta['period_number'] ?? 0,
                'total_dias' => $group->sum('total_days'),
                'dias_usados' => $group->sum('used_days'),
                'can_delete' => $group->sum('used_days') == 0,
                'saldos_desglosados' => $group->map(function ($e) {
                    return [
                        'id' => $e->id,
                        'tipo' => $e->type,
                        'total' => $e->total_days,
                        'usados' => $e->used_days
                    ];
                })
            ];
        })->values();

        return Inertia::render('Vacations/Admin/EmployeePeriods', [
            'empleado' => $empleado,
            'periodos' => $periodos
        ]);
    }

    public function destroyPeriod($empleadoId, $anio, $numeroPeriodo)
    {
        // Delete all entitlements for this period
        // Note: The route might pass an ID, but since "Period" is now a concept, we need to pass Year/Number or delete by a representative ID.
        // If the frontend calls logic with ID, we might need to look up that entitlement and delete its group.
        // Let's assume we update the route to pass unique identifiers or we find the group via one ID.

        // Strategy: Frontend passes one ID (e.g. from ordinario). We find that entitlement, get year/num, and delete all matching.
        $entitlement = Entitlement::findOrFail($empleadoId); // Implicitly $empleadoId argument might be entitlement ID if route is /periods/{id}

        // Wait, standard CRUD passes ID. In showPeriods, we mapped 'id' => $first->id.
        // So $empleadoId here is actually $entitlementId
        $entitlementId = $empleadoId;

        $target = Entitlement::findOrFail($entitlementId);
        $anio = $target->year;
        $periodo = $target->meta['period_number'] ?? 0;

        // Validation
        $group = Entitlement::where('empleado_id', $target->empleado_id)
            ->where('year', $anio)
            ->where('meta->period_number', $periodo)
            ->get();

        if ($group->sum('used_days') > 0) {
            return back()->with('error', 'No se puede eliminar el periodo porque tiene días utilizados.');
        }

        foreach ($group as $e) {
            $e->delete();
        }

        return back()->with('success', 'Periodo eliminado correctamente.');
    }
}
