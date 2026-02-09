<?php

namespace App\Http\Controllers;

use App\Models\Capture;
use App\Models\Community;
use App\Models\Firefighter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FirefighterQueryExport;

class FirefighterQueryController extends Controller
{
    public function index(Request $request)
    {
        $query = Capture::with(['community', 'firefighter']);

        // Filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('requirement_number')) {
            $query->where('requirement_number', $request->requirement_number);
        }

        if ($request->filled('community_id')) {
            $query->where('community_id', $request->community_id);
        }

        if ($request->filled('firefighter_id')) {
            $query->where('firefighter_id', $request->firefighter_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        if ($request->filled('amount_min')) {
            $query->where('total', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('total', '<=', $request->amount_max);
        }

        // Sorting
        $sortField = $request->get('sort', 'date');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $captures = $query->paginate(25)->withQueryString();

        // Get available years for filter
        $availableYears = Capture::distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->values();

        // Get available requirement numbers (filtered by year if selected)
        $availableRequirements = Capture::when($request->filled('year'), function ($q) use ($request) {
            $q->where('year', $request->year);
        })
            ->distinct()
            ->orderBy('requirement_number', 'asc')
            ->pluck('requirement_number')
            ->filter()
            ->values();

        // Get available communities
        $availableCommunities = Community::orderBy('name', 'asc')->get();

        // Get available firefighters (filtered by community if selected)
        $availableFirefighters = Firefighter::when($request->filled('community_id'), function ($q) use ($request) {
            $q->where('community_id', $request->community_id);
        })
            ->orderBy('name', 'asc')
            ->get();

        // Calculate totals for filtered results
        $totals = Capture::query();

        // Apply same filters for totals
        if ($request->filled('year')) {
            $totals->where('year', $request->year);
        }
        if ($request->filled('requirement_number')) {
            $totals->where('requirement_number', $request->requirement_number);
        }
        if ($request->filled('community_id')) {
            $totals->where('community_id', $request->community_id);
        }
        if ($request->filled('firefighter_id')) {
            $totals->where('firefighter_id', $request->firefighter_id);
        }
        if ($request->filled('date_from')) {
            $totals->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $totals->where('date', '<=', $request->date_to);
        }
        if ($request->filled('amount_min')) {
            $totals->where('total', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $totals->where('total', '<=', $request->amount_max);
        }

        $totalSums = [
            'subtotal' => $totals->sum('subtotal'),
            'commission' => $totals->sum('commission'),
            'total' => $totals->sum('subtotal') - $totals->sum('commission'),
            'count' => $totals->count(),
        ];

        return Inertia::render('FirefighterQuery/Index', [
            'captures' => $captures,
            'filters' => $request->only(['year', 'requirement_number', 'community_id', 'firefighter_id', 'date_from', 'date_to', 'amount_min', 'amount_max', 'sort', 'direction']),
            'availableYears' => $availableYears,
            'availableRequirements' => $availableRequirements,
            'availableCommunities' => $availableCommunities,
            'availableFirefighters' => $availableFirefighters,
            'totals' => $totalSums,
        ]);
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');

        $query = Capture::with(['community', 'firefighter']);

        // Apply same filters as index
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }
        if ($request->filled('requirement_number')) {
            $query->where('requirement_number', $request->requirement_number);
        }
        if ($request->filled('community_id')) {
            $query->where('community_id', $request->community_id);
        }
        if ($request->filled('firefighter_id')) {
            $query->where('firefighter_id', $request->firefighter_id);
        }
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        if ($request->filled('amount_min')) {
            $query->where('total', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('total', '<=', $request->amount_max);
        }

        $captures = $query->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.firefighter_query', [
                'captures' => $captures,
                'filters' => $request->only(['year', 'requirement_number', 'community_id', 'firefighter_id', 'date_from', 'date_to', 'amount_min', 'amount_max']),
            ]);
            return $pdf->download('consulta_bomberos_' . date('Y-m-d') . '.pdf');
        }

        // Excel export
        return Excel::download(new FirefighterQueryExport($captures), 'consulta_bomberos_' . date('Y-m-d') . '.xlsx');
    }
}
