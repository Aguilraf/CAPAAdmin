<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Capture;
use App\Models\FirefighterSetting; // Renamed
use Illuminate\Http\Request;

class CaptureController extends Controller
{
    public function index(Request $request)
    {
        $query = Capture::with(['community', 'firefighter']);

        if ($request->has('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        if ($request->has('requirement_number')) {
            $query->where('requirement_number', $request->requirement_number);
        }

        if ($request->boolean('pending_requirement')) {
            $query->whereNull('requirement_number');
        }

        return $query->orderBy('date', 'desc')->get();
    }

    public function store(Request $request)
    {

        // Validate basic fields first
        $validated = $request->validate([
            'date' => 'required|date',
            'year' => 'required|integer|min:2020|max:2030',
            'community_id' => 'required|exists:communities,id',
            'firefighter_id' => 'required|exists:firefighters,id',
            'subtotal' => 'required|numeric',
            'commission' => 'required|numeric',
            'total' => 'required|numeric',
            'rounding_commission' => 'required|numeric',
            'rounding_total' => 'required|numeric',
        ]);

        // Validate max rounding amount
        $firefighter = \App\Models\Firefighter::find($request->firefighter_id);
        $totalRounding = abs($request->rounding_total); // Validating absolute value of total rounding
        if ($firefighter->max_rounding_amount > 0 && $totalRounding > $firefighter->max_rounding_amount) {
            return response()->json([
                'message' => 'El monto del redondeo excede el límite autorizado para este bombero (' . $firefighter->max_rounding_amount . ').'
            ], 422);
        }

        $capture = Capture::create($validated);
        $capture->load(['community', 'firefighter']);

        return response()->json($capture, 201);
    }

    public function show(Capture $capture)
    {
        return $capture->load(['community', 'firefighter']);
    }

    public function update(Request $request, Capture $capture)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'year' => 'required|integer|min:2020|max:2030',
            'community_id' => 'required|exists:communities,id',
            'firefighter_id' => 'required|exists:firefighters,id',
            'subtotal' => 'required|numeric',
            'commission' => 'required|numeric',
            'total' => 'required|numeric',
            'rounding_commission' => 'required|numeric',
            'rounding_total' => 'required|numeric',
        ]);

        $capture->update($validated);

        return $capture;
    }

    public function destroy(Capture $capture)
    {
        $capture->delete();
        return response()->noContent();
    }

    public function assignRequirement(Request $request)
    {
        $request->validate([
            'capture_ids' => 'required|array',
            'capture_ids.*' => 'exists:captures,id',
            'requirement_number' => 'required|string',
            'year' => 'required|integer|min:2020|max:2030', // Validate year
        ]);

        $exists = Capture::where('year', $request->year)
            ->where('requirement_number', $request->requirement_number)
            ->whereNotIn('id', $request->capture_ids)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => "El número de requerimiento '{$request->requirement_number}' ya existe para el año {$request->year}."
            ], 422);
        }

        // Logic for Cancelación
        if ($request->fund_type === 'Cancelación') {
            $capturesToAssign = Capture::whereIn('id', $request->capture_ids)->get();
            $sumCommissions = $capturesToAssign->sum('commission');

            // Get Fund Amount from Settings
            $fundAmount = FirefighterSetting::where('key', 'report_fondo_amount')->value('value'); // Renamed
            $fundAmount = floatval($fundAmount);

            if ($fundAmount > 0) {
                $difference = $fundAmount - $sumCommissions;

                if ($difference > 0) {
                    // Create Balancing Record
                    // Need ID of "SIN NOMBRE" and "TRANSFERENCIA ELECTRONICA"
                    $community = \App\Models\Community::where('name', 'SIN NOMBRE')->first();
                    $firefighter = \App\Models\Firefighter::where('name', 'TRANSFERENCIA ELECTRONICA')->first();

                    if ($community && $firefighter) {
                        $balancingCapture = Capture::create([
                            'community_id' => $community->id,
                            'firefighter_id' => $firefighter->id,
                            'date' => now()->toDateString(),
                            'subtotal' => 0,
                            'rounding_commission' => 0,
                            'rounding_total' => 0,
                            'commission' => $difference,
                            'total' => $difference,
                            'year' => $request->year,
                            'requirement_number' => $request->requirement_number,
                            'assignment_date' => now(),
                        ]);
                    }
                }
            }
        }

        Capture::whereIn('id', $request->capture_ids)->update([
            'requirement_number' => $request->requirement_number,
            'year' => $request->year,
            'assignment_date' => now(),
        ]);

        return response()->json(['message' => 'Requerimiento y año asignados correctamente']);
    }

    public function getNextRequirementNumber(Request $request)
    {
        $year = $request->input('year', date('Y'));

        $maxReq = Capture::where(function ($query) use ($year) {
            $query->where('year', $year)
                ->orWhere(function ($q) use ($year) {
                    $q->whereNull('year')->whereYear('date', $year);
                });
        })
            ->whereNotNull('requirement_number')
            ->get()
            ->map(function ($capture) {
                return intval($capture->requirement_number);
            })
            ->max();

        $next = $maxReq ? $maxReq + 1 : 1;

        return response()->json(['next_requirement_number' => $next]);
    }

    public function getRequirements()
    {
        return Capture::whereNotNull('requirement_number')
            ->select('year', 'requirement_number', 'requirement_type')
            ->groupBy('year', 'requirement_number', 'requirement_type')
            ->orderByRaw('year DESC, requirement_type, MAX(id) DESC')
            ->get()
            ->map(function ($item) {
                return [
                    'year' => (int) $item->year,
                    'requirement_number' => (string) $item->requirement_number,
                    'requirement_type' => $item->requirement_type ?? 'bomberos',
                ];
            });
    }
}
