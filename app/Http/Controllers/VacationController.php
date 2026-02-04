<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SolicitudVacaciones;
use App\Models\Entitlement; // New Unified Model

class VacationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->empleado) {
            return redirect()->back()->with('error', 'No tienes un empleado asociado.');
        }

        $empleado = $user->empleado;

        // Verify tenure (6 months)
        $antiguedadMeses = Carbon::parse($empleado->fecha_alta)->diffInMonths(now());
        $tieneDerecho = $antiguedadMeses >= 6;

        // 1. Fetch Entitlements (Unified Balances)
        $entitlements = Entitlement::where('empleado_id', $empleado->id)
            ->where('status', 'ACTIVE') // or check dates
            ->get();

        // 2. Separate into "Periodos" (Ordinary/Tenure/Union) and "Bonos" for frontend compatibility
        $periodos = $entitlements->filter(fn($e) => $e->type !== 'BONO_CUATRIMESTRAL')
            ->groupBy(fn($e) => $e->year . '-' . ($e->meta['period_number'] ?? 0)) // Group by Year-Period
            ->map(function ($group) {
                $first = $group->first();
                return [
                    'anio' => $first->year,
                    'numero_periodo' => $first->meta['period_number'] ?? 0, // Using meta for period #
                    'fecha_inicio' => $first->valid_from ? $first->valid_from->format('Y-m-d') : null,
                    'fecha_fin' => $first->valid_until ? $first->valid_until->format('Y-m-d') : null,
                    'saldos_desglosados' => $group->map(function ($e) {
                        return [
                            'id' => $e->id,
                            'tipo' => $e->type, // ORDINARIO, ANTIGUEDAD
                            'total' => $e->total_days,
                            'usados' => $e->used_days,
                            'pendientes' => $e->pending_days,
                            'disponibles' => $e->total_days - $e->used_days - $e->pending_days,
                        ];
                    })->values(),
                    'activo' => true
                ];
            })->values();

        // 3. Format Bonos
        $bonos = $entitlements->filter(fn($e) => $e->type === 'BONO_CUATRIMESTRAL')
            ->map(function ($e) {
                return [
                    'id' => $e->id,
                    'anio' => $e->year,
                    'cuatrimestre' => $e->meta['cuatrimestre'] ?? 0,
                    'total' => $e->total_days,
                    'usados' => $e->used_days,
                    'disponibles' => $e->total_days - $e->used_days,
                    'expira' => $e->valid_until ? $e->valid_until->format('d/m/Y') : 'N/A',
                ];
            })->values();

        // 4. Fetch Requests
        $solicitudes = $empleado->solicitudesVacaciones()
            ->orderBy('created_at', 'desc')
            ->with(['entitlements']) // New relationship
            ->get();

        $solicitudesFormatted = $solicitudes->map(function ($solicitud) {
            $solicitudArray = $solicitud->toArray();

            // Derive "Type" string from the first attached entitlement
            if ($solicitud->entitlements->count() > 0) {
                $first = $solicitud->entitlements->first();
                if ($first->type === 'BONO_CUATRIMESTRAL') {
                    $cuatrimestre = $first->meta['cuatrimestre'] ?? '?';
                    $ordinal = match ($cuatrimestre) {
                        1 => '1er', 2 => '2do', 3 => '3er', default => $cuatrimestre . '°'
                    };
                    $solicitudArray['tipo_solicitud'] = "BONO {$ordinal} Cuatrimestre {$first->year}";
                } else {
                    $periodNum = $first->meta['period_number'] ?? '?';
                    $solicitudArray['tipo_solicitud'] = "VACACIONES Periodo {$periodNum} - {$first->year}";
                }
            } else {
                // Fallback if no details
                $solicitudArray['tipo_solicitud'] = $solicitud->tipo_solicitud; // e.g. VACACION
            }

            return $solicitudArray;
        });

        return Inertia::render('Vacations/Index', [
            'periodos' => $periodos,
            'solicitudes' => $solicitudesFormatted,
            'bonos' => $bonos,
            'isSindicalizado' => $empleado->es_sindicalizado,
            'canAccessVacations' => $tieneDerecho,
            'tenureMessage' => $tieneDerecho ? '' : 'Aún no cumples con la antigüedad mínima de 6 meses para gozar de vacaciones.'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'tipo_solicitud' => 'required|string',
            'motivo' => 'nullable|string',
        ]);

        $user = Auth::user();
        $empleado = $user->empleado;

        // Tenure Check
        if (Carbon::parse($empleado->fecha_alta)->diffInMonths(now()) < 6) {
            return back()->withErrors(['error' => 'No cumples con la antigüedad mínima de 6 meses.']);
        }

        try {
            return DB::transaction(function () use ($request, $empleado) {
                $start = Carbon::parse($request->fecha_inicio);
                $end = Carbon::parse($request->fecha_fin);

                if ($start->isWeekend())
                    return back()->withErrors(['fecha_inicio' => 'Inicio en fin de semana.']);
                if ($end->isWeekend())
                    return back()->withErrors(['fecha_fin' => 'Fin en fin de semana.']);

                // Updated Overlap Check
                $overlap = SolicitudVacaciones::where('empleado_id', $empleado->id)
                    ->whereIn('estado', ['PENDIENTE', 'APROBADA'])
                    ->where(function ($q) use ($start, $end) {
                        $q->whereBetween('fecha_inicio', [$start, $end])
                            ->orWhereBetween('fecha_fin', [$start, $end])
                            ->orWhere(function ($sq) use ($start, $end) {
                                $sq->where('fecha_inicio', '<=', $start)
                                    ->where('fecha_fin', '>=', $end);
                            });
                    })
                    ->exists();

                if ($overlap)
                    return back()->withErrors(['fecha_inicio' => 'Solapamiento de fechas.']);

                // Calc Days
                $diasSolicitados = 0;
                $temp = $start->copy();
                while ($temp->lte($end)) {
                    if (!$temp->isWeekend())
                        $diasSolicitados++;
                    $temp->addDay();
                }

                if ($diasSolicitados <= 0)
                    return back()->withErrors(['dias' => 'Sin días hábiles.']);

                // FETCH ENTITLEMENTS needed
                $query = Entitlement::where('empleado_id', $empleado->id)
                    ->where('status', 'ACTIVE')
                    ->whereRaw('(total_days - used_days) > 0'); // Only valid

                if ($request->tipo_solicitud === 'BONO_CUATRIMESTRAL') {
                    $query->where('type', 'BONO_CUATRIMESTRAL')
                        ->orderBy('valid_until', 'asc'); // Expiring first
                } else {
                    $query->where('type', '!=', 'BONO_CUATRIMESTRAL')
                        // Sort: Year ASC, then Type Priority
                        ->orderBy('year', 'asc')
                        ->orderByRaw("CASE type WHEN 'ORDINARIO' THEN 1 WHEN 'ANTIGUEDAD' THEN 2 ELSE 3 END");
                }

                $availableEntitlements = $query->get();
                $totalAvailable = $availableEntitlements->sum(fn($e) => $e->total_days - $e->used_days - $e->pending_days);

                if ($diasSolicitados > $totalAvailable) {
                    return back()->withErrors(['dias' => "Insuficiente saldo. Solicitas $diasSolicitados, tienes $totalAvailable."]);
                }

                // 1. Create Request
                $solicitud = SolicitudVacaciones::create([
                    'empleado_id' => $empleado->id,
                    'tipo_solicitud' => $request->tipo_solicitud,
                    'fecha_inicio' => $start->format('Y-m-d'),
                    'fecha_fin' => $end->format('Y-m-d'),
                    'dias_solicitados' => $diasSolicitados,
                    'motivo' => $request->motivo,
                    'estado' => 'PENDIENTE',
                ]);

                // 2. Consume Entitlements
                $diasPorAsignar = $diasSolicitados;
                foreach ($availableEntitlements as $entitlement) {
                    if ($diasPorAsignar <= 0)
                        break;

                    $available = $entitlement->total_days - $entitlement->used_days - $entitlement->pending_days;
                    if ($available <= 0)
                        continue;

                    $take = min($available, $diasPorAsignar);

                    // Attach to Pivot
                    $solicitud->entitlements()->attach($entitlement->id, ['days_taken' => $take]);

                    // Update Pending
                    $entitlement->pending_days += $take;
                    $entitlement->save();

                    $diasPorAsignar -= $take;
                }

                return redirect()->route('vacations.index')->with('success', 'Solicitud creada correctamente.');
            });
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
