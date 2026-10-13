<?php

namespace App\Http\Controllers;

use App\Models\MaterialOut;
use App\Services\MaterialOutExcelExportService;
use Illuminate\Http\Request;

class MaterialOutController extends Controller
{
    public function index()
    {
        return view('pages.material-out.index');
    }

    public function exportExcel(Request $request, MaterialOutExcelExportService $exportService)
    {
        $query = MaterialOut::with(['item.category']);

        $module = $request->get('module', 'all');
        if (!empty($module) && $module !== 'all') {
            $query->whereHas('item.category', function ($cq) use ($module) {
                if ($module === 'Cat' || $module === 'Cat / Painting') {
                    $cq->where('name', 'like', '%Cat%');
                } else {
                    $cq->where('name', $module);
                }
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }
        if ($request->filled('project')) {
            $query->where('project', $request->project);
        }
        if ($request->filled('pic')) {
            $query->where('pic', $request->pic);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('project', 'like', "%{$s}%")
                    ->orWhere('pic', 'like', "%{$s}%")
                    ->orWhere('reference_number', 'like', "%{$s}%")
                    ->orWhereHas('item', function ($iq) use ($s) {
                        $iq->where('name', 'like', "%{$s}%")
                            ->orWhere('code_item', 'like', "%{$s}%");
                    });
            });
        }

        $records = $query->orderBy('date', 'asc')->get();

        return $exportService->export(
            records: $records,
            startDate: $request->get('start_date'),
            endDate: $request->get('end_date'),
            module: $module
        );
    }
}
