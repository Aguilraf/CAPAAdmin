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
        $settings = FirefighterSetting::pluck('value', 'key')->toArray(); // Renamed

        // UNIFY DATA: Override Firefighter settings with System settings for logos
        $systemSettings = \App\Models\Setting::whereIn('key', ['logo_qroo', 'logo_unidos', 'footer_imagen'])->pluck('value', 'key');

        $settings['report_logo_state'] = $systemSettings['logo_qroo'] ?? null;
        $settings['report_logo_campaign'] = $systemSettings['logo_unidos'] ?? null;
        $settings['report_logo_footer'] = $systemSettings['footer_imagen'] ?? null;

        // Get assignment date from the first record
        $assignmentDate = $captures->first()->assignment_date ?? now();

        $pdf = Pdf::loadView('reports.bomberos', [
            'captures' => $captures,
            'settings' => $this->processImagesForPdf($settings),
            'year' => $request->year,
            'requirement_number' => $request->requirement_number,
            'assignment_date' => $assignmentDate
        ]);

        return $pdf->download('Reporte_Bomberos_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Convert image paths in settings to Base64 data URIs for robust PDF generation.
     */
    private function processImagesForPdf($settings)
    {
        $imageKeys = ['report_logo_state', 'report_logo_campaign', 'report_logo_footer'];

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
