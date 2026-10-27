<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\MaterialReject;
use App\Models\Project;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isPpic()) {
            return redirect()->route('items.index');
        }

        if ($user->isQc()) {
            return redirect()->route('material-in.index');
        }

        $totalItems = Item::count();
        $storage1Count = Item::inStorage(Item::STORAGE_1)->count();
        $storage2Count = Item::inStorage(Item::STORAGE_2)->count();

        $totalActiveTonnage = Project::getTotalActiveTonnage();

        // Pemisahan Kritis: Stok Habis (0) dan Stok Menipis (<= min_stock otomatis berbasis tonase project)
        $outOfStockCount = Item::where('stock', '<=', 0)->count();
        $lowStockOnlyCount = Item::whereRaw('stock <= COALESCE(requirement_per_ton * ?, min_stock)', [$totalActiveTonnage])->where('stock', '>', 0)->count();
        $lowStockCount = $outOfStockCount + $lowStockOnlyCount;

        $allItems = Item::select(['id', 'name', 'stock', 'unit', 'category_id', 'manufacturing_date', 'created_at'])->get();
        $totalCategories = Category::count();
        $canViewPrices = $user->canViewPrices();
        $totalAssetValue = $canViewPrices ? (float) Item::sum(DB::raw('COALESCE(stock, 0) * COALESCE(unit_price, 0)')) : 0;
        $pricedItemsCount = $canViewPrices ? (int) Item::where('unit_price', '>', 0)->count() : 0;

        $recentLogs = StockLog::with(['item.category', 'item.unitRelation', 'user'])
            ->latest()
            ->take(8)
            ->get();

        $criticalItems = Item::with(['category', 'unitRelation'])
            ->whereRaw('stock <= COALESCE(requirement_per_ton * ?, min_stock)', [$totalActiveTonnage])
            ->orderBy('stock', 'asc')
            ->take(6)
            ->get();
        $lowStockItems = $criticalItems;

        // =========================================================================
        // Aktivitas Harian Gudang (Real-Time Daily Movement & Operational Pulse)
        // =========================================================================
        $todayStr = now()->format('Y-m-d');
        $todayInCount = MaterialIn::whereDate('date', $todayStr)->count();
        $todayInQty = (float) MaterialIn::whereDate('date', $todayStr)->sum('quantity');
        $todayOutCount = MaterialOut::whereDate('date', $todayStr)->count();
        $todayOutQty = (float) MaterialOut::whereDate('date', $todayStr)->sum('quantity');
        $todayLogsCount = StockLog::whereDate('created_at', $todayStr)->count();

        $latestInDate = MaterialIn::max('date') ?? $todayStr;
        $latestOutDate = MaterialOut::max('date') ?? $todayStr;
        $latestOpDate = max($latestInDate, $latestOutDate);
        $recentDayInCount = MaterialIn::whereDate('date', $latestOpDate)->count();
        $recentDayOutCount = MaterialOut::whereDate('date', $latestOpDate)->count();

        $statMonth = (int) now()->month;
        $statYear = (int) now()->year;
        $thisMonthInCount = MaterialIn::whereMonth('date', $statMonth)->whereYear('date', $statYear)->count();
        $thisMonthOutCount = MaterialOut::whereMonth('date', $statMonth)->whereYear('date', $statYear)->count();
        if ($thisMonthInCount === 0 && $thisMonthOutCount === 0) {
            $latestCarbonOp = Carbon::parse($latestOpDate);
            $statMonth = (int) $latestCarbonOp->format('m');
            $statYear = (int) $latestCarbonOp->format('Y');
            $thisMonthInCount = MaterialIn::whereMonth('date', $statMonth)->whereYear('date', $statYear)->count();
            $thisMonthOutCount = MaterialOut::whereMonth('date', $statMonth)->whereYear('date', $statYear)->count();
            $monthLabel = $latestCarbonOp->translatedFormat('F Y');
        } else {
            $monthLabel = now()->translatedFormat('F Y');
        }

        // Antrean Incoming Inspection QC (Material Masuk menunggu pemeriksaan kualitas)
        $pendingQcCount = MaterialIn::where(function ($q) {
            $q->whereNull('qc_status')->orWhere('qc_status', 'pending_qc');
        })->count();

        $pendingQcItems = MaterialIn::with(['item.category', 'unitRelation'])
            ->where(function ($q) {
                $q->whereNull('qc_status')->orWhere('qc_status', 'pending_qc');
            })
            ->latest('date')
            ->take(5)
            ->get();

        // Material Reject / Rusak (Menunggu Disposisi: retur vendor, klaim garansi, karantina)
        $pendingRejectCount = MaterialReject::where('disposition_status', '!=', 'completed')->count();
        $totalRejectCount = MaterialReject::count();
        $pendingRejectList = MaterialReject::with(['item.category', 'unitRelation'])
            ->where('disposition_status', '!=', 'completed')
            ->latest('date')
            ->take(5)
            ->get();

        if ($pendingRejectList->isEmpty()) {
            $pendingRejectList = MaterialReject::with(['item.category', 'unitRelation'])
                ->latest('date')
                ->take(5)
                ->get();
        }

        // Fast-Moving Items: Top 4 barang paling sering dan banyak keluar bulan ini
        $startOfMonth = now()->startOfMonth();
        $fastMovingItems = MaterialOut::where('date', '>=', $startOfMonth)
            ->whereNotNull('item_id')
            ->select('item_id', DB::raw('SUM(quantity) as total_out_qty'), DB::raw('COUNT(*) as total_transactions'))
            ->groupBy('item_id')
            ->orderByDesc('total_out_qty')
            ->with('item.category')
            ->take(4)
            ->get();

        if ($fastMovingItems->count() < 3) {
            $fastMovingItems = MaterialOut::whereNotNull('item_id')
                ->select('item_id', DB::raw('SUM(quantity) as total_out_qty'), DB::raw('COUNT(*) as total_transactions'))
                ->groupBy('item_id')
                ->orderByDesc('total_out_qty')
                ->with('item.category')
                ->take(4)
                ->get();
        }

        // FEFO Cat Shelf-Life Calculations (Dynamic based on CompanyProfile cat_shelf_life_months and cat_fefo_alert_days)
        $shelfLifeMonths = CompanyProfile::getCatShelfLifeMonths();
        $alertDays = CompanyProfile::getCatFefoAlertDays();

        $today = now();
        $expiredDate = now()->subMonths($shelfLifeMonths);
        $warningDate = (clone $expiredDate)->addDays($alertDays);

        $paintItemQuery = Item::whereHas('category', fn($q) => $q->where('name', 'like', '%Cat%'))
            ->whereNotNull('manufacturing_date');

        $fefoExpiringCount = (clone $paintItemQuery)
            ->whereBetween('manufacturing_date', [$expiredDate->format('Y-m-d'), $warningDate->format('Y-m-d')])
            ->count();

        $fefoExpiredCount = (clone $paintItemQuery)
            ->where('manufacturing_date', '<', $expiredDate->format('Y-m-d'))
            ->count();

        $fefoItems = (clone $paintItemQuery)
            ->with(['category', 'unitRelation'])
            ->where('manufacturing_date', '<=', $warningDate->format('Y-m-d'))
            ->orderBy('manufacturing_date', 'asc')
            ->take(6)
            ->get();

        $categories = Category::withCount('items')->get();

        // Total Stock Calculation: Separate discrete count from continuous/decimal units
        $decimalUnits = ['KG', 'KILOGRAM', 'GRAM', 'GR', 'G', 'LITER', 'LTR', 'L', 'ML', 'MILILITER', 'METER', 'M', 'CM', 'CENTIMETER', 'MM', 'MILIMETER'];
        $totalDiscreteStock = 0;
        $totalDecimalStockByUnit = [];
        $categoryStockDetails = [];

        foreach ($categories as $cat) {
            $categoryStockDetails[$cat->id] = [
                'name' => $cat->name,
                'discrete' => 0,
                'decimals' => [],
                'formatted' => '0 Unit',
            ];
        }

        foreach ($allItems as $item) {
            $stk = (float) $item->stock;
            $unitStr = strtoupper(trim($item->unit ?? 'PCS'));
            $cId = $item->category_id;

            if (is_decimal_unit($unitStr)) {
                $totalDecimalStockByUnit[$unitStr] = ($totalDecimalStockByUnit[$unitStr] ?? 0) + $stk;
                if (isset($categoryStockDetails[$cId])) {
                    $categoryStockDetails[$cId]['decimals'][$unitStr] = ($categoryStockDetails[$cId]['decimals'][$unitStr] ?? 0) + $stk;
                }
            } else {
                $totalDiscreteStock += $stk;
                if (isset($categoryStockDetails[$cId])) {
                    $categoryStockDetails[$cId]['discrete'] += $stk;
                }
            }
        }

        foreach ($categoryStockDetails as $cId => &$csd) {
            $parts = [];
            if ($csd['discrete'] > 0) {
                $parts[] = number_format($csd['discrete'], 0, ',', '.') . ' Pcs/Unit';
            }
            if (!empty($csd['decimals'])) {
                $parts[] = format_unit_breakdown($csd['decimals']);
            }
            $csd['formatted'] = empty($parts) ? '0 Unit' : implode(' + ', $parts);
        }
        unset($csd);

        // Cat Umbrella Aggregation: Aggregate items count & stock from all paint subcategories into Cat
        $catIds = Category::getCatCategoryIds();
        $catUmbrella = $categories->first(fn($c) => $c->isCatUmbrella());
        if ($catUmbrella) {
            $catUmbrella->items_count = $categories->whereIn('id', $catIds)->sum('items_count');

            $catDiscreteTotal = 0;
            $catDecimalsTotal = [];
            foreach ($catIds as $subId) {
                if (isset($categoryStockDetails[$subId])) {
                    $catDiscreteTotal += $categoryStockDetails[$subId]['discrete'];
                    foreach ($categoryStockDetails[$subId]['decimals'] as $u => $v) {
                        $catDecimalsTotal[$u] = ($catDecimalsTotal[$u] ?? 0) + $v;
                    }
                }
            }
            if (isset($categoryStockDetails[$catUmbrella->id])) {
                $categoryStockDetails[$catUmbrella->id]['discrete'] = $catDiscreteTotal;
                $categoryStockDetails[$catUmbrella->id]['decimals'] = $catDecimalsTotal;
                $catParts = [];
                if ($catDiscreteTotal > 0) {
                    $catParts[] = number_format($catDiscreteTotal, 0, ',', '.') . ' Pcs/Unit';
                }
                if (!empty($catDecimalsTotal)) {
                    $catParts[] = format_unit_breakdown($catDecimalsTotal);
                }
                $categoryStockDetails[$catUmbrella->id]['formatted'] = empty($catParts) ? '0 Unit' : implode(' + ', $catParts);
            }
        }

        $totalDecimalStockBreakdown = format_unit_breakdown($totalDecimalStockByUnit);
        $totalStock = $totalDiscreteStock; // Primary discrete integer

        // Active Reference Date (Berdasarkan tanggal transaksi operasional terbaru di database, bukan tanggal upload file)
        $latestDateStr = MaterialOut::max('date') ?? date('Y-m-d');
        $latestCarbon = Carbon::parse($latestDateStr);
        $activeYear = (int) $latestCarbon->format('Y');

        // Timeframe Filters (Default: 1y = 1 Tahun)
        $lineRange = $request->get('line_range', '1y');
        $barRange = $request->get('bar_range', '1y');
        $itemOutRange = $request->get('item_out_range', '1y');
        $projectRange = $request->get('project_range', 'all');

        // ==========================================
        // 1. Line Chart Data (Mutasi Masuk vs Keluar)
        // Pemisahan Satuan Hitung (Discrete) vs Satuan Desimal (Decimal)
        // ==========================================
        $lineCategories = [];
        $monthlyInDiscrete = [];
        $monthlyOutDiscrete = [];
        $monthlyInDecimal = [];
        $monthlyOutDecimal = [];

        $driver = DB::getDriverName();
        $rawDateOut = ($driver === 'sqlite') ? "strftime('%Y-%m-%d', material_outs.date) as d" : "DATE(material_outs.date) as d";
        $rawDateIn = ($driver === 'sqlite') ? "strftime('%Y-%m-%d', stock_logs.created_at) as d" : "DATE(stock_logs.created_at) as d";
        $sqlUnitOut = "CASE WHEN UPPER(TRIM(COALESCE(items.unit, material_outs.unit, 'PCS'))) IN ('" . implode("','", $decimalUnits) . "') THEN 'decimal' ELSE 'discrete' END";
        $sqlUnitIn = "CASE WHEN UPPER(TRIM(COALESCE(items.unit, 'PCS'))) IN ('" . implode("','", $decimalUnits) . "') THEN 'decimal' ELSE 'discrete' END";

        if ($lineRange === '1w') {
            $startDate = $latestCarbon->copy()->subDays(6)->startOfDay();
            $endDate = $latestCarbon->copy()->endOfDay();

            $outsByDay = MaterialOut::leftJoin('items', 'material_outs.item_id', '=', 'items.id')
                ->selectRaw("$rawDateOut, $sqlUnitOut as unit_type, SUM(material_outs.quantity) as total")
                ->whereBetween('material_outs.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->groupBy('d', 'unit_type')
                ->get();

            $insByDay = StockLog::leftJoin('items', 'stock_logs.item_id', '=', 'items.id')
                ->selectRaw("$rawDateIn, $sqlUnitIn as unit_type, SUM(stock_logs.quantity) as total")
                ->where('stock_logs.type', 'in')
                ->whereBetween('stock_logs.created_at', [$startDate->format('Y-m-d 00:00:00'), $endDate->format('Y-m-d 23:59:59')])
                ->groupBy('d', 'unit_type')
                ->get();

            $outsMap = [];
            foreach ($outsByDay as $o) {
                $outsMap[$o->d . '_' . $o->unit_type] = (float) $o->total;
            }

            $insMap = [];
            foreach ($insByDay as $in) {
                $insMap[$in->d . '_' . $in->unit_type] = (float) $in->total;
            }

            for ($i = 6; $i >= 0; $i--) {
                $dateObj = $latestCarbon->copy()->subDays($i);
                $dateStr = $dateObj->format('Y-m-d');
                $lineCategories[] = $dateObj->translatedFormat('d M');

                $monthlyInDiscrete[] = (int) ($insMap[$dateStr . '_discrete'] ?? 0);
                $monthlyOutDiscrete[] = (int) ($outsMap[$dateStr . '_discrete'] ?? 0);
                $monthlyInDecimal[] = round((float) ($insMap[$dateStr . '_decimal'] ?? 0), 3);
                $monthlyOutDecimal[] = round((float) ($outsMap[$dateStr . '_decimal'] ?? 0), 3);
            }
        } elseif ($lineRange === '1m') {
            $startRange = $latestCarbon->copy()->subWeeks(3)->startOfWeek()->startOfDay();
            $endRange = $latestCarbon->copy()->endOfWeek()->endOfDay();

            $outsByDay = MaterialOut::leftJoin('items', 'material_outs.item_id', '=', 'items.id')
                ->selectRaw("$rawDateOut, $sqlUnitOut as unit_type, SUM(material_outs.quantity) as total")
                ->whereBetween('material_outs.date', [$startRange->format('Y-m-d'), $endRange->format('Y-m-d')])
                ->groupBy('d', 'unit_type')
                ->get();

            $insByDay = StockLog::leftJoin('items', 'stock_logs.item_id', '=', 'items.id')
                ->selectRaw("$rawDateIn, $sqlUnitIn as unit_type, SUM(stock_logs.quantity) as total")
                ->where('stock_logs.type', 'in')
                ->whereBetween('stock_logs.created_at', [$startRange->format('Y-m-d 00:00:00'), $endRange->format('Y-m-d 23:59:59')])
                ->groupBy('d', 'unit_type')
                ->get();

            for ($i = 3; $i >= 0; $i--) {
                $startW = $latestCarbon->copy()->subWeeks($i)->startOfWeek();
                $endW = $latestCarbon->copy()->subWeeks($i)->endOfWeek();
                $label = 'Mg ' . (4 - $i) . ' (' . $endW->format('d/m') . ')';
                $lineCategories[] = $label;

                $wStartStr = $startW->format('Y-m-d');
                $wEndStr = $endW->format('Y-m-d');

                $wOutDisc = 0; $wOutDec = 0;
                $wInDisc = 0; $wInDec = 0;

                foreach ($outsByDay as $o) {
                    if ($o->d >= $wStartStr && $o->d <= $wEndStr) {
                        if ($o->unit_type === 'decimal') {
                            $wOutDec += (float) $o->total;
                        } else {
                            $wOutDisc += (float) $o->total;
                        }
                    }
                }
                foreach ($insByDay as $in) {
                    if ($in->d >= $wStartStr && $in->d <= $wEndStr) {
                        if ($in->unit_type === 'decimal') {
                            $wInDec += (float) $in->total;
                        } else {
                            $wInDisc += (float) $in->total;
                        }
                    }
                }

                $monthlyInDiscrete[] = (int) $wInDisc;
                $monthlyOutDiscrete[] = (int) $wOutDisc;
                $monthlyInDecimal[] = round((float) $wInDec, 3);
                $monthlyOutDecimal[] = round((float) $wOutDec, 3);
            }
        } else {
            // Filter 1 Tahun (12 Bulan untuk Tahun Transaksi - Default)
            $lineCategories = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            $monthlyInDiscrete = array_fill(0, 12, 0);
            $monthlyOutDiscrete = array_fill(0, 12, 0);
            $monthlyInDecimal = array_fill(0, 12, 0);
            $monthlyOutDecimal = array_fill(0, 12, 0);

            $outsByMonth = MaterialOut::leftJoin('items', 'material_outs.item_id', '=', 'items.id')
                ->selectRaw("MONTH(material_outs.date) as m, $sqlUnitOut as unit_type, SUM(material_outs.quantity) as total")
                ->whereYear('material_outs.date', $activeYear)
                ->groupBy('m', 'unit_type')
                ->get();

            foreach ($outsByMonth as $o) {
                $mIndex = (int)$o->m - 1;
                if ($mIndex >= 0 && $mIndex < 12) {
                    if ($o->unit_type === 'decimal') {
                        $monthlyOutDecimal[$mIndex] = round((float) $o->total, 3);
                    } else {
                        $monthlyOutDiscrete[$mIndex] = (int) $o->total;
                    }
                }
            }

            $rawMonth = ($driver === 'sqlite') ? "strftime('%m', stock_logs.created_at) as month" : "MONTH(stock_logs.created_at) as month";
            $logsIn = StockLog::leftJoin('items', 'stock_logs.item_id', '=', 'items.id')
                ->selectRaw("$rawMonth, $sqlUnitIn as unit_type, SUM(stock_logs.quantity) as total")
                ->where('stock_logs.type', 'in')
                ->whereYear('stock_logs.created_at', $activeYear)
                ->groupBy('month', 'unit_type')
                ->get();

            foreach ($logsIn as $in) {
                $mIndex = (int)$in->month - 1;
                if ($mIndex >= 0 && $mIndex < 12) {
                    if ($in->unit_type === 'decimal') {
                        $monthlyInDecimal[$mIndex] = round((float) $in->total, 3);
                    } else {
                        $monthlyInDiscrete[$mIndex] = (int) $in->total;
                    }
                }
            }
        }

        $monthlyIn = $monthlyInDiscrete;
        $monthlyOut = $monthlyOutDiscrete;

        // ==========================================
        // 2. Bar Chart Data (Stok per Kategori Material)
        // Pemisahan Satuan Hitung vs Satuan Desimal
        // ==========================================
        $categoryNames = [];
        $categoryShortNames = [];
        $categoryStocksDiscrete = [];
        $categoryStocksDecimal = [];
        $categoryStockFormatted = [];

        $shortMap = [
            'MATERIAL UTAMA' => 'Mat. Utama',
            'MATERIAL PENDUKUNG' => 'Mat. Pendukung',
            'CONSUMABLE- UMUM' => 'Consumable',
            'CONSUMABLE- SPAREPART' => 'Sparepart',
            'CONSUMABLE- SAFETY' => 'Safety',
        ];

        foreach ($categories as $cat) {
            $fullName = $cat->name;
            $categoryNames[] = $fullName;
            $categoryShortNames[] = $shortMap[strtoupper(trim($fullName))] ?? (strlen($fullName) > 12 ? substr($fullName, 0, 10) . '..' : $fullName);

            $catDetails = $categoryStockDetails[$cat->id] ?? null;
            $discVal = $catDetails ? $catDetails['discrete'] : 0;
            $decVal = $catDetails ? array_sum($catDetails['decimals']) : 0;

            $categoryStocksDiscrete[] = (float) $discVal;
            $categoryStocksDecimal[] = round((float) $decVal, 3);
            $categoryStockFormatted[] = $catDetails ? $catDetails['formatted'] : '0 Unit';
        }

        $categoryStocks = $categoryStocksDiscrete;
        $totalCategoryDiscrete = array_sum($categoryStocksDiscrete);
        $totalCategoryDecimal = array_sum($categoryStocksDecimal);

        // ==========================================
        // 3. Chart Data: Top Pengeluaran Item/Material
        // Berdasarkan kolom 'date' di material_outs
        // ==========================================
        $outQuery = MaterialOut::select('material_outs.item_id', DB::raw('SUM(material_outs.quantity) as total_out'))
            ->with('item');

        if ($itemOutRange === '1w') {
            $outQuery->where('date', '>=', $latestCarbon->copy()->subDays(7)->format('Y-m-d'));
        } elseif ($itemOutRange === '1m') {
            $outQuery->where('date', '>=', $latestCarbon->copy()->subDays(30)->format('Y-m-d'));
        } else {
            $outQuery->whereYear('date', $activeYear);
        }

        $topMaterialOuts = $outQuery->groupBy('material_outs.item_id')
            ->orderByDesc('total_out')
            ->take(8)
            ->get();

        $topItemNames = [];
        $topItemShortNames = [];
        $topItemTotals = [];
        $topItemUnits = [];
        $topItemFormatted = [];

        foreach ($topMaterialOuts as $mo) {
            $itemName = $mo->item?->name ?? ('Item #' . $mo->item_id);
            $unit = $mo->item?->unit ?? 'PCS';
            $qty = (float) $mo->total_out;

            $topItemNames[] = $itemName;
            $topItemShortNames[] = strlen($itemName) > 18 ? substr($itemName, 0, 15) . '..' : $itemName;
            $topItemTotals[] = $qty;
            $topItemUnits[] = strtoupper(trim($unit));
            $topItemFormatted[] = format_unit_quantity($qty, $unit, true);
        }

        $totalTopItemQty = array_sum($topItemTotals);

        // ==========================================
        // 4. All Inventory Aging Data & Buckets
        // ==========================================
        $totalItemsCount = $allItems->count();

        $under30Count = 0;
        $between31and60Count = 0;
        $between61and90Count = 0;
        $over90Count = 0;

        $agingTableData = [];
        $now = now();

        foreach ($allItems as $item) {
            if ($item->manufacturing_date) {
                $days = (int) $now->diffInDays(Carbon::parse($item->manufacturing_date));
            } else {
                $days = (int) $now->diffInDays($item->created_at ?? $now);
            }

            if ($days <= 30) {
                $under30Count++;
                $bucket = '<30 Days';
                $badgeClass = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400';
            } elseif ($days <= 60) {
                $between31and60Count++;
                $bucket = '31-60 Days';
                $badgeClass = 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400';
            } elseif ($days <= 90) {
                $between61and90Count++;
                $bucket = '61-90 Days';
                $badgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400';
            } else {
                $over90Count++;
                $bucket = '>90 Days';
                $badgeClass = 'bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400';
            }

            $agingTableData[] = [
                'id' => $item->id,
                'name' => $item->name,
                'stock' => format_unit_quantity($item->stock, $item->unit),
                'unit' => $item->unit ?? 'Unit',
                'days' => $days,
                'bucket' => $bucket,
                'badge_class' => $badgeClass,
            ];
        }

        usort($agingTableData, fn($a, $b) => $b['days'] <=> $a['days']);

        // ==========================================
        // 5. Informasi Penggunaan Material per Project
        // Mengikuti data asli transaksi & pisahkan satuan desimal
        // ==========================================
        $projectOutQuery = MaterialOut::leftJoin('items', 'material_outs.item_id', '=', 'items.id')
            ->selectRaw("
                material_outs.project_id,
                material_outs.project,
                UPPER(TRIM(COALESCE(items.unit, material_outs.unit, 'PCS'))) as unit,
                SUM(material_outs.quantity) as sum_qty,
                COUNT(material_outs.id) as trans_count,
                COUNT(DISTINCT material_outs.item_id) as items_count
            ");

        if ($projectRange === '1w') {
            $projectOutQuery->where('material_outs.date', '>=', $latestCarbon->copy()->subDays(7)->format('Y-m-d'));
        } elseif ($projectRange === '1m') {
            $projectOutQuery->where('material_outs.date', '>=', $latestCarbon->copy()->subDays(30)->format('Y-m-d'));
        } elseif ($projectRange === '1y') {
            $projectOutQuery->whereYear('material_outs.date', $activeYear);
        }

        $projectOutRows = $projectOutQuery->groupBy('material_outs.project_id', 'material_outs.project', 'unit')
            ->get();

        $projectAggregates = [];
        foreach ($projectOutRows as $row) {
            $key = $row->project_id ? ('id_' . $row->project_id) : ('name_' . trim($row->project));
            if (!isset($projectAggregates[$key])) {
                $projectAggregates[$key] = [
                    'project_id' => $row->project_id,
                    'project_name' => $row->project,
                    'discrete_qty' => 0,
                    'decimal_totals' => [],
                    'total_trans' => 0,
                    'total_items' => 0,
                ];
            }
            $projectAggregates[$key]['total_trans'] += (int) $row->trans_count;
            $projectAggregates[$key]['total_items'] += (int) $row->items_count;
            $u = $row->unit;
            $q = (float) $row->sum_qty;
            if (in_array($u, $decimalUnits, true)) {
                $projectAggregates[$key]['decimal_totals'][$u] = ($projectAggregates[$key]['decimal_totals'][$u] ?? 0) + $q;
            } else {
                $projectAggregates[$key]['discrete_qty'] += $q;
            }
        }

        $allProjectModels = \App\Models\Project::all()->keyBy('id');

        $projectListData = collect($projectAggregates)->map(function ($agg) use ($allProjectModels) {
            $prj = $agg['project_id'] ? ($allProjectModels[$agg['project_id']] ?? null) : null;
            $pName = $prj?->name ?? ($agg['project_name'] ?: 'Project Tanpa Nama');
            $pCode = $prj?->code ?? '-';
            $pStatus = $prj?->status ?? 'Active';
            $pClient = $prj?->client ?? '-';

            $breakdownParts = [];
            if ($agg['discrete_qty'] > 0) {
                $breakdownParts['PCS'] = $agg['discrete_qty'];
            }
            foreach ($agg['decimal_totals'] as $du => $dq) {
                $breakdownParts[$du] = $dq;
            }
            $formatted = format_unit_breakdown($breakdownParts);

            return [
                'id' => $agg['project_id'],
                'name' => $pName,
                'code' => $pCode,
                'status' => $pStatus,
                'client' => $pClient,
                'discrete_qty' => (float) $agg['discrete_qty'],
                'decimal_totals' => $agg['decimal_totals'],
                'decimal_qty' => (float) array_sum($agg['decimal_totals']),
                'total_qty' => (float) ($agg['discrete_qty'] + array_sum($agg['decimal_totals'])),
                'total_qty_formatted' => $formatted,
                'total_trans' => $agg['total_trans'],
                'total_items' => $agg['total_items'],
                'url' => $agg['project_id'] ? route('projects.show', $agg['project_id']) : route('projects.index'),
            ];
        })->sortByDesc('discrete_qty')->values();

        $totalProjectDiscreteQty = (float) $projectListData->sum('discrete_qty');
        $totalProjectDecimalBreakdown = [];
        foreach ($projectListData as $pItem) {
            foreach ($pItem['decimal_totals'] as $u => $val) {
                $totalProjectDecimalBreakdown[$u] = ($totalProjectDecimalBreakdown[$u] ?? 0) + $val;
            }
        }
        $totalProjectDecimalString = format_unit_breakdown($totalProjectDecimalBreakdown);
        $totalProjectCount = $projectListData->count();
        $topProjectRecord = $projectListData->first();

        // Add percentage to projectListData
        $projectListData = $projectListData->map(function ($p, $idx) use ($totalProjectDiscreteQty) {
            $p['rank'] = $idx + 1;
            $pct = $totalProjectDiscreteQty > 0 ? round(($p['discrete_qty'] / $totalProjectDiscreteQty) * 100, 1) : 0;
            $p['percentage'] = $pct;
            return $p;
        });

        // Top 8 Projects for ApexChart
        $topProjectsForChart = $projectListData->take(8);
        $chartProjectNames = [];
        $chartProjectFullNames = [];
        $chartProjectDiscreteTotals = [];
        $chartProjectDecimalTotals = [];
        $chartProjectFormatted = [];

        foreach ($topProjectsForChart as $pc) {
            $chartProjectFullNames[] = $pc['name'];
            $chartProjectNames[] = strlen($pc['name']) > 18 ? substr($pc['name'], 0, 15) . '..' : $pc['name'];
            $chartProjectDiscreteTotals[] = (float) $pc['discrete_qty'];
            $chartProjectDecimalTotals[] = (float) $pc['decimal_qty'];
            $chartProjectFormatted[] = $pc['total_qty_formatted'];
        }

        return view('pages.dashboard.ecommerce', [
            'title' => 'Warehouse Management System',
            'totalItems' => $totalItems,
            'totalActiveTonnage' => $totalActiveTonnage,
            'totalAssetValue' => $totalAssetValue,
            'pricedItemsCount' => $pricedItemsCount,
            'totalStock' => $totalStock,
            'totalDiscreteStock' => $totalDiscreteStock,
            'totalDecimalStockBreakdown' => $totalDecimalStockBreakdown,
            'categoryStockDetails' => $categoryStockDetails,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'lowStockOnlyCount' => $lowStockOnlyCount,
            'totalCategories' => $totalCategories,
            'recentLogs' => $recentLogs,
            'lowStockItems' => $lowStockItems,
            'criticalItems' => $criticalItems,
            'fastMovingItems' => $fastMovingItems,
            'fefoExpiringCount' => $fefoExpiringCount,
            'fefoExpiredCount' => $fefoExpiredCount,
            'fefoItems' => $fefoItems,
            'shelfLifeMonths' => $shelfLifeMonths,
            'alertDays' => $alertDays,
            'categories' => $categories,
            // Warehouse Real-time Daily & Operational Pulse
            'todayInCount' => $todayInCount,
            'todayInQty' => $todayInQty,
            'todayOutCount' => $todayOutCount,
            'todayOutQty' => $todayOutQty,
            'todayLogsCount' => $todayLogsCount,
            'recentDayInCount' => $recentDayInCount,
            'recentDayOutCount' => $recentDayOutCount,
            'latestOpDate' => $latestOpDate,
            'thisMonthInCount' => $thisMonthInCount,
            'thisMonthOutCount' => $thisMonthOutCount,
            'monthLabel' => $monthLabel,
            // QC & Reject Status
            'pendingQcCount' => $pendingQcCount,
            'pendingQcItems' => $pendingQcItems,
            'pendingRejectCount' => $pendingRejectCount,
            'totalRejectCount' => $totalRejectCount,
            'pendingRejectList' => $pendingRejectList,
            // Active Year & Latest Date
            'activeYear' => $activeYear,
            'latestDateStr' => $latestDateStr,
            // Filter States
            'lineRange' => $lineRange,
            'barRange' => $barRange,
            'itemOutRange' => $itemOutRange,
            // Chart 1 Data (Line)
            'lineCategories' => $lineCategories,
            'monthlyIn' => $monthlyIn,
            'monthlyOut' => $monthlyOut,
            'monthlyInDiscrete' => $monthlyInDiscrete,
            'monthlyOutDiscrete' => $monthlyOutDiscrete,
            'monthlyInDecimal' => $monthlyInDecimal,
            'monthlyOutDecimal' => $monthlyOutDecimal,
            // Chart 2 Data (Bar Category)
            'categoryNames' => $categoryNames,
            'categoryShortNames' => $categoryShortNames,
            'categoryStocks' => $categoryStocks,
            'categoryStocksDiscrete' => $categoryStocksDiscrete,
            'categoryStocksDecimal' => $categoryStocksDecimal,
            'categoryStockFormatted' => $categoryStockFormatted,
            'totalCategoryDiscrete' => $totalCategoryDiscrete,
            'totalCategoryDecimal' => $totalCategoryDecimal,
            // Chart 3 Data (Top Material Outflow)
            'topItemNames' => $topItemNames,
            'topItemShortNames' => $topItemShortNames,
            'topItemTotals' => $topItemTotals,
            'topItemUnits' => $topItemUnits,
            'topItemFormatted' => $topItemFormatted,
            'totalTopItemQty' => $totalTopItemQty,
            // Chart 4 Data (All Inventory Aging)
            'agingSeries' => [$under30Count, $between31and60Count, $between61and90Count, $over90Count],
            'agingTotalItems' => $totalItemsCount,
            'agingTableData' => $agingTableData,
            // 5. Data Penggunaan Material per Project
            'projectRange' => $projectRange,
            'projectListData' => $projectListData,
            'totalProjectMaterialQty' => $totalProjectDiscreteQty,
            'totalProjectDiscreteQty' => $totalProjectDiscreteQty,
            'totalProjectDecimalString' => $totalProjectDecimalString,
            'totalProjectCount' => $totalProjectCount,
            'topProjectRecord' => $topProjectRecord,
            'chartProjectNames' => $chartProjectNames,
            'chartProjectFullNames' => $chartProjectFullNames,
            'chartProjectTotals' => $chartProjectDiscreteTotals,
            'chartProjectDiscreteTotals' => $chartProjectDiscreteTotals,
            'chartProjectDecimalTotals' => $chartProjectDecimalTotals,
            'chartProjectFormatted' => $chartProjectFormatted,
            'storage1Count' => $storage1Count,
            'storage2Count' => $storage2Count,
        ]);
    }
}
