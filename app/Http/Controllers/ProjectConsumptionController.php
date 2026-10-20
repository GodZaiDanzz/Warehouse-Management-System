<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MaterialOut;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectConsumptionController extends Controller
{
    /**
     * Display Project Consumption Dashboard with aggregated metrics per project.
     */
    public function index(Request $request)
    {
        // Auto-sync distinct projects from material_outs to projects table only if unlinked records exist
        if (MaterialOut::whereNull('project_id')->whereNotNull('project')->where('project', '!=', '')->exists()) {
            $distinctMaterialProjects = MaterialOut::whereNotNull('project')
                ->where('project', '!=', '')
                ->whereNull('project_id')
                ->distinct()
                ->pluck('project');

            foreach ($distinctMaterialProjects as $prjName) {
                $trimmedName = trim($prjName);
                if (!empty($trimmedName)) {
                    Project::firstOrCreate(['name' => $trimmedName], ['status' => 'Ongoing']);
                }
            }

            DB::statement("UPDATE material_outs mo JOIN projects p ON mo.project = p.name SET mo.project_id = p.id WHERE mo.project_id IS NULL");
        }

        $latestDateStr = MaterialOut::max('date') ?? date('Y-m-d');
        $endDate = $request->get('end_date', $latestDateStr);
        $startDate = $request->get('start_date', Carbon::parse($endDate)->subMonths(6)->format('Y-m-d'));

        $query = Project::query();

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('po_number', 'like', "%{$s}%")
                  ->orWhere('location', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        $projectsList = $query->orderByRaw("CASE WHEN status = 'Ongoing' THEN 1 WHEN status = 'Planning' THEN 2 WHEN status = 'On Hold' THEN 3 ELSE 4 END")->orderBy('name')->get();

        // Calculate material consumption metrics per project in 1 aggregated query (eliminates N+1)
        $projectIds = $projectsList->pluck('id')->filter()->all();
        $projectNames = $projectsList->pluck('name')->filter()->all();

        $aggRows = collect();
        if (!empty($projectIds) || !empty($projectNames)) {
            $moAggQuery = MaterialOut::leftJoin('items', 'material_outs.item_id', '=', 'items.id')
                ->where(function($q) use ($projectIds, $projectNames) {
                    if (!empty($projectIds)) {
                        $q->whereIn('material_outs.project_id', $projectIds);
                    }
                    if (!empty($projectNames)) {
                        $q->orWhereIn('material_outs.project', $projectNames);
                    }
                });

            if (!empty($startDate)) {
                $moAggQuery->whereDate('material_outs.date', '>=', $startDate);
            }
            if (!empty($endDate)) {
                $moAggQuery->whereDate('material_outs.date', '<=', $endDate);
            }
            if ($request->filled('category_id')) {
                $moAggQuery->whereHas('item', fn($iq) => $iq->where('category_id', $request->category_id));
            }

            $aggRows = $moAggQuery->selectRaw("
                material_outs.project_id,
                material_outs.project,
                UPPER(TRIM(COALESCE(material_outs.unit, items.unit, 'PCS'))) as item_unit,
                SUM(material_outs.quantity) as sum_qty,
                SUM(material_outs.quantity * COALESCE(items.unit_price, 0)) as sum_cost,
                COUNT(material_outs.id) as trans_count,
                MAX(material_outs.date) as max_date
            ")->groupBy('material_outs.project_id', 'material_outs.project', 'item_unit')->get();
        }

        // Map aggregated results by project identifier
        $projectAggMap = [];
        foreach ($aggRows as $row) {
            $keys = [];
            if ($row->project_id) {
                $keys[] = 'id_' . $row->project_id;
            }
            if (!empty($row->project)) {
                $keys[] = 'name_' . trim($row->project);
            }
            foreach ($keys as $k) {
                if (!isset($projectAggMap[$k])) {
                    $projectAggMap[$k] = [
                        'rows' => [],
                        'total_trans' => 0,
                        'total_cost' => 0,
                        'max_date' => null,
                    ];
                }
                $projectAggMap[$k]['rows'][] = [
                    'unit' => $row->item_unit,
                    'qty' => (float) $row->sum_qty,
                ];
                $projectAggMap[$k]['total_trans'] += (int) $row->trans_count;
                $projectAggMap[$k]['total_cost'] += (float) $row->sum_cost;
                if (!$projectAggMap[$k]['max_date'] || $row->max_date > $projectAggMap[$k]['max_date']) {
                    $projectAggMap[$k]['max_date'] = $row->max_date;
                }
            }
        }

        foreach ($projectsList as $p) {
            $pData = $projectAggMap['id_' . $p->id] ?? ($projectAggMap['name_' . trim($p->name)] ?? null);

            $discreteQty = 0;
            $decimalTotals = [];
            $allUnitTotals = [];

            if ($pData) {
                foreach ($pData['rows'] as $r) {
                    $u = $r['unit'];
                    $q = (float) $r['qty'];
                    $allUnitTotals[$u] = ($allUnitTotals[$u] ?? 0) + $q;
                    if (is_decimal_unit($u)) {
                        $decimalTotals[$u] = ($decimalTotals[$u] ?? 0) + $q;
                    } else {
                        $discreteQty += $q;
                    }
                }
            }

            $p->discrete_qty = $discreteQty;
            $p->decimal_totals = $decimalTotals;
            $p->decimal_string = format_unit_breakdown($decimalTotals);
            $p->total_trans = (int) ($pData['total_trans'] ?? 0);
            $p->total_cost = (float) ($pData['total_cost'] ?? 0);
            $p->last_date = $pData['max_date'] ?? null;
            $p->formatted_qty = format_unit_breakdown($allUnitTotals);
            $p->sort_metric = $discreteQty + array_sum($decimalTotals);
        }

        // Top 8 Projects for ApexChart Visualization
        $topProjects = $projectsList->sortByDesc('sort_metric')->take(8);
        $chartProjectNames = [];
        $chartProjectFullNames = [];
        $chartProjectDiscreteTotals = [];
        $chartProjectDecimalTotals = [];
        $chartProjectFormattedTotals = [];

        foreach ($topProjects as $tp) {
            if ($tp->sort_metric > 0) {
                $name = $tp->name;
                $chartProjectNames[] = strlen($name) > 20 ? substr($name, 0, 17) . '..' : $name;
                $chartProjectFullNames[] = $name;
                $chartProjectDiscreteTotals[] = (int) round($tp->discrete_qty);
                $chartProjectDecimalTotals[] = (float) array_sum($tp->decimal_totals);
                $chartProjectFormattedTotals[] = $tp->formatted_qty;
            }
        }

        // Paginate manually
        $page = (int) $request->get('page', 1);
        $perPage = 15;
        $paginatedProjects = new \Illuminate\Pagination\LengthAwarePaginator(
            $projectsList->forPage($page, $perPage)->values(),
            $projectsList->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $categories = Category::orderBy('name')->get();

        // Overall summary for the date/category filter
        $overallQuery = MaterialOut::query();
        if (!empty($startDate)) {
            $overallQuery->whereDate('date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $overallQuery->whereDate('date', '<=', $endDate);
        }
        if ($request->filled('category_id')) {
            $overallQuery->whereHas('item', fn($iq) => $iq->where('category_id', $request->category_id));
        }

        $overallRows = $overallQuery
            ->leftJoin('items', 'material_outs.item_id', '=', 'items.id')
            ->select(
                'material_outs.quantity',
                'items.unit_price',
                DB::raw("UPPER(TRIM(COALESCE(material_outs.unit, items.unit, 'PCS'))) as item_unit")
            )
            ->get();

        $overallDiscreteQty = 0;
        $overallDecimalTotals = [];
        $overallTotalCost = 0;
        foreach ($overallRows as $or) {
            $u = $or->item_unit;
            $q = (float) $or->quantity;
            $price = (float) ($or->unit_price ?? 0);
            $overallTotalCost += ($q * $price);
            if (is_decimal_unit($u)) {
                $overallDecimalTotals[$u] = ($overallDecimalTotals[$u] ?? 0) + $q;
            } else {
                $overallDiscreteQty += $q;
            }
        }
        $overallDecimalString = format_unit_breakdown($overallDecimalTotals);

        $totalOverallProjects = Project::count();
        $ongoingProjectsCount = Project::where('status', 'Ongoing')->count();
        $completedProjectsCount = Project::where('status', 'Completed')->count();

        // Distinct fabrication locations for filter & datalist suggestions
        $fabricationLocations = Project::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        $defaultSuggestions = collect(['Workshop 1', 'Workshop 2']);
        $allLocationSuggestions = $fabricationLocations->merge($defaultSuggestions)->unique()->values();

        $currentUser = \Illuminate\Support\Facades\Auth::user();
        $canViewPrices = $currentUser && $currentUser->canViewPrices();

        return view('pages.projects.index', [
            'title' => 'Rekapitulasi Penyerapan Material per Project',
            'projects' => $paginatedProjects,
            'categories' => $categories,
            'fabricationLocations' => $fabricationLocations,
            'allLocationSuggestions' => $allLocationSuggestions,
            'selectedLocation' => $request->get('location', ''),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalOverallQty' => $overallDiscreteQty,
            'overallDiscreteQty' => $overallDiscreteQty,
            'overallDecimalString' => $overallDecimalString,
            'overallTotalCost' => $overallTotalCost,
            'canViewPrices' => $canViewPrices,
            'totalOverallProjects' => $totalOverallProjects,
            'ongoingProjectsCount' => $ongoingProjectsCount,
            'completedProjectsCount' => $completedProjectsCount,
            'chartProjectNames' => $chartProjectNames,
            'chartProjectFullNames' => $chartProjectFullNames,
            'chartProjectDiscreteTotals' => $chartProjectDiscreteTotals,
            'chartProjectDecimalTotals' => $chartProjectDecimalTotals,
            'chartProjectFormattedTotals' => $chartProjectFormattedTotals,
        ]);
    }
}
