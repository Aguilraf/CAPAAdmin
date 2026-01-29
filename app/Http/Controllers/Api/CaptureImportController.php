<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Capture;
use App\Models\Community;
use App\Models\Firefighter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CaptureImportController extends Controller
{
    public function import(Request $request)
    {
        $request->validate([
            'records' => 'required|array',
            'records.*.date' => 'required|date',
            'records.*.community_name' => 'required|string',
            'records.*.firefighter_name' => 'required|string',
            'records.*.subtotal' => 'required|numeric',
            'records.*.commission' => 'required|numeric',
            'records.*.total' => 'required|numeric',
            'records.*.requirement_number' => 'nullable|string',
        ]);

        $records = $request->input('records');
        $results = [
            'success' => 0,
            'errors' => [],
        ];

        DB::beginTransaction();

        try {
            foreach ($records as $index => $row) {
                // normalize names for matching
                $communityName = trim($row['community_name']);
                $firefighterName = trim($row['firefighter_name']);

                // Find Community
                $community = Community::where('name', $communityName)->first();

                if (!$community) {
                    $results['errors'][] = "Fila " . ($index + 1) . ": Comunidad no encontrada '{$communityName}'";
                    continue;
                }

                // Find Firefighter in that community
                $firefighter = Firefighter::where('community_id', $community->id)
                    ->where('name', $firefighterName)
                    ->first();

                if (!$firefighter) {
                    // Create inactive firefighter if not found (historical data support)
                    $firefighter = Firefighter::create([
                        'name' => $firefighterName,
                        'community_id' => $community->id,
                        'active' => false, // Set as inactive
                    ]);
                }

                // Create Capture
                Capture::create([
                    'date' => $row['date'],
                    'year' => date('Y', strtotime($row['date'])), // Derive from date
                    'community_id' => $community->id,
                    'firefighter_id' => $firefighter->id,
                    'subtotal' => $row['subtotal'],
                    'commission' => $row['commission'],
                    'total' => $row['total'],
                    'rounding_commission' => 0, // Default to 0 for imports
                    'rounding_total' => 0,      // Default to 0 for imports
                    'requirement_number' => null, // Explicitly null for pending assignment
                ]);

                $results['success']++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Import error: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al procesar importación: ' . $e->getMessage()], 500);
        }

        return response()->json([
            'message' => "Proceso completado. {$results['success']} registros importados.",
            'details' => $results
        ]);
    }
}
