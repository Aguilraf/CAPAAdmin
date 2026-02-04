<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\SolicitudVacaciones;
use App\Models\DetalleSolicitud;
use App\Models\SaldoVacaciones;
use App\Models\BonoEvaluacion;
use App\Models\PeriodoVacacional;

class VacationController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user->empleado) {
            return redirect()->back()->with('error', 'No tienes un empleado asociado.');
        }

        $empleado = $user->empleado;

        // --- FIX DATA CORRUPTION (TEMPORARY) ---
        // Corregir posibles registros con namespace roto por el error anterior
        try {
            DB::table('detalles_solicitud')
                ->where('origen_tipo', 'AppModelsSaldoVacaciones')
                ->update(['origen_tipo' => 'App\Models\SaldoVacaciones']);

            DB::table('detalles_solicitud')
                ->where('origen_tipo', 'AppModelsBonoEvaluacion')
                ->update(['origen_tipo' => 'App\Models\BonoEvaluacion']);
        } catch (\Exception $e) {
            // Ignorar errores si la tabla no existe o algo falla
        }
        // ---------------------------------------

        // Verificar antigüedad de 6 meses
        $antiguedadMeses = Carbon::parse($empleado->fecha_alta)->diffInMonths(now());
        $tieneDerecho = $antiguedadMeses >= 6;

        // Cargar periodos activos con sus saldos
        $periodos = $empleado->periodosVacacionales()
            ->where('activo', true)
            ->with([
                'saldos' => function ($q) {
                    // Opcional: filtrar saldos con dias disponibles > 0 si queremos limpiar la vista
                }
            ])
            ->get()
            ->map(function ($periodo) {
                // Sort saldos: ORDINARIO > ANTIGUEDAD > SUTECAPA
                $sortedSaldos = $periodo->saldos->sortBy(function ($saldo) {
                    $order = [
                        'ORDINARIO' => 1,
                        'ANTIGUEDAD' => 2,
                        'SUTECAPA' => 3,
                    ];
                    return $order[strtoupper($saldo->tipo)] ?? 99;
                });

                $periodo->saldos_desglosados = $sortedSaldos->map(function ($saldo) {
                    return [
                        'id' => $saldo->id,
                        'tipo' => $saldo->tipo,
                        'total' => $saldo->total_dias,
                        'usados' => $saldo->dias_usados,
                        'pendientes' => $saldo->dias_pendientes,
                        'disponibles' => $saldo->dias_disponibles,
                    ];
                })->values();

                return $periodo;
            })
            ->filter(function ($periodo) {
                // Filter out periods with 0 total available days
                $totalDisponibles = $periodo->saldos_desglosados->sum('disponibles');
                return $totalDisponibles > 0;
            })
            ->values();

        // Obtener historial de solicitudes
        $solicitudes = $empleado->solicitudesVacaciones()
            ->orderBy('created_at', 'desc')
            ->with(['detalles.origen'])
            ->get();

        // Manually eager load nested relationships for polymorphic types
        $solicitudes->load([
            'detalles.origen' => function ($morphTo) {
                $morphTo->morphWith([
                    SaldoVacaciones::class => ['periodo'],
                    BonoEvaluacion::class => [],
                ]);
            }
        ]);

        // Obtener bonos de evaluación disponibles (no expirados y con días disponibles)
        $bonos = BonoEvaluacion::where('empleado_id', $empleado->id)
            ->where('fecha_expiracion', '>=', now())
            ->where('dias_usados', '<', DB::raw('dias_otorgados'))
            ->get()
            ->map(function ($bono) {
                return [
                    'id' => $bono->id,
                    'anio' => $bono->anio,
                    'cuatrimestre' => $bono->cuatrimestre,
                    'total' => $bono->dias_otorgados,
                    'usados' => $bono->dias_usados,
                    'disponibles' => $bono->dias_otorgados - $bono->dias_usados,
                    'expira' => $bono->fecha_expiracion->format('d/m/Y'),
                ];
            });

        // Transform solicitudes to include periodo/cuatrimestre info
        $solicitudesFormatted = $solicitudes->map(function ($solicitud) {
            $solicitudArray = $solicitud->toArray();

            // Add periodo/cuatrimestre info from detalles
            if ($solicitud->detalles && $solicitud->detalles->count() > 0) {
                $primerDetalle = $solicitud->detalles->first();
                if ($primerDetalle && $primerDetalle->origen) {
                    $origen = $primerDetalle->origen;

                    // Check if it's a SaldoVacaciones with periodo
                    if ($origen instanceof SaldoVacaciones && $origen->periodo) {
                        $solicitudArray['tipo_solicitud'] = "VACACIONES Periodo {$origen->periodo->numero_periodo} - {$origen->periodo->anio}";
                    }
                    // Check if it's a BonoEvaluacion
                    elseif ($origen instanceof BonoEvaluacion) {
                        $ordinal = match ($origen->cuatrimestre) {
                            1 => '1er',
                            2 => '2do',
                            3 => '3er',
                            default => $origen->cuatrimestre . '°'
                        };
                        $solicitudArray['tipo_solicitud'] = "BONO {$ordinal} Cuatrimestre {$origen->anio}";
                    }
                }
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
            'tipo_solicitud' => 'required|string', // VACACION, ONOMASTICO, etc
            'motivo' => 'nullable|string',
        ]);

        $user = Auth::user();
        $empleado = $user->empleado;

        // Validacion backend de antigüedad
        $antiguedadMeses = Carbon::parse($empleado->fecha_alta)->diffInMonths(now());
        if ($antiguedadMeses < 6) {
            return back()->withErrors(['error' => 'No cumples con la antigüedad mínima de 6 meses.']);
        }

        try {
            $start = Carbon::parse($request->fecha_inicio);
            $end = Carbon::parse($request->fecha_fin);

            // Validate that start and end dates are not weekends
            if ($start->isWeekend()) {
                return back()->withErrors(['fecha_inicio' => 'La fecha de inicio no puede ser sábado o domingo.']);
            }

            if ($end->isWeekend()) {
                return back()->withErrors(['fecha_fin' => 'La fecha de fin no puede ser sábado o domingo.']);
            }

            // Validar solapamiento de fechas
            $overlap = SolicitudVacaciones::where('empleado_id', $empleado->id)
                ->whereIn('estado', ['PENDIENTE', 'APROBADA'])
                ->where(function ($query) use ($start, $end) {
                    $query->where(function ($q) use ($start, $end) {
                        $q->where('fecha_inicio', '<=', $end)
                            ->where('fecha_fin', '>=', $start);
                    });
                })
                ->exists();

            if ($overlap) {
                return back()->withErrors(['fecha_inicio' => 'Ya existe una solicitud activa en el rango de fechas seleccionado.']);
            }

            // 1. Calcular días hábiles (Business Days)
            $diasSolicitados = 0;
            $tempDate = $start->copy();
            while ($tempDate->lte($end)) {
                if (!$tempDate->isWeekend()) {
                    $diasSolicitados++;
                }
                $tempDate->addDay();
            }

            if ($diasSolicitados <= 0) {
                return back()->withErrors(['dias' => 'El rango de fechas no contiene días hábiles.']);
            }

            // SPECIAL CASE: BONO_CUATRIMESTRAL
            if ($request->tipo_solicitud === 'BONO_CUATRIMESTRAL') {
                // Get available bonuses (not expired, with available days)
                $bonos = BonoEvaluacion::where('empleado_id', $empleado->id)
                    ->where('fecha_expiracion', '>=', now())
                    ->where('dias_usados', '<', DB::raw('dias_otorgados'))
                    ->orderBy('fecha_expiracion', 'asc') // Use oldest first
                    ->get();

                $totalBonosDisponibles = $bonos->sum(function ($b) {
                    return $b->dias_otorgados - $b->dias_usados;
                });

                if ($totalBonosDisponibles == 0) {
                    return back()->withErrors(['fecha_inicio' => 'No tienes días de bono cuatrimestral disponibles. Verifica que tengas bonos activos y no expirados.']);
                }

                if ($diasSolicitados > $totalBonosDisponibles) {
                    $detallesBonos = $bonos->map(function ($b) {
                        $disponibles = $b->dias_otorgados - $b->dias_usados;
                        return "Cuatrimestre {$b->cuatrimestre}-{$b->anio}: {$disponibles} día(s)";
                    })->join(', ');

                    return back()->withErrors([
                        'fecha_inicio' => "No tienes suficientes días de bono cuatrimestral. Solicitas {$diasSolicitados} día(s), pero solo tienes {$totalBonosDisponibles} disponible(s). Detalle: {$detallesBonos}"
                    ]);
                }

                // Create the request
                $solicitud = SolicitudVacaciones::create([
                    'empleado_id' => $empleado->id,
                    'tipo_solicitud' => $request->tipo_solicitud,
                    'fecha_inicio' => $request->fecha_inicio,
                    'fecha_fin' => $request->fecha_fin,
                    'dias_solicitados' => $diasSolicitados,
                    'motivo' => $request->motivo,
                    'estado' => 'PENDIENTE',
                ]);

                // Deduct from bonuses and create details
                $diasPorAsignar = $diasSolicitados;
                foreach ($bonos as $bono) {
                    if ($diasPorAsignar <= 0)
                        break;

                    $disponible = $bono->dias_otorgados - $bono->dias_usados;
                    if ($disponible <= 0)
                        continue;

                    $tomar = min($disponible, $diasPorAsignar);

                    // Reserve days (mark as pending)
                    $bono->dias_usados += $tomar;
                    $bono->save();

                    // Create detail record
                    DetalleSolicitud::create([
                        'solicitud_id' => $solicitud->id,
                        'origen_tipo' => 'App\Models\BonoEvaluacion',
                        'origen_id' => $bono->id,
                        'dias_tomados' => $tomar,
                    ]);

                    $diasPorAsignar -= $tomar;
                }

                return redirect()->route('vacations.index')->with('success', 'Solicitud de Bono Cuatrimestral creada exitosamente.');
            }

            // 2. Obtener saldos disponibles (NORMAL VACATION LOGIC)
            $saldos = SaldoVacaciones::whereHas('periodo', function ($q) use ($empleado) {
                $q->where('empleado_id', $empleado->id)->where('activo', true);
            })
                ->with('periodo')
                ->get()
                ->sort(function ($a, $b) {
                    // 1. Prioridad por Año (Ascendente - más antiguo primero)
                    if ($a->periodo->anio != $b->periodo->anio) {
                        return $a->periodo->anio <=> $b->periodo->anio;
                    }

                    // 2. Prioridad por Tipo (ORDINARIO > ANTIGUEDAD > SUTECAPA)
                    $prioridad = [
                        'ORDINARIO' => 1,
                        'ANTIGUEDAD' => 2,
                        'SUTECAPA' => 3,
                    ];

                    $valA = $prioridad[strtoupper($a->tipo)] ?? 99;
                    $valB = $prioridad[strtoupper($b->tipo)] ?? 99;

                    return $valA <=> $valB;
                });

            // 3. Verificar suficiencia de saldo total
            $totalDisponible = $saldos->sum('dias_disponibles');
            if ($diasSolicitados > $totalDisponible) {
                return back()->withErrors(['dias' => "No tienes suficientes días disponibles. Solicitas $diasSolicitados, tienes $totalDisponible."]);
            }

            // 4. Crear Solicitud Única
            $solicitud = new SolicitudVacaciones();
            $solicitud->empleado_id = $empleado->id;
            $solicitud->tipo_solicitud = $request->tipo_solicitud;
            $solicitud->fecha_inicio = $start->format('Y-m-d');
            $solicitud->fecha_fin = $end->format('Y-m-d');
            $solicitud->dias_solicitados = $diasSolicitados;
            $solicitud->motivo = $request->motivo;
            $solicitud->estado = 'PENDIENTE';
            $solicitud->save();

            // 5. Distribuir días en Detalles
            $diasPorAsignar = $diasSolicitados;

            foreach ($saldos as $saldo) {
                if ($diasPorAsignar <= 0)
                    break;

                $disponible = $saldo->dias_disponibles;
                if ($disponible <= 0)
                    continue;

                $tomar = min($disponible, $diasPorAsignar);

                // Crear Detalle vinculándolo a la solicitud única
                $detalle = new DetalleSolicitud();
                $detalle->solicitud_id = $solicitud->id;
                $detalle->origen_tipo = 'App\Models\SaldoVacaciones';
                $detalle->origen_id = $saldo->id;
                $detalle->dias_tomados = $tomar;
                $detalle->save();

                $saldo->dias_pendientes += $tomar;
                $saldo->save();

                $diasPorAsignar -= $tomar;
            }

            return redirect()->route('vacations.index')->with('success', 'Solicitudes enviadas correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
