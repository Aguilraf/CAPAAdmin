<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Requirement;
use App\Models\Empleado;
use App\Models\Setting;
use App\Models\Organismo;
use App\Models\Provider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Helpers\NumberHelper;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['requirement', 'elaboratedBy', 'formulatedBy', 'authorizedBy'])
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return Inertia::render('Payments/Index', [
            'payments' => $payments
        ]);
    }

    public function create(Request $request)
    {
        $requirementId = $request->query('requirement_id');
        $requirement = null;

        if ($requirementId) {
            $requirement = Requirement::with(['elaborator', 'manager', 'coordinator', 'director', 'items.employee', 'travelAllowance'])->find($requirementId);
            if ($requirement && ($requirement->type === 'viaticos' || $requirement->type === 'bomberos')) {
                $requirement->employee_breakdown = $requirement->items
                    ->groupBy('employee_id')
                    ->map(function ($items, $empId) {
                        $emp = $items->first()->employee;
                        return [
                            'employee_id' => $empId,
                            'full_name' => $emp ? "C. {$emp->nombre} {$emp->primer_apellido} {$emp->segundo_apellido}" : 'N/A',
                            'total' => $items->sum('amount'),
                        ];
                    })->values();
            }
        }

        $employees = Empleado::activos()->select('id', 'nombre', 'primer_apellido', 'segundo_apellido', 'puesto')->get();
        // Add full_name attribute if needed for select
        foreach ($employees as $emp) {
            $emp->full_name = "{$emp->nombre} {$emp->primer_apellido} {$emp->segundo_apellido}";
        }

        $organismos = Organismo::all();
        $providers = Provider::where('active', true)->orderBy('name')->get();

        // Default signatories
        // Elaboro: C. Elio Tec Pat (DEPTO. REC. FINANCIEROS)
        // Formulo: PLC. Mariano Cervantes Sanchez (SUBGERENTE ADMINISTRATIVO)
        // Autoriza: C. Luis Daniel Heredia Duarte (GERENTE)

        $defaultElaboro = Empleado::where('puesto', 'LIKE', '%REC%FINANCIEROS%')->first();
        $defaultFormulo = Empleado::where('puesto', 'LIKE', '%SUBGERENTE ADMINISTRATIVO%')->first();
        $defaultAutoriza = Empleado::where('es_gerente', true)->first();

        // Find Subgerente Comercial for Bomberos defaults
        $subgerente = Empleado::where('puesto', 'LIKE', '%SUBGERENTE COMERCIAL%')
            ->where('activo', true)
            ->orderByDesc('id')
            ->first();

        // Pending requirements for selection if not passed
        $pendingRequirements = Requirement::with(['items.employee', 'travelAllowance'])
            ->where('status', 'pending')
            ->orderBy('year', 'desc')
            ->orderBy('requirement_number', 'desc')
            ->get()
            ->map(function ($req) {
                return [
                    'id' => $req->id,
                    'label' => "{$req->formatted_number} - " . substr($req->description, 0, 50) . "..." . " ($" . number_format($req->total, 2) . ")",
                    'total' => $req->total,
                    'description' => $req->description,
                    'type' => $req->type,
                    'requirement_number' => $req->requirement_number,
                    'year' => $req->year,
                    'month_billed' => $req->month_billed,
                    'year_billed' => $req->year_billed,
                    'month_charged' => $req->month_charged,
                    'year_charged' => $req->year_charged,
                    'start_date' => $req->start_date ? $req->start_date->format('Y-m-d') : null,
                    'end_date' => $req->end_date ? $req->end_date->format('Y-m-d') : null,
                    'employee_ids' => $req->items->pluck('employee_id')->unique()->filter()->values(),
                    'employee_breakdown' => $req->items->groupBy('employee_id')->map(function ($items, $empId) {
                        $emp = $items->first()->employee;
                        return [
                            'employee_id' => $empId,
                            'full_name' => $emp ? "C. {$emp->nombre} {$emp->primer_apellido} {$emp->segundo_apellido}" : 'N/A',
                            'total' => $items->sum('amount'),
                        ];
                    })->values(),
                    'justification' => $req->travelAllowance?->justification,
                ];
            });

        return Inertia::render('Payments/Create', [
            'requirement' => $requirement,
            'employees' => $employees,
            'organismos' => $organismos,
            'pendingRequirements' => $pendingRequirements,
            'providers' => $providers,
            'subgerente' => $subgerente,
            'defaultSignatories' => [
                'elaborated_by_id' => $defaultElaboro ? $defaultElaboro->id : null,
                'formulated_by_id' => $defaultFormulo ? $defaultFormulo->id : null,
                'authorized_by_id' => $defaultAutoriza ? $defaultAutoriza->id : null,
            ]
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'payments' => 'required|array|min:1',
            'payments.*.organismo_id' => 'required|exists:organismos,id',
            'payments.*.payment_date' => 'required|date',
            'payments.*.beneficiary_type' => 'required|string',
            'payments.*.beneficiary_id' => 'nullable|integer',
            'payments.*.beneficiary' => 'required|string',
            'payments.*.amount' => 'required|numeric',
            'payments.*.amount_letters' => 'required|string',
            'payments.*.requirement_id' => 'nullable|exists:requirements,id',
            'payments.*.concept' => 'required|string',
            'payments.*.payment_type' => 'required|in:transferencia,cheque',
            'payments.*.reference' => 'required|string',
            'payments.*.elaborated_by_id' => 'nullable|exists:empleados,id',
            'payments.*.formulated_by_id' => 'nullable|exists:empleados,id',
            'payments.*.authorized_by_id' => 'nullable|exists:empleados,id',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->payments as $paymentData) {
                $payment = Payment::create($paymentData);

                if ($payment->requirement_id) {
                    Requirement::where('id', $payment->requirement_id)->update(['status' => 'paid']);
                }
            }
        });

        return redirect()->route('payments.index')->with('success', 'Pago(s) registrado(s) exitosamente.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['requirement', 'elaboratedBy', 'formulatedBy', 'authorizedBy', 'organismo']);

        return Inertia::render('Payments/Show', [
            'payment' => $payment
        ]);
    }

    public function downloadPdf(Payment $payment)
    {
        $payment->load(['requirement', 'elaboratedBy', 'formulatedBy', 'authorizedBy', 'organismo']);

        // Process images for DomPDF
        $rawSettings = Setting::pluck('value', 'key')->toArray();
        $settings = [];
        foreach ($rawSettings as $key => $value) {
            if (in_array($key, ['logo_qroo', 'logo_unidos', 'logo_capa_header', 'logo_capa', 'footer_imagen']) && $value) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($value)) {
                    $path = \Illuminate\Support\Facades\Storage::disk('public')->path($value);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $settings[$key] = $base64;
                } else {
                    $settings[$key] = $value;
                }
            } else {
                $settings[$key] = $value;
            }
        }

        // Add formatted date
        $fecha = \Carbon\Carbon::parse($payment->payment_date);
        $fecha_formateada = $fecha->day . ' de ' . $fecha->translatedFormat('F') . ' ' . $fecha->year;

        $pdf = Pdf::loadView('reports.payment_receipt', [
            'payment' => $payment,
            'settings' => $settings,
            'fecha_formateada' => $fecha_formateada,
        ])->setPaper('letter', 'portrait');

        $filename = 'Recibo_Pago_' . $payment->reference . '.pdf';
        return $pdf->download($filename);
    }

    public function downloadRequirementPaymentsPdf(Requirement $requirement)
    {
        $payments = Payment::where('requirement_id', $requirement->id)
            ->with(['requirement', 'elaboratedBy', 'formulatedBy', 'authorizedBy', 'organismo'])
            ->get();

        if ($payments->isEmpty()) {
            abort(404, 'No se encontraron pagos para este requerimiento.');
        }

        // Process images for DomPDF
        $rawSettings = Setting::pluck('value', 'key')->toArray();
        $settings = [];
        foreach ($rawSettings as $key => $value) {
            if (in_array($key, ['logo_qroo', 'logo_unidos', 'logo_capa_header', 'logo_capa', 'footer_imagen']) && $value) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($value)) {
                    $path = \Illuminate\Support\Facades\Storage::disk('public')->path($value);
                    $type = pathinfo($path, PATHINFO_EXTENSION);
                    $data = file_get_contents($path);
                    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                    $settings[$key] = $base64;
                } else {
                    $settings[$key] = $value;
                }
            } else {
                $settings[$key] = $value;
            }
        }

        $processedPayments = $payments->map(function ($payment) {
            $fecha = \Carbon\Carbon::parse($payment->payment_date);
            $payment->fecha_formateada = $fecha->day . ' de ' . $fecha->translatedFormat('F') . ' ' . $fecha->year;
            return $payment;
        });

        $pdf = Pdf::loadView('reports.bulk_payment_receipts', [
            'payments' => $processedPayments,
            'settings' => $settings,
        ])->setPaper('letter', 'portrait');

        $filename = 'Recibos_Pagos_' . str_replace('/', '-', $requirement->formatted_number) . '.pdf';
        return $pdf->download($filename);
    }
}
