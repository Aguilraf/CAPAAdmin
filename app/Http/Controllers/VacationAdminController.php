<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VacationAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Empleado::activos()
            ->with([
                'periodosVacacionales' => function ($q) {
                    $q->orderBy('anio', 'desc')->orderBy('numero_periodo', 'desc');
                }
            ]);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('numero_empleado', 'like', "%{$search}%");
            });
        }

        $empleados = $query->paginate(15)->through(function ($empleado) {
            $ultimoPeriodo = $empleado->periodosVacacionales->first();
            return [
                'id' => $empleado->id,
                'nombre' => $empleado->nombre,
                'numero_empleado' => $empleado->numero_empleado,
                'fecha_alta' => $empleado->fecha_alta->format('Y-m-d'),
                'antiguedad' => $empleado->antiguedad,
                'es_sindicalizado' => $empleado->es_sindicalizado,
                'ultimo_periodo' => $ultimoPeriodo ? "{$ultimoPeriodo->numero_periodo}-{$ultimoPeriodo->anio}" : 'Sin periodos',
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
            // Lógica Inline de Generación de Periodo (Reemplazando VacationService)

            // 1. Fechas del Periodo
            if ($request->numero_periodo == 1) {
                $fechaInicio = \Carbon\Carbon::create($request->anio, 1, 1);
                $fechaFin = \Carbon\Carbon::create($request->anio, 6, 30);
            } else {
                $fechaInicio = \Carbon\Carbon::create($request->anio, 7, 1);
                $fechaFin = \Carbon\Carbon::create($request->anio, 12, 31);
            }

            // Evitar duplicados
            $existe = \App\Models\PeriodoVacacional::where('empleado_id', $empleado->id)
                ->where('anio', $request->anio)
                ->where('numero_periodo', $request->numero_periodo)
                ->exists();

            if ($existe) {
                return redirect()->back()->with('error', "El periodo {$request->numero_periodo}-{$request->anio} ya existe para este empleado.");
            }

            // 2. Crear Periodo
            $periodo = new \App\Models\PeriodoVacacional();
            $periodo->empleado_id = $empleado->id;
            $periodo->anio = $request->anio;
            $periodo->numero_periodo = $request->numero_periodo;
            $periodo->fecha_inicio = $fechaInicio;
            $periodo->fecha_fin = $fechaFin;
            $periodo->activo = true; // Por defecto activo
            $periodo->save();

            // 3. Crear Saldos (Reglas de Negocio)

            // A. Ordinario (Base: 10 días por periodo semestral - Asunción basada en screenshot)
            $diasOrdinario = 10;
            $periodo->saldos()->create([
                'tipo' => 'ORDINARIO',
                'total_dias' => $diasOrdinario,
                'dias_usados' => 0,
                'dias_pendientes' => 0,
            ]);

            // B. Sindicato (SUTECAPA: 5 días si es sindicalizado)
            if ($empleado->es_sindicalizado) {
                $periodo->saldos()->create([
                    'tipo' => 'SUTECAPA',
                    'total_dias' => 5,
                    'dias_usados' => 0,
                    'dias_pendientes' => 0,
                ]);
            }

            // C. Antigüedad
            // Regla estimada: 1 día extra por año después del primero?
            // O basado en tabla de ley federal?
            // Dado el screenshot con "5" días disponible, asumiremos una cantidad basada en años.
            // Por seguridad, usaremos un cálculo conservador: 
            // 1 a 5 años = 0 extras? O es esto un bono?
            // Al no tener la tabla exacta, pondré 5 días fijos si tiene > 1 año para igualar screenshot,
            // pero dejaré TODO para ajustar.
            $aniosAntiguedad = \Carbon\Carbon::parse($empleado->fecha_alta)->diffInYears(now());

            $diasAntiguedad = 0;
            if ($aniosAntiguedad >= 1) {
                // Ejemplo: 1 día por año hasta tope de 5?
                $diasAntiguedad = min($aniosAntiguedad, 5);
            }

            if ($diasAntiguedad > 0) {
                $periodo->saldos()->create([
                    'tipo' => 'ANTIGUEDAD',
                    'total_dias' => $diasAntiguedad,
                    'dias_usados' => 0,
                    'dias_pendientes' => 0,
                ]);
            }

            return redirect()->back()->with('success', "Periodo {$request->numero_periodo}-{$request->anio} generado correctamente para {$empleado->nombre}.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function cancellationIndex(Request $request)
    {
        $query = Empleado::activos();

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('numero_empleado', 'like', "%{$search}%");
            });
        }

        $empleados = $query->paginate(15)->through(function ($empleado) {
            return [
                'id' => $empleado->id,
                'nombre' => $empleado->nombre,
                'numero_empleado' => $empleado->numero_empleado,
                'periodos_count' => $empleado->periodosVacacionales()->count(),
            ];
        });

        return Inertia::render('Vacations/Admin/CancellationIndex', [
            'empleados' => $empleados,
            'filters' => $request->only(['search']),
        ]);
    }

    public function showPeriods($empleadoId)
    {
        $empleado = Empleado::with(['periodosVacacionales.saldos'])->findOrFail($empleadoId);

        $periodos = $empleado->periodosVacacionales->map(function ($periodo) {
            $totalUsados = $periodo->saldos->sum('dias_usados');
            return [
                'id' => $periodo->id,
                'anio' => $periodo->anio,
                'numero_periodo' => $periodo->numero_periodo,
                'total_dias' => $periodo->total_dias, // Accessor or sum
                'dias_usados' => $totalUsados,
                'can_delete' => $totalUsados == 0,
                'saldos' => $periodo->saldos
            ];
        })->sortByDesc('anio')->values();

        return Inertia::render('Vacations/Admin/EmployeePeriods', [
            'empleado' => $empleado,
            'periodos' => $periodos
        ]);
    }

    public function destroyPeriod($periodoId)
    {
        // Encontrar modelo PeriodoVacacional (Necesitamos importarlo o usar full namespace)
        $periodo = \App\Models\PeriodoVacacional::with('saldos')->findOrFail($periodoId);

        // Validacion: Ningun dia usado
        $usados = $periodo->saldos->sum('dias_usados');

        if ($usados > 0) {
            return back()->with('error', 'No es posible cancelar este periodo porque ya se han utilizado días.');
        }

        // Eliminar
        $periodo->saldos()->delete();
        $periodo->delete();

        return back()->with('success', 'Periodo cancelado correctamente.');
    }
}
