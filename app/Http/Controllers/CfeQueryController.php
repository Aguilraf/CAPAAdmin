<?php

namespace App\Http\Controllers;

use App\Models\CfeReceipt;
use App\Models\Requirement;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CfeQueryExport;

class CfeQueryController extends Controller
{
    public function index(Request $request)
    {
        $query = CfeReceipt::with([
            'requirement' => function ($q) {
                $q->select('id', 'year', 'number', 'assignment_date', 'start_date', 'end_date', 'total');
            }
        ]);

        // Filters
        if ($request->filled('year')) {
            $query->whereHas('requirement', function ($q) use ($request) {
                $q->where('year', $request->year);
            });
        }

        if ($request->filled('requirement_number')) {
            $query->whereHas('requirement', function ($q) use ($request) {
                $q->where('number', $request->requirement_number);
            });
        }

        if ($request->filled('rpu')) {
            $query->where('rpu', 'like', '%' . $request->rpu . '%');
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('date_from')) {
            $query->where('period_start', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('period_end', '<=', $request->date_to);
        }

        if ($request->filled('amount_min')) {
            $query->where('total', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('total', '<=', $request->amount_max);
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        if ($sortField === 'year' || $sortField === 'requirement_number') {
            $query->join('requirements', 'cfe_receipts.requirement_id', '=', 'requirements.id')
                ->orderBy('requirements.' . ($sortField === 'year' ? 'year' : 'number'), $sortDirection)
                ->select('cfe_receipts.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Pagination
        $receipts = $query->paginate(25)->withQueryString();

        // Get available years for filter
        $availableYears = Requirement::where('type', 'cfe')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Calculate totals for filtered results
        $totals = CfeReceipt::with([
            'requirement' => function ($q) {
                $q->select('id', 'year', 'number');
            }
        ]);

        // Apply same filters for totals
        if ($request->filled('year')) {
            $totals->whereHas('requirement', function ($q) use ($request) {
                $q->where('year', $request->year);
            });
        }
        if ($request->filled('requirement_number')) {
            $totals->whereHas('requirement', function ($q) use ($request) {
                $q->where('number', $request->requirement_number);
            });
        }
        if ($request->filled('rpu')) {
            $totals->where('rpu', 'like', '%' . $request->rpu . '%');
        }
        if ($request->filled('search')) {
            $totals->where('description', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('date_from')) {
            $totals->where('period_start', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $totals->where('period_end', '<=', $request->date_to);
        }
        if ($request->filled('amount_min')) {
            $totals->where('total', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $totals->where('total', '<=', $request->amount_max);
        }

        $totalSums = [
            'subtotal' => $totals->sum('subtotal'),
            'iva' => $totals->sum('iva'),
            'total' => $totals->sum('total'),
            'count' => $totals->count(),
        ];

        return Inertia::render('CfeQuery/Index', [
            'receipts' => $receipts,
            'filters' => $request->only(['year', 'requirement_number', 'rpu', 'search', 'date_from', 'date_to', 'amount_min', 'amount_max', 'sort', 'direction']),
            'availableYears' => $availableYears,
            'totals' => $totalSums,
        ]);
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');

        $query = CfeReceipt::with([
            'requirement' => function ($q) {
                $q->select('id', 'year', 'number', 'assignment_date', 'start_date', 'end_date', 'total');
            }
        ]);

        // Apply same filters as index
        if ($request->filled('year')) {
            $query->whereHas('requirement', function ($q) use ($request) {
                $q->where('year', $request->year);
            });
        }
        if ($request->filled('requirement_number')) {
            $query->whereHas('requirement', function ($q) use ($request) {
                $q->where('number', $request->requirement_number);
            });
        }
        if ($request->filled('rpu')) {
            $query->where('rpu', 'like', '%' . $request->rpu . '%');
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('date_from')) {
            $query->where('period_start', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('period_end', '<=', $request->date_to);
        }
        if ($request->filled('amount_min')) {
            $query->where('total', '>=', $request->amount_min);
        }
        if ($request->filled('amount_max')) {
            $query->where('total', '<=', $request->amount_max);
        }

        $receipts = $query->get();

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.cfe_query', [
                'receipts' => $receipts,
                'filters' => $request->only(['year', 'requirement_number', 'rpu', 'search', 'date_from', 'date_to', 'amount_min', 'amount_max']),
            ]);
            return $pdf->download('consulta_cfe_' . date('Y-m-d') . '.pdf');
        }

        // Excel export
        return Excel::download(new CfeQueryExport($receipts), 'consulta_cfe_' . date('Y-m-d') . '.xlsx');
    }
}
