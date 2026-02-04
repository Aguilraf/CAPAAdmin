<?php

namespace App\Http\Controllers;

use App\Models\SolicitudVacaciones;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;

class VacationPdfController extends Controller
{
    public function downloadRequest(SolicitudVacaciones $solicitud)
    {
        // Verificar que la solicitud pertenezca al usuario autenticado (o sea admin)
        $user = Auth::user();
        if ($user->empleado->id !== $solicitud->empleado_id && !$user->can('administrar vacaciones')) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        $empleado = $solicitud->empleado;

        // Intentar obtener el año del periodo asociado si existiera lógica, por defecto el año actual
        $periodo_anio = now()->year;

        // Obtener el Gerente activo (Solo debe haber uno)
        $gerente = \App\Models\Empleado::where('activo', true)
            ->where('es_gerente', true)
            ->first();

        // Si no hay gerente definido, usar valores genéricos o el jefe inmediato como fallback
        if (!$gerente && $empleado->jefe_inmediato) {
            // Lógica de fallback si es necesaria, o dejar null
        }

        // Obtener detalles y agruparlos por Periodo/Tipo
        $grupos = [];

        // Cargar detalles con su origen (SaldoVacaciones -> Periodo)
        $solicitud->load('detalles.origen.periodo');

        // Ordenar detalles para consumir los periodos más antiguos primero (Lógica FIFO visual)
        $detalles = $solicitud->detalles->sortBy(function ($detalle) {
            // Protección contra nulos si la relación polimórfica falla o no existe
            $saldo = $detalle->origen;

            // Fallback manual si eager loading falló (por namespace corto en BD)
            if (!$saldo && ($detalle->origen_tipo === 'SaldoVacaciones' || $detalle->origen_tipo === 'App\\Models\\SaldoVacaciones')) {
                $saldo = \App\Models\SaldoVacaciones::find($detalle->origen_id);
            }

            return ($saldo && $saldo->periodo) ? $saldo->periodo->anio : 9999;
        });

        foreach ($detalles as $detalle) {
            if ($detalle->origen_tipo === 'SaldoVacaciones' || $detalle->origen_tipo === 'App\\Models\\SaldoVacaciones') {
                $saldo = $detalle->origen; // Ya cargado por la relación polimórfica o accesores si estuvieran definidos
                // Nota: Laravel a veces no carga polimórficos automáticos en strings custom, aseguramos carga manual si falla
                if (!$saldo) {
                    $saldo = \App\Models\SaldoVacaciones::find($detalle->origen_id);
                }

                if ($saldo) {
                    $key = $saldo->id; // Agrupar por ID de saldo (mismo tipo y año)
                    if (!isset($grupos[$key])) {
                        $grupos[$key] = [
                            'tipo' => $saldo->tipo, // ORDINARIO, ANTIGUEDAD
                            'anio' => $saldo->periodo ? $saldo->periodo->anio : $periodo_anio,
                            'dias' => 0,
                            'detalles' => []
                        ];
                    }
                    $grupos[$key]['dias'] += $detalle->dias_tomados;
                }
            } elseif ($detalle->origen_tipo === 'App\Models\BonoEvaluacion') {
                // Handle Bonus Evaluation
                $bono = $detalle->origen;
                if (!$bono) {
                    $bono = \App\Models\BonoEvaluacion::find($detalle->origen_id);
                }

                if ($bono) {
                    $key = 'bono_' . $bono->id;
                    if (!isset($grupos[$key])) {
                        $grupos[$key] = [
                            'tipo' => 'BONO CUATRIMESTRAL',
                            'anio' => $bono->anio,
                            'cuatrimestre' => $bono->cuatrimestre,
                            'dias' => 0,
                            'detalles' => []
                        ];
                    }
                    $grupos[$key]['dias'] += $detalle->dias_tomados;
                }
            }
        }

        // Si no hay detalles (caso legacy o error), crear un grupo dummy con toda la solicitud
        if (empty($grupos)) {
            $grupos[] = [
                'tipo' => 'VACACIONES',
                'anio' => $periodo_anio,
                'dias' => $solicitud->dias_solicitados,
                'fecha_inicio' => \Carbon\Carbon::parse($solicitud->fecha_inicio)->isoFormat('D [DE] MMMM [DEL] YYYY'),
                'fecha_fin' => \Carbon\Carbon::parse($solicitud->fecha_fin)->isoFormat('D [DE] MMMM [DEL] YYYY'),
                'fecha_retorno' => \Carbon\Carbon::parse($solicitud->fecha_fin)->addDay()->isoFormat('D [DE] MMMM [DEL] YYYY')
            ];
        } else {
            // Calcular fechas para cada grupo
            $currentDate = \Carbon\Carbon::parse($solicitud->fecha_inicio);

            foreach ($grupos as &$grupo) {
                $diasRestantes = $grupo['dias'];
                $grupoInicio = $currentDate->copy();

                // Avanzar fecha fin basado en días hábiles (saltando Sábados y Domingos)
                // El primer día cuenta, así que avanzamos ($diasRestantes - 1)
                $daysAdded = 0;
                $tempDate = $grupoInicio->copy();

                // Si el día de inicio es fin de semana, avanzar al lunes (aunque validaciones deberían prevenir esto)
                while ($tempDate->isWeekend()) {
                    $tempDate->addDay();
                    $grupoInicio = $tempDate->copy(); // Actualizar inicio real
                }

                // Loop para encontrar la fecha fin
                while ($daysAdded < $diasRestantes) {
                    // Si es el último día necesario, paramos aquí
                    if ($daysAdded == $diasRestantes - 1) {
                        break;
                    }

                    $tempDate->addDay();
                    // Si caímos en fin de semana, seguimos avanzando hasta lunes
                    while ($tempDate->isWeekend()) {
                        $tempDate->addDay();
                    }
                    $daysAdded++;
                }
                $grupoFin = $tempDate->copy();

                // Fecha retorno es el día siguiente a fin (o lunes si es viernes)
                $fechaRetorno = $grupoFin->copy()->addDay();
                while ($fechaRetorno->isWeekend()) {
                    $fechaRetorno->addDay();
                }

                // Guardar fechas formateadas
                $grupo['fecha_inicio'] = $grupoInicio->isoFormat('D [DE] MMMM [DEL] YYYY');
                $grupo['fecha_fin'] = $grupoFin->isoFormat('D [DE] MMMM [DEL] YYYY');
                $grupo['fecha_retorno'] = $fechaRetorno->isoFormat('D [DE] MMMM [DEL] YYYY');
                $grupo['dias_solicitados'] = $grupo['dias'];

                // Preparar currentDate para el siguiente grupo (día siguiente a este fin)
                $currentDate = $grupoFin->copy()->addDay();
                while ($currentDate->isWeekend()) {
                    $currentDate->addDay();
                }
            }
        }

        $pdf = Pdf::loadView('pdf.vacation-request', [
            'solicitud' => $solicitud, // Base para datos generales
            'empleado' => $empleado,
            'gerente' => $gerente,
            'subSolicitudes' => $grupos // Array de sub-solicitudes
        ]);

        return $pdf->download("Solicitud_Vacaciones_{$empleado->clave}_{$solicitud->id}.pdf");
    }
}
