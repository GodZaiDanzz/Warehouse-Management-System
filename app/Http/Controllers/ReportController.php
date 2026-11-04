<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\StockLog;
use App\Models\MaterialReject;
use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use App\Services\MaterialOutExcelExportService;

class ReportController extends Controller
{
    /**
     * Display warehouse reports page with filters, pagination, and print options.
     */
    public function index(Request $request)
    {
        $categories = Category::all();
        $query = StockLog::with(['item.category', 'user']);

        // Check if any filter is active
        $isFiltered = $request->filled('type')
            || $request->filled('category_id')
            || $request->filled('start_date')
            || $request->filled('end_date')
            || $request->filled('search');

        // Filter by Transaction Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Category
        if ($request->filled('category_id')) {
            $query->whereHas('item', function($iq) use ($request) {
                $iq->where('category_id', $request->category_id);
            });
        }

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Optional Search Filter
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->whereHas('item', function($iq) use ($s) {
                    $iq->where('name', 'like', "%{$s}%")
                       ->orWhere('code_item', 'like', "%{$s}%");
                })->orWhere('recipient_or_provider', 'like', "%{$s}%")
                  ->orWhere('notes', 'like', "%{$s}%");
            });
        }

        // Calculate summary metrics on full filtered dataset using direct SQL aggregation (avoids memory lag)
        $totalIn = (clone $query)->where('type', 'in')->sum('quantity');
        $totalOut = (clone $query)->where('type', 'out')->sum('quantity');
        $totalItemsCount = Item::count();

        // Determine per_page behavior (default 25 per page so page is bounded and responsive):
        $perPage = $request->get('per_page', 25);
        if (empty($perPage)) {
            $perPage = 25;
        }

        if ($perPage === 'all' || $perPage === 'semua' || (int)$perPage <= 0) {
            $totalCount = (clone $query)->count();
            $logs = $query->latest()->paginate(max(1, $totalCount ?: 1))->withQueryString();
        } else {
            $perPageInt = max(5, min(200, (int) $perPage));
            $logs = $query->latest()->paginate($perPageInt)->withQueryString();
        }

        return view('pages.reports.index', [
            'title' => 'Laporan Gudang - Report Mutasi Stok',
            'logs' => $logs,
            'categories' => $categories,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'totalItemsCount' => $totalItemsCount,
            'isFiltered' => $isFiltered,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Display Consumable Usage Report with 31-day filtering, ApexChart & Summary Table.
     */
    public function consumableReport(Request $request)
    {
        // Reference Latest Date in DB
        $latestDateStr = MaterialOut::max('date') ?? date('Y-m-d');

        // Default 31-day window
        $endDate = $request->get('end_date', $latestDateStr);
        $startDate = $request->get('start_date', Carbon::parse($endDate)->subDays(30)->format('Y-m-d'));

        $selectedItemIds = (array) $request->get('item_ids', []);
        if (empty($selectedItemIds) && $request->filled('item_id')) {
            $selectedItemIds = [$request->item_id];
        }
        $selectedItemIds = array_values(array_filter(array_map('intval', $selectedItemIds)));

        $query = MaterialOut::with(['item.category', 'projectRelation'])
            ->select('material_outs.*')
            ->join('items', 'material_outs.item_id', '=', 'items.id');

        if (!empty($startDate)) {
            $query->whereDate('material_outs.date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('material_outs.date', '<=', $endDate);
        }
        if (!empty($selectedItemIds)) {
            $query->whereIn('material_outs.item_id', $selectedItemIds);
        }
        if ($request->filled('project')) {
            $query->where('material_outs.project', $request->project);
        }
        if ($request->filled('pic')) {
            $query->where('material_outs.pic', $request->pic);
        }

        $logs = $query->orderBy('items.name', 'asc')
            ->orderBy('material_outs.date', 'asc')
            ->orderBy('material_outs.project', 'asc')
            ->orderBy('material_outs.id', 'asc')
            ->get();

        // Group logs per item so each material has its own separate block
        $groupedLogs = $logs->groupBy('item_id');

        $itemsList = Item::orderBy('name', 'asc')->get();
        $projectsList = MaterialOut::select('project')->distinct()->whereNotNull('project')->where('project', '!=', '')->orderBy('project', 'asc')->pluck('project');
        $picsList = MaterialOut::select('pic')->distinct()->whereNotNull('pic')->where('pic', '!=', '')->orderBy('pic', 'asc')->pluck('pic');

        // Summary metrics
        $totalQuantity = $logs->sum('quantity');
        $totalTransactions = $logs->count();
        $totalProjectsCount = $logs->pluck('project')->filter()->unique()->count();
        $totalPicsCount = $logs->pluck('pic')->filter()->unique()->count();

        // Calculate days span
        $daysSpan = max(1, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);

        // Chart Data: Top accumulated consumable items in the filtered period
        $topConsumablesQuery = MaterialOut::select('material_outs.item_id', DB::raw('SUM(material_outs.quantity) as total_qty'))
            ->with('item')
            ->whereDate('material_outs.date', '>=', $startDate)
            ->whereDate('material_outs.date', '<=', $endDate);

        if (!empty($selectedItemIds)) {
            $topConsumablesQuery->whereIn('material_outs.item_id', $selectedItemIds);
        }
        if ($request->filled('project')) {
            $topConsumablesQuery->where('material_outs.project', $request->project);
        }
        if ($request->filled('pic')) {
            $topConsumablesQuery->where('material_outs.pic', $request->pic);
        }

        $topConsumables = $topConsumablesQuery->groupBy('material_outs.item_id')
            ->orderByDesc('total_qty')
            ->take(10)
            ->get();

        $chartNames = [];
        $chartShortNames = [];
        $chartTotals = [];

        foreach ($topConsumables as $tc) {
            $name = $tc->item?->name ?? ('Item #' . $tc->item_id);
            $chartNames[] = $name;
            $chartShortNames[] = strlen($name) > 16 ? substr($name, 0, 13) . '..' : $name;
            $chartTotals[] = (float) $tc->total_qty;
        }

        // Summary Conclusion Table Data (Tabel Ringkasan Otomatis)
        $summaryTableData = [];

        foreach ($groupedLogs as $itemId => $itemLogs) {
            $itemObj = $itemLogs->first()->item;
            $itemName = $itemObj ? $itemObj->name : ('Item #' . $itemId);
            $unit = $itemLogs->first()->unit ?? ($itemObj ? $itemObj->unit : 'PCS');
            $itemTotalQty = $itemLogs->sum('quantity');
            $itemTransCount = $itemLogs->count();
            $avgPerDay = round($itemTotalQty / $daysSpan, 1);

            $topPic = $itemLogs->groupBy('pic')->sortByDesc(fn($g) => $g->sum('quantity'))->keys()->first() ?? '-';
            $topProject = $itemLogs->groupBy('project')->sortByDesc(fn($g) => $g->sum('quantity'))->keys()->first() ?? '-';

            // Auto-generated conclusion text
            if ($avgPerDay > 50 || $itemTotalQty > 1000) {
                $status = 'critical';
                $keterangan = "Akumulasi Sangat Tinggi ({$itemTransCount}x transaksi). Mayoritas diambil oleh PIC '{$topPic}' untuk Project '{$topProject}'. Disarankan evaluasi & re-order stok.";
            } elseif ($avgPerDay > 10 || $itemTotalQty > 200) {
                $status = 'warning';
                $keterangan = "Penggunaan Sedang ({$itemTransCount}x transaksi). Digunakan oleh PIC '{$topPic}' pada Project '{$topProject}'.";
            } else {
                $status = 'normal';
                $keterangan = "Penggunaan Normal ({$itemTransCount}x transaksi). Terdaftar atas nama PIC '{$topPic}' untuk Project '{$topProject}'.";
            }

            $summaryTableData[] = [
                'code_item' => $itemObj ? $itemObj->code_item : '-',
                'item_name' => $itemName,
                'unit' => $unit,
                'total_qty' => $itemTotalQty,
                'avg_per_day' => $avgPerDay,
                'trans_count' => $itemTransCount,
                'time_span' => "{$daysSpan} HARI",
                'top_pic' => $topPic,
                'top_project' => $topProject,
                'status' => $status,
                'keterangan' => $keterangan,
            ];
        }

        // Sort summary table by total_qty descending
        usort($summaryTableData, fn($a, $b) => $b['total_qty'] <=> $a['total_qty']);

        return view('pages.reports.consumable', [
            'title' => 'Dashboard & Laporan Pemakaian Consumable (31 Hari)',
            'logs' => $logs,
            'groupedLogs' => $groupedLogs,
            'itemsList' => $itemsList,
            'projectsList' => $projectsList,
            'picsList' => $picsList,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'latestDateStr' => $latestDateStr,
            'daysSpan' => $daysSpan,
            'totalQuantity' => $totalQuantity,
            'totalTransactions' => $totalTransactions,
            'totalProjectsCount' => $totalProjectsCount,
            'totalPicsCount' => $totalPicsCount,
            'chartNames' => $chartNames,
            'chartShortNames' => $chartShortNames,
            'chartTotals' => $chartTotals,
            'summaryTableData' => $summaryTableData,
            'selectedItemIds' => $selectedItemIds,
        ]);
    }

    /**
     * Export Consumable Report to Excel (.xlsx) with full cell borders and formatted tables matching user screenshot.
     */
    public function exportConsumableExcel(Request $request)
    {
        $latestDateStr = MaterialOut::max('date') ?? date('Y-m-d');
        $endDate = $request->get('end_date', $latestDateStr);
        $startDate = $request->get('start_date', Carbon::parse($endDate)->subDays(30)->format('Y-m-d'));

        $selectedItemIds = (array) $request->get('item_ids', []);
        if (empty($selectedItemIds) && $request->filled('item_id')) {
            $selectedItemIds = [$request->item_id];
        }
        $selectedItemIds = array_values(array_filter(array_map('intval', $selectedItemIds)));

        $query = MaterialOut::with(['item.category', 'projectRelation'])
            ->select('material_outs.*')
            ->join('items', 'material_outs.item_id', '=', 'items.id');

        if (!empty($startDate)) {
            $query->whereDate('material_outs.date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('material_outs.date', '<=', $endDate);
        }
        if (!empty($selectedItemIds)) {
            $query->whereIn('material_outs.item_id', $selectedItemIds);
        }
        if ($request->filled('project')) {
            $query->where('material_outs.project', $request->project);
        }
        if ($request->filled('pic')) {
            $query->where('material_outs.pic', $request->pic);
        }

        $logs = $query->orderBy('items.name', 'asc')
            ->orderBy('material_outs.date', 'asc')
            ->orderBy('material_outs.project', 'asc')
            ->orderBy('material_outs.id', 'asc')
            ->get();

        return app(MaterialOutExcelExportService::class)->export(
            records: $logs,
            startDate: $startDate,
            endDate: $endDate,
            module: 'Consumable',
            customTitle: 'LAPORAN PEMAKAIAN MATERIAL CONSUMABLE'
        );
    }

    /**
     * 1. Laporan Inspeksi Incoming Material & Cat (Prompt Section 6)
     */
    public function incomingInspectionReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $selectedQc = $request->get('qc_status', 'all');
        $selectedSupplier = $request->get('supplier', '');
        $selectedCategory = $request->get('category', 'all');

        $query = MaterialIn::with(['item.category', 'user', 'qcUser']);

        if (!empty($startDate)) {
            $query->whereDate('date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('date', '<=', $endDate);
        }
        if (!empty($selectedQc) && $selectedQc !== 'all') {
            $query->where('qc_status', $selectedQc);
        }
        if (!empty($selectedSupplier)) {
            $query->where('supplier', $selectedSupplier);
        }
        if (!empty($selectedCategory) && $selectedCategory !== 'all') {
            if ($selectedCategory === 'cat') {
                $catIds = Category::getCatCategoryIds();
                $query->whereHas('item', fn($iq) => $iq->whereIn('category_id', $catIds)->orWhereHas('category', fn($cq) => $cq->where('name', 'like', '%Cat%')));
            } elseif (is_numeric($selectedCategory)) {
                $selectedCat = Category::find($selectedCategory);
                if ($selectedCat && $selectedCat->isCatUmbrella()) {
                    $catIds = Category::getCatCategoryIds();
                    $query->whereHas('item', fn($iq) => $iq->whereIn('category_id', $catIds)->orWhereHas('category', fn($cq) => $cq->where('name', 'like', '%Cat%')));
                } else {
                    $query->whereHas('item', fn($iq) => $iq->where('category_id', $selectedCategory));
                }
            } else {
                $query->whereHas('item.category', fn($q) => $q->where('name', $selectedCategory)->orWhere('name', 'like', "%{$selectedCategory}%"));
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('supplier', 'like', "%{$s}%")
                  ->orWhere('po_number', 'like', "%{$s}%")
                  ->orWhere('received_by', 'like', "%{$s}%")
                  ->orWhereHas('item', fn($iq) => $iq->where('name', 'like', "%{$s}%")->orWhere('code_item', 'like', "%{$s}%"));
            });
        }

        $totalCount = (clone $query)->count();
        $totalApproved = (clone $query)->where('qc_status', 'approved')->count();
        $totalRejected = (clone $query)->where('qc_status', 'rejected')->count();
        $totalPending = (clone $query)->where('qc_status', 'pending_qc')->count();
        $totalQty = (clone $query)->sum('quantity');

        $suppliersList = MaterialIn::distinct('supplier')->whereNotNull('supplier')->where('supplier', '!=', '')->orderBy('supplier')->pluck('supplier');
        $categoriesList = Category::orderBy('name')->get();

        $logs = $query->latest('date')->latest('id')->paginate(25)->withQueryString();

        return view('pages.reports.incoming-inspection', [
            'title' => 'Laporan Inspeksi Incoming Material & Cat',
            'logs' => $logs,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedQc' => $selectedQc,
            'selectedSupplier' => $selectedSupplier,
            'selectedCategory' => $selectedCategory,
            'suppliersList' => $suppliersList,
            'categoriesList' => $categoriesList,
            'totalCount' => $totalCount,
            'totalApproved' => $totalApproved,
            'totalRejected' => $totalRejected,
            'totalPending' => $totalPending,
            'totalQty' => $totalQty,
        ]);
    }

    public function exportIncomingInspectionExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $selectedQc = $request->get('qc_status', 'all');
        $selectedSupplier = $request->get('supplier', '');
        $selectedCategory = $request->get('category', 'all');

        $query = MaterialIn::with(['item.category', 'user', 'qcUser']);

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if (!empty($selectedQc) && $selectedQc !== 'all') $query->where('qc_status', $selectedQc);
        if (!empty($selectedSupplier)) $query->where('supplier', $selectedSupplier);
        if (!empty($selectedCategory) && $selectedCategory !== 'all') {
            if ($selectedCategory === 'cat') {
                $catIds = Category::getCatCategoryIds();
                $query->whereHas('item', fn($iq) => $iq->whereIn('category_id', $catIds)->orWhereHas('category', fn($cq) => $cq->where('name', 'like', '%Cat%')));
            } elseif (is_numeric($selectedCategory)) {
                $selectedCat = Category::find($selectedCategory);
                if ($selectedCat && $selectedCat->isCatUmbrella()) {
                    $catIds = Category::getCatCategoryIds();
                    $query->whereHas('item', fn($iq) => $iq->whereIn('category_id', $catIds)->orWhereHas('category', fn($cq) => $cq->where('name', 'like', '%Cat%')));
                } else {
                    $query->whereHas('item', fn($iq) => $iq->where('category_id', $selectedCategory));
                }
            } else {
                $query->whereHas('item.category', fn($q) => $q->where('name', $selectedCategory)->orWhere('name', 'like', "%{$selectedCategory}%"));
            }
        }

        $records = $query->latest('date')->latest('id')->get();
        $company = CompanyProfile::getProfile();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inspeksi Incoming');

        // Header Title
        $sheet->setCellValue('A1', strtoupper($company->name ?? 'PT MUDALAYA ENERGY INDONESIA'));
        $sheet->setCellValue('A2', 'LAPORAN INSPEKSI INCOMING MATERIAL & CAT (QUALITY CONTROL)');
        $sheet->setCellValue('A3', 'Periode: ' . format_date($startDate) . ' s/d ' . format_date($endDate));

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        $headers = [
            'A5' => 'NO',
            'B5' => 'TANGGAL TERIMA',
            'C5' => 'NO PO',
            'D5' => 'SUPPLIER / VENDOR',
            'E5' => 'KODE BARANG',
            'F5' => 'NAMA BARANG',
            'G5' => 'KATEGORI',
            'H5' => 'QTY FISIK',
            'I5' => 'SATUAN',
            'J5' => 'SATUAN GROSIR',
            'K5' => 'TGL PEMBUATAN (CAT)',
            'L5' => 'STATUS QC',
            'M5' => 'QC INSPECTOR',
            'N5' => 'CATATAN INSPEKSI',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A5:N5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '275538']],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(24);

        $row = 6;
        foreach ($records as $index => $item) {
            $isConsumable = $item->item && $item->item->isConsumable();
            $qcLabel = $isConsumable ? 'Bebas QC (Consumable)' : match($item->qc_status) {
                'approved' => 'OK / Lolos QC',
                'rejected' => 'REJECT / Ditolak',
                default => 'PENDING QC',
            };
            $mfgDate = ($item->manufacturing_date) ? format_date($item->manufacturing_date) : ($item->item && $item->item->isCat() && $item->item->manufacturing_date ? format_date($item->item->manufacturing_date) : '-');
            $wholesale = ($item->wholesale_qty && $item->wholesale_unit) ? format_quantity($item->wholesale_qty) . " {$item->wholesale_unit}" : '-';

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->date ? format_date($item->date) : '-');
            $sheet->setCellValue('C' . $row, $item->po_number ?? '-');
            $sheet->setCellValue('D' . $row, $item->supplier);
            $sheet->setCellValue('E' . $row, $item->item->code_item ?? '-');
            $sheet->setCellValue('F' . $row, $item->item->name ?? '-');
            $sheet->setCellValue('G' . $row, $item->item->category->name ?? 'Consumable');
            $sheet->setCellValue('H' . $row, (float) $item->quantity);
            $sheet->setCellValue('I' . $row, strtoupper($item->unit));
            $sheet->setCellValue('J' . $row, $wholesale);
            $sheet->setCellValue('K' . $row, $mfgDate);
            $sheet->setCellValue('L' . $row, $qcLabel);
            $sheet->setCellValue('M' . $row, $isConsumable ? 'Bebas QC' : ($item->qcUser->name ?? ($item->qc_by ? 'QC' : '-')));
            $sheet->setCellValue('N' . $row, $isConsumable ? 'Material consumable (langsung masuk stok)' : ($item->qc_notes ?? $item->notes ?? '-'));

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $row . ':L' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        $tableRange = 'A5:N' . ($row - 1);
        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D1D5DB']]],
        ]);

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'Laporan_Inspeksi_Incoming_' . date('Ymd_His') . '.xlsx';
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Set execution time and memory limits for PDF generation to prevent OOM
     */
    private function preparePdfEnvironment(): void
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);
    }

    public function exportIncomingInspectionPdf(Request $request)
    {
        $this->preparePdfEnvironment();

        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $selectedQc = $request->get('qc_status', 'all');
        $selectedSupplier = $request->get('supplier', '');
        $selectedCategory = $request->get('category', 'all');

        $query = MaterialIn::with(['item.category', 'user', 'qcUser']);

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if (!empty($selectedQc) && $selectedQc !== 'all') $query->where('qc_status', $selectedQc);
        if (!empty($selectedSupplier)) $query->where('supplier', $selectedSupplier);
        if (!empty($selectedCategory) && $selectedCategory !== 'all') {
            if ($selectedCategory === 'cat') {
                $catIds = Category::getCatCategoryIds();
                $query->whereHas('item', fn($iq) => $iq->whereIn('category_id', $catIds)->orWhereHas('category', fn($cq) => $cq->where('name', 'like', '%Cat%')));
            } elseif (is_numeric($selectedCategory)) {
                $selectedCat = Category::find($selectedCategory);
                if ($selectedCat && $selectedCat->isCatUmbrella()) {
                    $catIds = Category::getCatCategoryIds();
                    $query->whereHas('item', fn($iq) => $iq->whereIn('category_id', $catIds)->orWhereHas('category', fn($cq) => $cq->where('name', 'like', '%Cat%')));
                } else {
                    $query->whereHas('item', fn($iq) => $iq->where('category_id', $selectedCategory));
                }
            } else {
                $query->whereHas('item.category', fn($q) => $q->where('name', $selectedCategory)->orWhere('name', 'like', "%{$selectedCategory}%"));
            }
        }

        $records = $query->latest('date')->latest('id')->get();
        $company = CompanyProfile::getProfile();

        $pdf = Pdf::loadView('pages.reports.pdf.incoming-inspection', [
            'company' => $company,
            'records' => $records,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Inspeksi_Incoming_' . date('Ymd_His') . '.pdf');
    }

    /**
     * 2. Laporan Pengeluaran Material (Gabungan: All / Consumable / Secondary Part / Cat & Coating)
     */
    public function materialOutReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $category = $request->get('category', 'all');

        $query = MaterialOut::with(['item.category', 'projectRelation', 'user']);

        if ($category === 'consumable') {
            $query->whereHas('item.category', fn($q) => $q->where('name', 'Consumable'));
        } elseif ($category === 'secondary') {
            $query->whereHas('item.category', fn($q) => $q->where('name', 'Secondary Part'));
        } elseif ($category === 'cat') {
            $query->whereHas('item.category', fn($q) => $q->where('name', 'like', '%Cat%'));
        }

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($request->filled('project')) $query->where('project', $request->project);
        if ($request->filled('pic')) $query->where('pic', $request->pic);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('project', 'like', "%{$s}%")
                  ->orWhere('pic', 'like', "%{$s}%")
                  ->orWhereHas('item', fn($iq) => $iq->where('name', 'like', "%{$s}%")->orWhere('code_item', 'like', "%{$s}%"));
            });
        }

        $totalQty = (clone $query)->sum('quantity');
        $totalTrans = (clone $query)->count();
        $totalProjects = (clone $query)->distinct('project')->count('project');

        $baseQuery = MaterialOut::query();
        if ($category === 'consumable') {
            $baseQuery->whereHas('item.category', fn($q) => $q->where('name', 'Consumable'));
        } elseif ($category === 'secondary') {
            $baseQuery->whereHas('item.category', fn($q) => $q->where('name', 'Secondary Part'));
        } elseif ($category === 'cat') {
            $baseQuery->whereHas('item.category', fn($q) => $q->where('name', 'like', '%Cat%'));
        }

        $projectsList = (clone $baseQuery)->distinct('project')->pluck('project');
        $picsList = (clone $baseQuery)->distinct('pic')->pluck('pic');

        $logs = $query->latest('date')->latest('id')->paginate(25)->withQueryString();

        return view('pages.reports.material-out', [
            'title' => 'Laporan Pengeluaran Material',
            'logs' => $logs,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'category' => $category,
            'totalQty' => $totalQty,
            'totalTrans' => $totalTrans,
            'totalProjects' => $totalProjects,
            'projectsList' => $projectsList,
            'picsList' => $picsList,
        ]);
    }

    public function exportMaterialOutExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $category = $request->get('category', 'all');

        $query = MaterialOut::with(['item.category', 'projectRelation']);

        if ($category === 'consumable') {
            $query->whereHas('item.category', fn($q) => $q->where('name', 'Consumable'));
            $module = 'Consumable';
            $customTitle = 'LAPORAN PENGELUARAN CONSUMABLE GUDANG';
        } elseif ($category === 'secondary') {
            $query->whereHas('item.category', fn($q) => $q->where('name', 'Secondary Part'));
            $module = 'Secondary Part';
            $customTitle = 'LAPORAN PENGELUARAN SECONDARY PART GUDANG';
        } elseif ($category === 'cat') {
            $query->whereHas('item.category', fn($q) => $q->where('name', 'like', '%Cat%'));
            $module = 'Cat';
            $customTitle = 'LAPORAN PENGELUARAN CAT GUDANG';
        } else {
            $module = 'Pengeluaran Material';
            $customTitle = 'LAPORAN PENGELUARAN MATERIAL GUDANG (SEMUA KATEGORI)';
        }

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($request->filled('project')) $query->where('project', $request->project);
        if ($request->filled('pic')) $query->where('pic', $request->pic);

        $records = $query->orderBy('date', 'asc')->get();

        return app(MaterialOutExcelExportService::class)->export(
            records: $records,
            startDate: $startDate,
            endDate: $endDate,
            module: $module,
            customTitle: $customTitle
        );
    }

    public function exportMaterialOutPdf(Request $request)
    {
        $this->preparePdfEnvironment();

        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $category = $request->get('category', 'all');

        $query = MaterialOut::with(['item.category', 'projectRelation']);

        if ($category === 'consumable') {
            $query->whereHas('item.category', fn($q) => $q->where('name', 'Consumable'));
            $categoryLabel = 'Consumable';
        } elseif ($category === 'secondary') {
            $query->whereHas('item.category', fn($q) => $q->where('name', 'Secondary Part'));
            $categoryLabel = 'Secondary Part';
        } elseif ($category === 'cat') {
            $query->whereHas('item.category', fn($q) => $q->where('name', 'like', '%Cat%'));
            $categoryLabel = 'Cat / Coating';
        } else {
            $categoryLabel = 'Semua Kategori';
        }

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($request->filled('project')) $query->where('project', $request->project);
        if ($request->filled('pic')) $query->where('pic', $request->pic);

        $records = $query->latest('date')->latest('id')->get();
        $company = CompanyProfile::getProfile();

        $pdf = Pdf::loadView('pages.reports.pdf.material-out', [
            'company' => $company,
            'records' => $records,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'categoryLabel' => $categoryLabel,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Pengeluaran_Material_' . date('Ymd_His') . '.pdf');
    }

    /**
     * 3. Laporan Pengeluaran Cat / Painting (Prompt Section 6)
     */
    public function catPaintingReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $query = MaterialOut::with(['item.category', 'projectRelation', 'user'])
            ->whereHas('item.category', fn($q) => $q->where('name', 'like', '%Cat%'));

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($request->filled('project')) $query->where('project', $request->project);
        if ($request->filled('pic')) $query->where('pic', $request->pic);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('project', 'like', "%{$s}%")
                  ->orWhere('pic', 'like', "%{$s}%")
                  ->orWhereHas('item', fn($iq) => $iq->where('name', 'like', "%{$s}%")->orWhere('code_item', 'like', "%{$s}%"));
            });
        }

        $totalQty = (clone $query)->sum('quantity');
        $totalTrans = (clone $query)->count();
        $totalProjects = (clone $query)->distinct('project')->count('project');

        $projectsList = MaterialOut::whereHas('item.category', fn($q) => $q->where('name', 'like', '%Cat%'))->distinct('project')->pluck('project');
        $picsList = MaterialOut::whereHas('item.category', fn($q) => $q->where('name', 'like', '%Cat%'))->distinct('pic')->pluck('pic');

        $logs = $query->latest('date')->latest('id')->paginate(25)->withQueryString();

        return view('pages.reports.cat-painting', [
            'title' => 'Laporan Pengeluaran Cat',
            'logs' => $logs,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalQty' => $totalQty,
            'totalTrans' => $totalTrans,
            'totalProjects' => $totalProjects,
            'projectsList' => $projectsList,
            'picsList' => $picsList,
        ]);
    }

    public function exportCatPaintingExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $query = MaterialOut::with(['item.category', 'projectRelation'])
            ->whereHas('item.category', fn($q) => $q->where('name', 'like', '%Cat%'));

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($request->filled('project')) $query->where('project', $request->project);
        if ($request->filled('pic')) $query->where('pic', $request->pic);

        $records = $query->orderBy('date', 'asc')->get();

        return app(MaterialOutExcelExportService::class)->export(
            records: $records,
            startDate: $startDate,
            endDate: $endDate,
            module: 'Cat',
            customTitle: 'LAPORAN PENGELUARAN CAT GUDANG'
        );
    }

    public function exportCatPaintingPdf(Request $request)
    {
        $this->preparePdfEnvironment();

        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $query = MaterialOut::with(['item.category', 'projectRelation'])
            ->whereHas('item.category', fn($q) => $q->where('name', 'like', '%Cat%'));

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($request->filled('project')) $query->where('project', $request->project);
        if ($request->filled('pic')) $query->where('pic', $request->pic);

        $records = $query->latest('date')->latest('id')->get();
        $company = CompanyProfile::getProfile();

        $pdf = Pdf::loadView('pages.reports.pdf.cat-painting', [
            'company' => $company,
            'records' => $records,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Pengeluaran_Cat_' . date('Ymd_His') . '.pdf');
    }

    /**
     * 3. Laporan Pengeluaran Secondary Part (Prompt Section 6)
     */
    public function secondaryPartReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $query = MaterialOut::with(['item.category', 'projectRelation', 'user'])
            ->whereHas('item.category', fn($q) => $q->where('name', 'Secondary Part'));

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($request->filled('project')) $query->where('project', $request->project);
        if ($request->filled('pic')) $query->where('pic', $request->pic);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function($q) use ($s) {
                $q->where('project', 'like', "%{$s}%")
                  ->orWhere('pic', 'like', "%{$s}%")
                  ->orWhereHas('item', fn($iq) => $iq->where('name', 'like', "%{$s}%")->orWhere('code_item', 'like', "%{$s}%"));
            });
        }

        $totalQty = (clone $query)->sum('quantity');
        $totalTrans = (clone $query)->count();
        $totalProjects = (clone $query)->distinct('project')->count('project');

        $projectsList = MaterialOut::whereHas('item.category', fn($q) => $q->where('name', 'Secondary Part'))->distinct('project')->pluck('project');
        $picsList = MaterialOut::whereHas('item.category', fn($q) => $q->where('name', 'Secondary Part'))->distinct('pic')->pluck('pic');

        $logs = $query->latest('date')->latest('id')->paginate(25)->withQueryString();

        return view('pages.reports.secondary-part', [
            'title' => 'Laporan Pengeluaran Secondary Part',
            'logs' => $logs,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalQty' => $totalQty,
            'totalTrans' => $totalTrans,
            'totalProjects' => $totalProjects,
            'projectsList' => $projectsList,
            'picsList' => $picsList,
        ]);
    }

    public function exportSecondaryPartExcel(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $query = MaterialOut::with(['item.category', 'projectRelation'])
            ->whereHas('item.category', fn($q) => $q->where('name', 'Secondary Part'));

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($request->filled('project')) $query->where('project', $request->project);
        if ($request->filled('pic')) $query->where('pic', $request->pic);

        $records = $query->orderBy('date', 'asc')->get();

        return app(MaterialOutExcelExportService::class)->export(
            records: $records,
            startDate: $startDate,
            endDate: $endDate,
            module: 'Secondary Part',
            customTitle: 'LAPORAN PENGELUARAN SECONDARY PART GUDANG'
        );
    }

    public function exportSecondaryPartPdf(Request $request)
    {
        $this->preparePdfEnvironment();

        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        $query = MaterialOut::with(['item.category', 'projectRelation'])
            ->whereHas('item.category', fn($q) => $q->where('name', 'Secondary Part'));

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($request->filled('project')) $query->where('project', $request->project);
        if ($request->filled('pic')) $query->where('pic', $request->pic);

        $records = $query->latest('date')->latest('id')->get();
        $company = CompanyProfile::getProfile();

        $pdf = Pdf::loadView('pages.reports.pdf.secondary-part', [
            'company' => $company,
            'records' => $records,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Pengeluaran_Secondary_Part_' . date('Ymd_His') . '.pdf');
    }

    /**
     * 4. Export PDF Consumable (Prompt Section 6)
     */
    public function exportConsumablePdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(180);

        $latestDateStr = MaterialOut::max('date') ?? date('Y-m-d');
        $endDate = $request->get('end_date', $latestDateStr);
        $startDate = $request->get('start_date', Carbon::parse($endDate)->subDays(30)->format('Y-m-d'));

        $selectedItemIds = (array) $request->get('item_ids', []);
        if (empty($selectedItemIds) && $request->filled('item_id')) {
            $selectedItemIds = [$request->item_id];
        }
        $selectedItemIds = array_values(array_filter(array_map('intval', $selectedItemIds)));

        $query = MaterialOut::with(['item.category', 'projectRelation'])
            ->whereHas('item.category', fn($q) => $q->where('name', 'Consumable'));

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if (!empty($selectedItemIds)) $query->whereIn('item_id', $selectedItemIds);
        if ($request->filled('project')) $query->where('project', $request->project);
        if ($request->filled('pic')) $query->where('pic', $request->pic);

        $totalRecordsCount = (clone $query)->count();
        $totalQty = (clone $query)->sum('quantity');

        // Aggregation per item for Executive Summary Table
        $allLogs = $query->get();
        $groupedLogs = $allLogs->groupBy('item_id');
        $daysSpan = max(1, Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate)) + 1);

        $summaryTableData = [];
        foreach ($groupedLogs as $itemId => $itemLogs) {
            $itemObj = $itemLogs->first()->item;
            $itemName = $itemObj ? $itemObj->name : ('Item #' . $itemId);
            $unit = $itemLogs->first()->unit ?? ($itemObj ? $itemObj->unit : 'PCS');
            $itemTotalQty = $itemLogs->sum('quantity');
            $itemTransCount = $itemLogs->count();
            $avgPerDay = round($itemTotalQty / $daysSpan, 1);

            $topPic = $itemLogs->groupBy('pic')->sortByDesc(fn($g) => $g->sum('quantity'))->keys()->first() ?? '-';
            $topProject = $itemLogs->groupBy('project')->sortByDesc(fn($g) => $g->sum('quantity'))->keys()->first() ?? '-';

            if ($avgPerDay > 50 || $itemTotalQty > 1000) {
                $keterangan = "Akumulasi Tinggi ({$itemTransCount}x transaksi). Terbanyak untuk {$topProject} ({$topPic}).";
            } elseif ($avgPerDay > 10 || $itemTotalQty > 200) {
                $keterangan = "Penggunaan Sedang ({$itemTransCount}x transaksi). Untuk Project {$topProject}.";
            } else {
                $keterangan = "Penggunaan Normal ({$itemTransCount}x transaksi). PIC: {$topPic}.";
            }

            $summaryTableData[] = [
                'code_item' => $itemObj ? $itemObj->code_item : '-',
                'item_name' => $itemName,
                'unit' => strtoupper($unit),
                'total_qty' => (float) $itemTotalQty,
                'avg_per_day' => $avgPerDay,
                'trans_count' => $itemTransCount,
                'time_span' => "{$daysSpan} HARI",
                'keterangan' => $keterangan,
            ];
        }

        // Sort descending by total quantity
        usort($summaryTableData, fn($a, $b) => $b['total_qty'] <=> $a['total_qty']);

        // Recent transaction rows sample (up to 100 rows for detailed table in PDF)
        $recentLogs = (clone $query)->latest('date')->latest('id')->take(100)->get();

        $company = CompanyProfile::getProfile();

        $pdf = Pdf::loadView('pages.reports.pdf.consumable', [
            'company' => $company,
            'summaryTableData' => $summaryTableData,
            'recentLogs' => $recentLogs,
            'totalRecordsCount' => $totalRecordsCount,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'daysSpan' => $daysSpan,
            'totalQty' => $totalQty,
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Pemakaian_Consumable_' . date('Ymd_His') . '.pdf');
    }

    /**
     * 3. Laporan Material Rusak / Reject (Report / Reject Material & Rekapitulasi)
     */
    public function rejectMaterialReport(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $status = $request->get('disposition_status', 'all');
        $damageType = $request->get('damage_type', 'all');
        $disposition = $request->get('disposition', 'all');
        $supplier = $request->get('supplier', '');
        $categoryId = $request->get('category_id', 'all');

        $query = MaterialReject::with(['item.category', 'materialIn', 'reportedBy', 'inspectedBy', 'resolvedBy']);

        if (!empty($startDate)) {
            $query->whereDate('date', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('date', '<=', $endDate);
        }
        if ($status !== 'all' && !empty($status)) {
            $query->where('disposition_status', $status);
        }
        if ($damageType !== 'all' && !empty($damageType)) {
            $query->where('damage_type', $damageType);
        }
        if ($disposition !== 'all' && !empty($disposition)) {
            $query->where('disposition', $disposition);
        }
        if (!empty($supplier)) {
            $query->where('supplier', $supplier);
        }
        if ($categoryId !== 'all' && !empty($categoryId)) {
            $selectedCat = Category::find($categoryId);
            if ($selectedCat && $selectedCat->isCatUmbrella()) {
                $catIds = Category::getCatCategoryIds();
                $query->whereHas('item', fn($iq) => $iq->whereIn('category_id', $catIds));
            } else {
                $query->whereHas('item', fn($iq) => $iq->where('category_id', $categoryId));
            }
        }

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('reference_number', 'like', "%{$s}%")
                    ->orWhere('supplier', 'like', "%{$s}%")
                    ->orWhere('po_number', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhere('resolution_notes', 'like', "%{$s}%")
                    ->orWhereHas('item', fn($iq) => $iq->where('name', 'like', "%{$s}%")->orWhere('code_item', 'like', "%{$s}%"));
            });
        }

        $totalCount = (clone $query)->count();
        $totalQty = (clone $query)->sum('quantity');
        $totalPending = (clone $query)->where('disposition_status', 'pending')->count();
        $totalInProgress = (clone $query)->where('disposition_status', 'in_progress')->count();
        $totalCompleted = (clone $query)->where('disposition_status', 'completed')->count();

        $suppliersList = MaterialReject::select('supplier')->distinct()->whereNotNull('supplier')->where('supplier', '!=', '')->orderBy('supplier')->pluck('supplier');
        $damageTypesList = MaterialReject::select('damage_type')->distinct()->whereNotNull('damage_type')->where('damage_type', '!=', '')->orderBy('damage_type')->pluck('damage_type');
        $categoriesList = Category::orderBy('name')->get();

        $records = $query->latest('date')->latest('id')->paginate(25)->withQueryString();

        if (auth()->check()) {
            ActivityLog::record(
                'Melihat Laporan Material Rusak / Reject',
                'Membuka modul laporan rekapitulasi data material rusak/reject gudang.',
                'Laporan Reject'
            );
        }

        return view('pages.reports.reject-material', [
            'title' => 'Laporan Material Rusak / Reject (BAP & Disposisi)',
            'records' => $records,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'status' => $status,
            'damageType' => $damageType,
            'disposition' => $disposition,
            'supplier' => $supplier,
            'categoryId' => $categoryId,
            'totalCount' => $totalCount,
            'totalQty' => $totalQty,
            'totalPending' => $totalPending,
            'totalInProgress' => $totalInProgress,
            'totalCompleted' => $totalCompleted,
            'suppliersList' => $suppliersList,
            'damageTypesList' => $damageTypesList,
            'categoriesList' => $categoriesList,
        ]);
    }

    /**
     * Export Excel Rekapitulasi Material Rusak / Reject
     */
    public function exportRejectMaterialExcel(Request $request)
    {
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $status = $request->get('disposition_status', 'all');
        $damageType = $request->get('damage_type', 'all');
        $disposition = $request->get('disposition', 'all');
        $supplier = $request->get('supplier', '');

        $query = MaterialReject::with(['item.category', 'materialIn', 'reportedBy', 'inspectedBy', 'resolvedBy']);

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($status !== 'all' && !empty($status)) $query->where('disposition_status', $status);
        if ($damageType !== 'all' && !empty($damageType)) $query->where('damage_type', $damageType);
        if ($disposition !== 'all' && !empty($disposition)) $query->where('disposition', $disposition);
        if (!empty($supplier)) $query->where('supplier', $supplier);

        $records = $query->latest('date')->latest('id')->get();
        $company = CompanyProfile::getProfile();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Material Reject');

        // Header Perusahaan
        $sheet->setCellValue('A1', strtoupper($company->name ?? 'PT MUDALAYA ENERGY INDONESIA'));
        $sheet->setCellValue('A2', 'LAPORAN REKAPITULASI MATERIAL RUSAK / REJECT & RETUR SUPPLIER');
        $periodText = 'Periode: ' . ($startDate ? format_date($startDate) : 'Semua') . ' s/d ' . ($endDate ? format_date($endDate) : 'Sekarang');
        $sheet->setCellValue('A3', $periodText . ' | Total Kasus: ' . $records->count() . ' | Total Qty Rusak: ' . $records->sum('quantity'));

        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');
        $sheet->mergeCells('A3:N3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('275538'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('555555'));

        // Header Tabel
        $headers = [
            'A5' => 'NO',
            'B5' => 'TGL REJECT',
            'C5' => 'NO REGISTRASI BAP',
            'D5' => 'NO PO',
            'E5' => 'SUPPLIER / VENDOR',
            'F5' => 'KODE BARANG',
            'G5' => 'NAMA BARANG',
            'H5' => 'KATEGORI',
            'I5' => 'QTY RUSAK',
            'J5' => 'SATUAN',
            'K5' => 'JENIS KERUSAKAN',
            'L5' => 'STATUS DISPOSISI',
            'M5' => 'TINDAK LANJUT / DISPOSISI',
            'N5' => 'URAIAN CACAT / CATATAN',
        ];

        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '275538']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '1E3F2B']]],
        ];
        $sheet->getStyle('A5:N5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(25);

        $row = 6;
        foreach ($records as $idx => $item) {
            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $item->date ? format_date($item->date) : '-');
            $sheet->setCellValue('C' . $row, $item->reference_number);
            $sheet->setCellValue('D' . $row, $item->po_number ?? ($item->materialIn->po_number ?? '-'));
            $sheet->setCellValue('E' . $row, $item->supplier);
            $sheet->setCellValue('F' . $row, $item->item->code_item ?? '-');
            $sheet->setCellValue('H' . $row, $item->item->category->name ?? 'Consumable');
            $sheet->setCellValue('G' . $row, $item->item->name ?? '-');
            $sheet->setCellValue('I' . $row, (float) $item->quantity);
            $sheet->setCellValue('J' . $row, strtoupper($item->unit));
            $sheet->setCellValue('K' . $row, $item->damage_type);
            $sheet->setCellValue('L' . $row, $item->status_label);
            $sheet->setCellValue('M' . $row, $item->disposition_label);
            $sheet->setCellValue('N' . $row, $item->description . ($item->resolution_notes ? ' | Catatan: ' . $item->resolution_notes : ''));

            if ($row % 2 == 1) {
                $sheet->getStyle('A' . $row . ':N' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
            }

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('L' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        if ($row > 6) {
            $sheet->getStyle('A6:N' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D1D5DB');
        }

        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (auth()->check()) {
            ActivityLog::record(
                'Export Excel Laporan Material Reject',
                "Mengunduh file Excel rekapitulasi data material reject ({$records->count()} baris data).",
                'Laporan Reject'
            );
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Material_Reject_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export PDF Rekapitulasi Material Rusak / Reject
     */
    public function exportRejectMaterialPdf(Request $request)
    {
        $this->preparePdfEnvironment();

        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', date('Y-m-d'));
        $status = $request->get('disposition_status', 'all');
        $damageType = $request->get('damage_type', 'all');
        $disposition = $request->get('disposition', 'all');
        $supplier = $request->get('supplier', '');

        $query = MaterialReject::with(['item.category', 'materialIn', 'reportedBy', 'inspectedBy', 'resolvedBy']);

        if (!empty($startDate)) $query->whereDate('date', '>=', $startDate);
        if (!empty($endDate)) $query->whereDate('date', '<=', $endDate);
        if ($status !== 'all' && !empty($status)) $query->where('disposition_status', $status);
        if ($damageType !== 'all' && !empty($damageType)) $query->where('damage_type', $damageType);
        if ($disposition !== 'all' && !empty($disposition)) $query->where('disposition', $disposition);
        if (!empty($supplier)) $query->where('supplier', $supplier);

        $records = $query->latest('date')->latest('id')->get();
        $company = CompanyProfile::getProfile();

        if (auth()->check()) {
            ActivityLog::record(
                'Export PDF Laporan Material Reject',
                "Mengunduh file PDF rekapitulasi data material reject ({$records->count()} baris data).",
                'Laporan Reject'
            );
        }

        $pdf = Pdf::loadView('pages.reports.pdf.reject-material', [
            'company' => $company,
            'records' => $records,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalQty' => $records->sum('quantity'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Laporan_Material_Reject_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Export Dokumen Resmi Berita Acara Pengembalian / Reject Material (BAP) PDF
     */
    public function exportRejectMaterialBapPdf($id)
    {
        $this->preparePdfEnvironment();

        $reject = MaterialReject::with(['item.category', 'materialIn', 'reportedBy', 'inspectedBy', 'resolvedBy'])->findOrFail($id);
        $company = CompanyProfile::getProfile();

        if (auth()->check()) {
            ActivityLog::record(
                'Cetak BAP Material Reject',
                "Mencetak Berita Acara Pengembalian / Reject Material (BAP) untuk dokumen {$reject->reference_number} ({$reject->item->name})",
                'BAP Reject'
            );
        }

        $pdf = Pdf::loadView('pages.reports.pdf.berita-acara-reject', [
            'reject' => $reject,
            'company' => $company,
        ])->setPaper('a4', 'portrait');

        return $pdf->download('BAP_Reject_' . str_replace('/', '_', $reject->reference_number) . '.pdf');
    }

    /**
     * Compute slow moving & dead stock dataset for items.
     *
     * @param Request $request
     * @return array
     */
    private function getSlowMovingData(Request $request): array
    {
        $now = Carbon::now();
        $date30Ago = $now->copy()->subDays(30)->toDateString();
        $date90Ago = $now->copy()->subDays(90)->toDateString();

        // 1. Query outbound summary per item
        $lastOutSub = DB::table('material_outs')
            ->select('item_id', DB::raw('MAX(date) as last_out_date'))
            ->groupBy('item_id')
            ->pluck('last_out_date', 'item_id');

        $out30Sub = DB::table('material_outs')
            ->select('item_id', DB::raw('SUM(quantity) as qty_30d'))
            ->whereDate('date', '>=', $date30Ago)
            ->groupBy('item_id')
            ->pluck('qty_30d', 'item_id');

        $out90Sub = DB::table('material_outs')
            ->select('item_id', DB::raw('SUM(quantity) as qty_90d'))
            ->whereDate('date', '>=', $date90Ago)
            ->groupBy('item_id')
            ->pluck('qty_90d', 'item_id');

        // 2. Query inbound latest date as fallback
        $lastInSub = DB::table('material_ins')
            ->select('item_id', DB::raw('MAX(date) as last_in_date'))
            ->groupBy('item_id')
            ->pluck('last_in_date', 'item_id');

        // 3. Load all items with category
        $itemsQuery = Item::with('category');
        if ($request->filled('category_id')) {
            $itemsQuery->where('category_id', $request->category_id);
        }
        if ($request->filled('search')) {
            $s = trim($request->search);
            $itemsQuery->where(function($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code_item', 'like', "%{$s}%");
            });
        }

        $items = $itemsQuery->get();

        $kpi = [
            'dead_stock_count' => 0,
            'dead_stock_units' => 0,
            'slow_moving_count' => 0,
            'slow_moving_units' => 0,
            'medium_moving_count' => 0,
            'medium_moving_units' => 0,
            'fast_moving_count' => 0,
            'fast_moving_units' => 0,
            'out_of_stock_count' => 0,
            'total_items' => $items->count(),
        ];

        $analyzedItems = $items->map(function ($item) use ($now, $lastOutSub, $out30Sub, $out90Sub, $lastInSub, &$kpi) {
            $lastOut = $lastOutSub[$item->id] ?? null;
            $lastIn = $lastInSub[$item->id] ?? null;
            $lastActivity = $lastOut ?: ($lastIn ?: $item->created_at);

            $lastActivityCarbon = $lastActivity ? Carbon::parse($lastActivity) : $item->created_at;
            $daysInactive = $lastActivityCarbon ? (int) $lastActivityCarbon->diffInDays($now) : 0;

            $qty30 = (float) ($out30Sub[$item->id] ?? 0);
            $qty90 = (float) ($out90Sub[$item->id] ?? 0);

            $stock = (float) $item->stock;

            $slowMovingThreshold = CompanyProfile::getSlowMovingDays();
            $deadStockThreshold = CompanyProfile::getDeadStockDays();

            if ($stock <= 0) {
                $statusKey = 'out_of_stock';
                $statusLabel = 'Stok Kosong';
                $badgeClass = 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300';
                $recommendation = 'Restock apabila material masih dibutuhkan';
                $kpi['out_of_stock_count']++;
            } elseif ($daysInactive > $deadStockThreshold) {
                $statusKey = 'dead_stock';
                $statusLabel = "Dead Stock (> {$deadStockThreshold} Hari)";
                $badgeClass = 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300';
                $recommendation = 'Lelang / cuci gudang, retur ke supplier, atau scrap';
                $kpi['dead_stock_count']++;
                $kpi['dead_stock_units'] += $stock;
            } elseif ($daysInactive > $slowMovingThreshold) {
                $statusKey = 'slow_moving';
                $statusLabel = "Slow Moving (" . ($slowMovingThreshold + 1) . "-{$deadStockThreshold} Hari)";
                $badgeClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300';
                $recommendation = 'Prioritaskan pemakaian project & tunda pembelian baru';
                $kpi['slow_moving_count']++;
                $kpi['slow_moving_units'] += $stock;
            } elseif ($daysInactive >= 31) {
                $statusKey = 'medium_moving';
                $statusLabel = "Medium Moving (31-{$slowMovingThreshold} Hari)";
                $badgeClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300';
                $recommendation = 'Pertahankan level stok normal sesuai min-stock';
                $kpi['medium_moving_count']++;
                $kpi['medium_moving_units'] += $stock;
            } else {
                $statusKey = 'fast_moving';
                $statusLabel = 'Fast Moving (≤ 30 Hari)';
                $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300';
                $recommendation = 'Jaga kontinuitas pasokan & perhatikan safety stock';
                $kpi['fast_moving_count']++;
                $kpi['fast_moving_units'] += $stock;
            }

            return (object) [
                'id' => $item->id,
                'code_item' => $item->code_item,
                'name' => $item->name,
                'category_name' => $item->category->name ?? 'General',
                'category_id' => $item->category_id,
                'stock' => $stock,
                'unit' => $item->unit,
                'min_stock' => $item->min_stock,
                'expiry_date' => $item->expiry_date,
                'expiry_status' => $item->expiry_status,
                'last_out_date' => $lastOut,
                'last_activity_date' => $lastActivity ? Carbon::parse($lastActivity)->format('Y-m-d') : null,
                'days_inactive' => $daysInactive,
                'out_qty_30d' => $qty30,
                'out_qty_90d' => $qty90,
                'status_key' => $statusKey,
                'status_label' => $statusLabel,
                'badge_class' => $badgeClass,
                'recommendation' => $recommendation,
            ];
        });

        // Filter by Status if requested
        if ($request->filled('status') && $request->status !== 'all') {
            $analyzedItems = $analyzedItems->where('status_key', $request->status)->values();
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'days_desc');
        switch ($sortBy) {
            case 'days_asc':
                $analyzedItems = $analyzedItems->sortBy('days_inactive')->values();
                break;
            case 'stock_desc':
                $analyzedItems = $analyzedItems->sortByDesc('stock')->values();
                break;
            case 'stock_asc':
                $analyzedItems = $analyzedItems->sortBy('stock')->values();
                break;
            case 'name_asc':
                $analyzedItems = $analyzedItems->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)->values();
                break;
            case 'days_desc':
            default:
                $analyzedItems = $analyzedItems->sortByDesc('days_inactive')->values();
                break;
        }

        return [
            'items' => $analyzedItems,
            'kpi' => $kpi,
        ];
    }

    /**
     * Display Slow-Moving & Dead Stock Analysis Report
     */
    public function slowMovingStockReport(Request $request)
    {
        $categories = Category::all();
        $data = $this->getSlowMovingData($request);
        $company = CompanyProfile::getProfile();

        $perPage = (int) $request->get('per_page', 25);
        if ($perPage <= 0) $perPage = 25;

        $page = (int) $request->get('page', 1);
        $total = $data['items']->count();
        $pagedItems = new \Illuminate\Pagination\LengthAwarePaginator(
            $data['items']->forPage($page, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('pages.reports.slow-moving-stock', [
            'items' => $pagedItems,
            'kpi' => $data['kpi'],
            'categories' => $categories,
            'company' => $company,
            'slowMovingThreshold' => CompanyProfile::getSlowMovingDays(),
            'deadStockThreshold' => CompanyProfile::getDeadStockDays(),
            'selectedCategory' => $request->get('category_id', ''),
            'selectedStatus' => $request->get('status', 'all'),
            'search' => $request->get('search', ''),
            'sortBy' => $request->get('sort_by', 'days_desc'),
            'perPage' => $perPage,
        ]);
    }

    /**
     * Export Excel Slow-Moving & Dead Stock Report
     */
    public function exportSlowMovingStockExcel(Request $request)
    {
        $data = $this->getSlowMovingData($request);
        $records = $data['items'];
        $company = CompanyProfile::getProfile();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Slow & Dead Stock');

        // Kop Perusahaan
        $sheet->setCellValue('A1', strtoupper($company->name ?? 'PT MUDALAYA ENERGY INDONESIA'));
        $sheet->setCellValue('A2', 'LAPORAN ANALISIS BARANG LAMBAT BERGERAK (SLOW-MOVING & DEAD STOCK)');
        $sheet->setCellValue('A3', 'Tanggal Analisis: ' . date('d/m/Y H:i') . ' WIB | Total Item: ' . $records->count());

        $sheet->mergeCells('A1:L1');
        $sheet->mergeCells('A2:L2');
        $sheet->mergeCells('A3:L3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('1E3A8A');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('6B7280');

        // Headers
        $headers = [
            'A5' => 'No',
            'B5' => 'Kode Barang',
            'C5' => 'Nama Barang',
            'D5' => 'Kategori',
            'E5' => 'Stok Saat Ini',
            'F5' => 'Satuan',
            'G5' => 'Tgl Aktivitas Terakhir',
            'H5' => 'Hari Inaktif',
            'I5' => 'Status Pergerakan',
            'J5' => 'Keluar 30 Hari',
            'K5' => 'Keluar 90 Hari',
            'L5' => 'Rekomendasi Tindakan',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $sheet->getStyle('A5:L5')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A5:L5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A');
        $sheet->getStyle('A5:L5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $row = 6;
        foreach ($records as $index => $item) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->code_item ?: '-');
            $sheet->setCellValue('C' . $row, $item->name);
            $sheet->setCellValue('D' . $row, $item->category_name);
            $sheet->setCellValue('E' . $row, $item->stock);
            $sheet->setCellValue('F' . $row, $item->unit);
            $sheet->setCellValue('G' . $row, $item->last_activity_date ? date('d/m/Y', strtotime($item->last_activity_date)) : 'Belum Ada Transaksi');
            $sheet->setCellValue('H' . $row, $item->days_inactive);
            $sheet->setCellValue('I' . $row, $item->status_label);
            $sheet->setCellValue('J' . $row, $item->out_qty_30d);
            $sheet->setCellValue('K' . $row, $item->out_qty_90d);
            $sheet->setCellValue('L' . $row, $item->recommendation);

            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Highlight Dead Stock with soft red, Slow moving with soft amber
            if ($item->status_key === 'dead_stock') {
                $sheet->getStyle('I' . $row)->getFont()->setBold(true)->getColor()->setRGB('991B1B');
            } elseif ($item->status_key === 'slow_moving') {
                $sheet->getStyle('I' . $row)->getFont()->setBold(true)->getColor()->setRGB('92400E');
            }

            $row++;
        }

        if ($row > 6) {
            $sheet->getStyle('A5:L' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D1D5DB');
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (auth()->check()) {
            ActivityLog::record(
                'Export Excel Analisis Slow & Dead Stock',
                "Mengunduh file Excel analisis pergerakan stok ({$records->count()} baris data).",
                'Laporan Stok'
            );
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'Analisis_Slow_Dead_Stock_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Export PDF Slow-Moving & Dead Stock Report
     */
    public function exportSlowMovingStockPdf(Request $request)
    {
        $this->preparePdfEnvironment();

        $data = $this->getSlowMovingData($request);
        $records = $data['items'];
        $kpi = $data['kpi'];
        $company = CompanyProfile::getProfile();

        if (auth()->check()) {
            ActivityLog::record(
                'Export PDF Analisis Slow & Dead Stock',
                "Mengunduh file PDF analisis pergerakan barang ({$records->count()} baris data).",
                'Laporan Stok'
            );
        }

        $pdf = Pdf::loadView('pages.reports.pdf.slow-moving-stock', [
            'company' => $company,
            'records' => $records,
            'kpi' => $kpi,
            'slowMovingThreshold' => CompanyProfile::getSlowMovingDays(),
            'deadStockThreshold' => CompanyProfile::getDeadStockDays(),
            'statusFilter' => $request->get('status', 'all'),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Analisis_Slow_Dead_Stock_' . date('Ymd_His') . '.pdf');
    }
}
