<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Empleado;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CommissionController extends Controller
{
    public function index()
    {
        $commissions = Commission::with(['empleado:id,nombre,primer_apellido,segundo_apellido,puesto_id', 'vehicle'])
            ->latest()
            ->paginate(15);
        return Inertia::render('Commissions/Index', [
            'commissions' => $commissions
        ]);
    }

    public function create()
    {
        $empleados = Empleado::activos()->orderBy('nombre')->get();
        $vehicles = \App\Models\Vehicle::where('active', true)->orderBy('inventory_number')->get();
        return Inertia::render('Commissions/Create', [
            'empleados' => $empleados,
            'vehicles' => $vehicles
        ]);
    }

    public function store(Request $request)
    {
        $year = $request->start_date ? date('Y', strtotime($request->start_date)) : date('Y');

        $validated = $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'oficio_number' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('commissions')->where(function ($query) use ($year) {
                    return $query->whereYear('start_date', $year);
                })
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ], [
            'oficio_number.unique' => 'El número de oficio ya existe para este año.',
            'end_date.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ]);

        Commission::create($validated);

        return redirect()->route('commissions.index')->with('message', 'Comisión creada exitosamente.');
    }

    public function edit(Commission $commission)
    {
        $empleados = Empleado::activos()->orderBy('nombre')->get();
        $vehicles = \App\Models\Vehicle::where('active', true)->orderBy('inventory_number')->get();
        return Inertia::render('Commissions/Edit', [
            'commission' => $commission,
            'empleados' => $empleados,
            'vehicles' => $vehicles
        ]);
    }

    public function update(Request $request, Commission $commission)
    {
        $year = $request->start_date ? date('Y', strtotime($request->start_date)) : date('Y');

        $validated = $request->validate([
            'empleado_id' => 'required|exists:empleados,id',
            'oficio_number' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('commissions')->where(function ($query) use ($year) {
                    return $query->whereYear('start_date', $year);
                })->ignore($commission->id)
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ], [
            'oficio_number.unique' => 'El número de oficio ya existe para este año.',
            'end_date.after_or_equal' => 'La fecha de fin no puede ser anterior a la fecha de inicio.',
        ]);

        $commission->update($validated);

        return redirect()->route('commissions.index')->with('message', 'Comisión actualizada exitosamente.');
    }

    public function destroy(Commission $commission)
    {
        $commission->delete();
        return redirect()->route('commissions.index')->with('message', 'Comisión eliminada exitosamente.');
    }

    public function printPdf(Commission $commission)
    {
        $commission->load(['empleado.puesto', 'vehicle']);
        $gerente = Empleado::where('es_gerente', true)->with('puesto')->first();
        $settings = \App\Models\Setting::pluck('value', 'key')->toArray();

        $settings = $this->processImagesForPdf($settings);

        $pdf = Pdf::loadView('reports.commission_oficio', compact('commission', 'gerente', 'settings'))
            ->setPaper('letter');

        return $pdf->stream("Comision_{$commission->oficio_number}.pdf");
    }

    private function processImagesForPdf($settings)
    {
        $imageKeys = ['logo_qroo', 'logo_unidos', 'logo_capa', 'logo_capa_header'];

        foreach ($imageKeys as $key) {
            if (isset($settings[$key]) && $settings[$key]) {
                $path = storage_path('app/public/' . $settings[$key]);
                if (file_exists($path)) {
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $settings[$key] = $base64;
                }
            }
        }

        return $settings;
    }
}
