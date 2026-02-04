<?php

namespace App\Http\Controllers;

use App\Models\SolicitudVacaciones;
use App\Models\Entitlement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VacationRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $query = SolicitudVacaciones::where('estado', 'PENDIENTE')
            ->orderBy('created_at', 'asc')
            ->with(['empleado', 'entitlements']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('empleado', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('numero_empleado', 'like', "%{$search}%");
            });
        }

        $requests = $query->paginate(15)->through(function ($solicitud) {
            $solicitudArray = $solicitud->toArray();

            // Format Type based on Entitlement Metadata
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
            }
            return $solicitudArray;
        });

        return Inertia::render('Vacations/Admin/Requests', [
            'requests' => $requests,
            'filters' => $request->only(['search']),
        ]);
    }

    public function approve(Request $request, $id)
    {
        $request->validate([
            'numero_oficio' => [
                'required',
                'string',
                'max:50',
                'regex:/^\d+(\sBIS)?$/'
            ],
        ], [
            'numero_oficio.regex' => 'El número de oficio solo debe contener números (ej. 101) o terminar en BIS con espacio (ej. 101 BIS).'
        ]);

        // Validate Uniqueness: Check request_entitlements pivot for same office number in Approved requests this year
        $conflict = DB::table('request_entitlements')
            ->join('solicitudes_vacaciones', 'request_entitlements.solicitud_id', '=', 'solicitudes_vacaciones.id')
            ->join('empleados', 'solicitudes_vacaciones.empleado_id', '=', 'empleados.id')
            ->where('request_entitlements.numero_oficio', $request->numero_oficio)
            ->whereYear('solicitudes_vacaciones.fecha_aprobacion', now()->year)
            ->where('solicitudes_vacaciones.estado', 'APROBADA')
            ->select('empleados.nombre as empleado_nombre', 'solicitudes_vacaciones.fecha_aprobacion')
            ->first();

        if ($conflict) {
            $fecha = Carbon::parse($conflict->fecha_aprobacion)->format('d/m/Y');
            return back()->withErrors([
                'numero_oficio' => "Este número de oficio ya fue utilizado por {$conflict->empleado_nombre} en la solicitud aprobada el {$fecha}."
            ]);
        }

        $solicitud = SolicitudVacaciones::with('entitlements')->findOrFail($id);

        if ($solicitud->estado !== 'PENDIENTE') {
            return back()->with('error', 'La solicitud no está pendiente.');
        }

        DB::transaction(function () use ($solicitud, $request) {
            // Update Entitlements (Convert Pending to Used)
            foreach ($solicitud->entitlements as $entitlement) {
                // Pivot data: days_taken
                $daysTaken = $entitlement->pivot->days_taken;

                $entitlement->pending_days -= $daysTaken;
                $entitlement->used_days += $daysTaken;
                $entitlement->save();

                // Save Office Number to pivot
                // We use DB update on pivot or syncWithoutDetaching
                $solicitud->entitlements()->updateExistingPivot($entitlement->id, [
                    'numero_oficio' => $request->numero_oficio
                ]);
            }

            // Update Request Status
            $solicitud->estado = 'APROBADA';
            $solicitud->aprobado_por = auth()->id();
            $solicitud->fecha_aprobacion = now();
            $solicitud->save();
        });

        return back()->with('success', 'Solicitud aprobada correctamente con oficio ' . $request->numero_oficio);
    }

    public function reject($id)
    {
        $solicitud = SolicitudVacaciones::with('entitlements')->findOrFail($id);

        if ($solicitud->estado !== 'PENDIENTE') {
            return back()->with('error', 'La solicitud no está pendiente.');
        }

        DB::transaction(function () use ($solicitud) {
            // Revert Pending Days in Entitlements
            foreach ($solicitud->entitlements as $entitlement) {
                $daysTaken = $entitlement->pivot->days_taken;

                $entitlement->pending_days -= $daysTaken;
                // No change to used_days or total_days
                $entitlement->save();
            }

            $solicitud->estado = 'RECHAZADA';
            $solicitud->save();
        });

        return back()->with('success', 'Solicitud rechazada y días liberados.');
    }
}
