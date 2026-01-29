<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Capture;
use App\Models\FirefighterSetting; // Renamed
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class FirefighterReportPdfController extends Controller
{
    public function download(Request $request)
    {
        $query = Capture::with(['community', 'firefighter'])->orderBy('id', 'asc');

        if ($request->has('year')) {
            $query->where('year', $request->year);
        }

        if ($request->has('requirement_number')) {
            $query->where('requirement_number', $request->requirement_number);
        }

        if ($request->has('requirement_type')) {
            $query->where('requirement_type', $request->requirement_type);
        }

        $captures = $query->get();
        $settings = FirefighterSetting::pluck('value', 'key'); // Renamed

        // Get assignment date from the first record
        $assignmentDate = $captures->first()->assignment_date ?? now();

        $pdf = Pdf::loadView('reports.bomberos', [
            'captures' => $captures,
            'settings' => $settings,
            'year' => $request->year,
            'requirement_number' => $request->requirement_number,
            'assignment_date' => $assignmentDate
        ]);

        return $pdf->download('Reporte_Bomberos_' . date('Y-m-d') . '.pdf');
    }
}
