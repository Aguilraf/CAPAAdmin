<?php

namespace App\Http\Controllers;

use App\Models\SolicitudVacaciones;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class VacationRequestAdminController extends Controller
{
    public function index(Request $request)
    {
        $requests = SolicitudVacaciones::where('estado', 'PENDIENTE')
            ->orderBy('created_at', 'asc')
            ->with(['empleado'])
            ->get(); // Get collection first to use load()

        // Manually eager load using morphWith for polymorphic relation
        $requests->load([
            'detalles.origen' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\SaldoVacaciones::class => ['periodo'],
                    \App\Models\BonoEvaluacion::class => [],
                ]);
            }
        ]);

        if ($request->has('search')) {
            $search = $request->input('search');
            $requests = $requests->filter(function ($solicitud) use ($search) {
                return str_contains(strtolower($solicitud->empleado->nombre), strtolower($search)) ||
                    str_contains($solicitud->empleado->numero_empleado, $search);
            });
        }

        // Manual pagination for collection
        $page = $request->input('page', 1);
        $perPage = 15;
        $items = $requests->forPage($page, $perPage)->values();

        $paginatedRequests = new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $requests->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('Vacations/Admin/Requests', [
            'requests' => $paginatedRequests,
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
                'regex:/^\d+(\sBIS)?$/' // Solo números, opcionalmente espacio y BIS
            ],
        ], [
            'numero_oficio.regex' => 'El número de oficio solo debe contener números (ej. 101) o terminar en BIS con espacio (ej. 101 BIS).'
        ]);

        // Validar unicidad del oficio en el año actual
        $conflictingDetail = \App\Models\DetalleSolicitud::where('numero_oficio', $request->numero_oficio)
            ->whereHas('solicitud', function ($q) {
                $q->whereYear('fecha_aprobacion', now()->year);
            })
            ->with(['solicitud.empleado'])
            ->first();

        if ($conflictingDetail) {
            $empleado = $conflictingDetail->solicitud->empleado ? $conflictingDetail->solicitud->empleado->nombre : 'Usuario desconocido';
            $fecha = $conflictingDetail->solicitud->fecha_aprobacion ? \Carbon\Carbon::parse($conflictingDetail->solicitud->fecha_aprobacion)->format('d/m/Y') : 'Fecha desconocida';

            return back()->withErrors([
                'numero_oficio' => "Este número de oficio ya fue utilizado por {$empleado} en la solicitud aprobada el {$fecha}."
            ]);
        }

        $solicitud = SolicitudVacaciones::with('detalles.origen')->findOrFail($id);

        if ($solicitud->estado !== 'PENDIENTE') {
            return back()->with('error', 'La solicitud no está pendiente.');
        }

        DB::transaction(function () use ($solicitud, $request) {
            // Actualizar saldos
            foreach ($solicitud->detalles as $detalle) {
                $saldo = $detalle->origen; // SaldoVacaciones
                if ($saldo instanceof \App\Models\SaldoVacaciones) {
                    $saldo->dias_pendientes -= $detalle->dias_tomados;
                    $saldo->dias_usados += $detalle->dias_tomados;
                    $saldo->save();
                } elseif ($saldo instanceof \App\Models\BonoEvaluacion) {
                    $saldo->dias_usados += $detalle->dias_tomados;
                    $saldo->save();
                }

                // Guardar número de oficio
                $detalle->numero_oficio = $request->numero_oficio;
                $detalle->save();
            }

            // Actualizar estado
            $solicitud->estado = 'APROBADA';
            $solicitud->aprobado_por = auth()->id();
            $solicitud->fecha_aprobacion = now();
            $solicitud->save();
        });

        return back()->with('success', 'Solicitud aprobada correctamente con oficio ' . $request->numero_oficio);
    }

    public function reject($id)
    {
        $solicitud = SolicitudVacaciones::with('detalles.origen')->findOrFail($id);

        if ($solicitud->estado !== 'PENDIENTE') {
            return back()->with('error', 'La solicitud no está pendiente.');
        }

        DB::transaction(function () use ($solicitud) {
            // Revertir saldos pendientes
            foreach ($solicitud->detalles as $detalle) {
                $saldo = $detalle->origen; // SaldoVacaciones
                if ($saldo) {
                    $saldo->dias_pendientes -= $detalle->dias_tomados;
                    // No sumamos a usados porque se rechaza
                    $saldo->save();
                }
            }

            // Actualizar estado
            $solicitud->estado = 'RECHAZADA';
            $solicitud->save();
        });

        return back()->with('success', 'Solicitud rechazada correctamente.');
    }
}
