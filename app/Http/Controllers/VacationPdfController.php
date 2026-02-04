<?php

namespace App\Http\Controllers;

use App\Models\SolicitudVacaciones;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class VacationPdfController extends Controller
{
    public function downloadRequest(SolicitudVacaciones $solicitud)
    {
        // Set Spanish locale for dates
        Carbon::setLocale('es');

        // Permission Check
        $user = Auth::user();
        if ($user->empleado->id !== $solicitud->empleado_id && !$user->can('administrar vacaciones')) {
            abort(403, 'No tienes permiso para ver este documento.');
        }

        $empleado = $solicitud->empleado;
        $periodo_anio = now()->year; // Default

        // Get Manager
        $gerente = \App\Models\Empleado::where('activo', true)
            ->where('es_gerente', true)
            ->first();

        // Group Entitlements by Period/Context
        $grupos = [];
        $solicitud->load('entitlements');

        // Sort entitlements to process oldest Periods first
        $entitlements = $solicitud->entitlements->sortBy(function ($e) {
            return $e->year . $e->meta['period_number'] ?? '99';
        });

        foreach ($entitlements as $entitlement) {
            $daysTaken = $entitlement->pivot->days_taken;

            if ($entitlement->type === 'BONO_CUATRIMESTRAL') {
                $key = 'bono_' . $entitlement->id;
                $grupos[$key] = [
                    'tipo' => 'BONO CUATRIMESTRAL',
                    'anio' => $entitlement->year,
                    'cuatrimestre' => $entitlement->meta['cuatrimestre'] ?? '?',
                    'dias' => $daysTaken,
                    'is_bonus' => true
                ];
            } else {
                // Regular Period (Ordinario, Antiguedad, Sutecapa)
                // Each TYPE gets its own page/group
                $periodNum = $entitlement->meta['period_number'] ?? 0;
                $key = $entitlement->year . '_' . $periodNum . '_' . $entitlement->type;

                if (!isset($grupos[$key])) {
                    $grupos[$key] = [
                        'tipo' => $entitlement->type,
                        'anio' => $entitlement->year,
                        'numero_periodo' => $periodNum,
                        'dias' => 0,
                        'is_bonus' => false
                    ];
                }
                $grupos[$key]['dias'] += $daysTaken;
            }
        }

        // If empty (shouldn't happen with valid data)
        if (empty($grupos)) {
            $grupos[] = [
                'tipo' => 'VACACIONES',
                'anio' => $periodo_anio,
                'dias' => $solicitud->dias_solicitados,
                'fecha_inicio' => Carbon::parse($solicitud->fecha_inicio)->isoFormat('D [DE] MMMM [DEL] YYYY'),
                'fecha_fin' => Carbon::parse($solicitud->fecha_fin)->isoFormat('D [DE] MMMM [DEL] YYYY'),
                'fecha_retorno' => Carbon::parse($solicitud->fecha_fin)->addDay()->isoFormat('D [DE] MMMM [DEL] YYYY')
            ];
        } else {
            // Calculate Dates for each Group
            $currentDate = Carbon::parse($solicitud->fecha_inicio);

            foreach ($grupos as &$grupo) {
                $diasRestantes = $grupo['dias'];
                $grupoInicio = $currentDate->copy();

                // Adjust start if weekend
                while ($grupoInicio->isWeekend()) {
                    $grupoInicio->addDay();
                }

                // Calculate End Date based on business days
                $daysAdded = 0;
                $tempDate = $grupoInicio->copy();

                // If 0 days (error?), skip
                if ($diasRestantes > 0) {
                    // First day counts
                    while ($daysAdded < $diasRestantes - 1) {
                        $tempDate->addDay();
                        while ($tempDate->isWeekend()) {
                            $tempDate->addDay();
                        }
                        $daysAdded++;
                    }
                }
                $grupoFin = $tempDate->copy();

                // Set Retorno
                $fechaRetorno = $grupoFin->copy()->addDay();
                while ($fechaRetorno->isWeekend()) {
                    $fechaRetorno->addDay();
                }

                // Update CurrentDate for next group (Start next day)
                $currentDate = $fechaRetorno->copy(); // Next block starts next business day

                // Format
                $grupo['fecha_inicio'] = $grupoInicio->isoFormat('D [DE] MMMM [DEL] YYYY');
                $grupo['fecha_fin'] = $grupoFin->isoFormat('D [DE] MMMM [DEL] YYYY');
                $grupo['fecha_retorno'] = $fechaRetorno->isoFormat('D [DE] MMMM [DEL] YYYY');

                // Label refinement
                if (!isset($grupo['display_type'])) {
                    $grupo['display_type'] = $grupo['tipo'];
                    if (isset($grupo['cuatrimestre'])) {
                        $grupo['display_type'] .= " (Cuatrimestre {$grupo['cuatrimestre']})";
                    }
                }
            }
        }

        $pdf = Pdf::loadView('pdf.vacation-request', [
            'solicitud' => $solicitud,
            'empleado' => $empleado,
            'gerente' => $gerente,
            'grupos' => $grupos
        ]);

        return $pdf->stream('solicitud-vacaciones.pdf');
    }
}
