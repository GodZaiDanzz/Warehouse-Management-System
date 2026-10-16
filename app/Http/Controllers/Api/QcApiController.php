<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompanyProfile;
use App\Models\Item;
use App\Models\MaterialIn;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class QcApiController extends Controller
{
    /**
     * Validate optional API Key if QC_API_KEY is configured in .env.
     */
    protected function authorizeRequest(Request $request): ?JsonResponse
    {
        $expectedKey = env('QC_API_KEY');

        // If no key is set in .env, allow public read access for internal QC/QA integration
        if (empty($expectedKey)) {
            return null;
        }

        $providedKey = $request->header('X-API-KEY') 
            ?: $request->bearerToken() 
            ?: $request->query('api_key');

        if (!$providedKey || !hash_equals((string) $expectedKey, (string) $providedKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: Kunci API (API Key) tidak valid atau belum disertakan.',
            ], 401);
        }

        return null;
    }

    /**
     * Ringkasan & Statistik QC/QA (Dashboard Summary)
     * GET /api/qc/summary
     */
    public function summary(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeRequest($request)) {
            return $authError;
        }

        $totalItems = Item::count();
        $outOfStockCount = Item::where('stock', '<=', 0)->count();
        $lowStockCount = Item::where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();

        // Expired items check
        $expiredCount = Item::whereNotNull('expiry_date')
            ->where('expiry_date', '<', now()->startOfDay())
            ->count();

        $totalMaterialIns = MaterialIn::count();
        $pendingQcCount = MaterialIn::where(function ($q) {
            $q->whereNull('qc_status')->orWhere('qc_status', 'pending_qc');
        })->count();

        $approvedCount = MaterialIn::where('qc_status', 'approved')->count();
        $rejectedCount = MaterialIn::where('qc_status', 'rejected')->count();

        // 5 data penerimaan terbaru yang sedang menunggu inspeksi QC
        $recentPending = MaterialIn::with(['item.category', 'unitRelation'])
            ->where(function ($q) {
                $q->whereNull('qc_status')->orWhere('qc_status', 'pending_qc');
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'reference_number' => $row->reference_number,
                    'date' => $row->date ? $row->date->format('Y-m-d') : null,
                    'po_number' => $row->po_number,
                    'supplier' => $row->supplier,
                    'item_code' => $row->item?->code_item,
                    'item_name' => $row->item?->name,
                    'category' => $row->item?->category?->name,
                    'quantity' => (float) $row->quantity,
                    'unit' => $row->unit,
                    'received_by' => $row->received_by,
                    'qc_status' => 'pending_qc',
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Ringkasan data QC/QA berhasil diambil.',
            'data' => [
                'inventory' => [
                    'total_items' => $totalItems,
                    'storage_1_items' => Item::inStorage(Item::STORAGE_1)->count(),
                    'storage_2_items' => Item::inStorage(Item::STORAGE_2)->count(),
                    'out_of_stock' => $outOfStockCount,
                    'low_stock' => $lowStockCount,
                    'expired_items' => $expiredCount,
                ],
                'material_in' => [
                    'total_transactions' => $totalMaterialIns,
                    'pending_qc' => $pendingQcCount,
                    'approved_qc' => $approvedCount,
                    'rejected_qc' => $rejectedCount,
                ],
                'recent_pending_queue' => $recentPending,
            ],
        ]);
    }

    /**
     * Menampilkan Data Stok Barang Gudang
     * GET /api/qc/stocks atau GET /api/qc/items
     */
    public function stocks(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeRequest($request)) {
            return $authError;
        }

        $query = Item::with(['category', 'unitRelation']);

        // Filter: Pencarian nama atau kode material
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code_item', 'like', "%{$search}%");
            });
        }

        // Filter: Kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->query('category_id'));
        } elseif ($request->filled('category')) {
            $categoryName = trim($request->query('category'));
            $query->whereHas('category', function ($q) use ($categoryName) {
                $q->where('name', 'like', "%{$categoryName}%");
            });
        }

        // Filter: Gudang Penyimpanan (STORAGE 1 vs STORAGE 2)
        if ($request->filled('storage')) {
            $query->inStorage($request->query('storage'));
        }

        // Filter: Status Stok
        if ($request->filled('status')) {
            $status = strtolower(trim($request->query('status')));
            if ($status === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            } elseif ($status === 'low_stock') {
                $query->where('stock', '>', 0)->whereColumn('stock', '<=', 'min_stock');
            } elseif ($status === 'safe') {
                $query->whereColumn('stock', '>', 'min_stock');
            } elseif ($status === 'expired') {
                $query->whereNotNull('expiry_date')->where('expiry_date', '<', now()->startOfDay());
            } elseif ($status === 'near_expiry') {
                $alertDays = CompanyProfile::getCatFefoAlertDays() ?? 30;
                $query->whereNotNull('expiry_date')
                      ->whereBetween('expiry_date', [now()->startOfDay(), now()->addDays($alertDays)->endOfDay()]);
            }
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'name');
        $sortDir = strtolower($request->query('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $allowedSorts = ['name', 'code_item', 'stock', 'min_stock', 'expiry_date', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir);
        } else {
            $query->orderBy('name', 'asc');
        }

        // Transform collection item
        $transformItem = function (Item $item) {
            $stock = (float) $item->stock;
            $minStock = (float) $item->min_stock;
            $isExpired = $item->isExpired();
            $isNearExpiry = $item->isNearExpiry();

            $stockStatus = 'safe';
            if ($stock <= 0) {
                $stockStatus = 'out_of_stock';
            } elseif ($stock <= $minStock) {
                $stockStatus = 'low_stock';
            }

            return [
                'id' => $item->id,
                'code_item' => $item->code_item,
                'name' => $item->name,
                'category' => $item->category ? [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                ] : null,
                'stock' => $stock,
                'unit' => $item->unit,
                'min_stock' => $minStock,
                'storage' => $item->storage_location,
                'location' => $item->location,
                'display_location' => $item->display_location,
                'manufacturing_date' => $item->manufacturing_date ? $item->manufacturing_date->format('Y-m-d') : null,
                'expiry_date' => $item->expiry_date ? $item->expiry_date->format('Y-m-d') : null,
                'is_expired' => $isExpired,
                'is_near_expiry' => $isNearExpiry,
                'stock_status' => $stockStatus,
                'wholesale' => $item->hasWholesaleConversion() ? [
                    'wholesale_unit' => $item->wholesale_unit,
                    'conversion_factor' => (float) $item->conversion_factor,
                    'stock_in_wholesale' => $item->stock_in_wholesale_unit,
                ] : null,
                'updated_at' => $item->updated_at ? $item->updated_at->toIso8601String() : null,
            ];
        };

        // Format respon (Pagination atau All)
        $isAll = filter_var($request->query('all', false), FILTER_VALIDATE_BOOLEAN);
        if ($isAll) {
            $items = $query->get()->map($transformItem);
            return response()->json([
                'success' => true,
                'message' => 'Semua data stok barang berhasil diambil.',
                'data' => $items,
                'meta' => [
                    'total' => $items->count(),
                ],
            ]);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data stok barang berhasil diambil.',
            'data' => collect($paginated->items())->map($transformItem),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Detail 1 Barang beserta Riwayat QC Penerimaan Terakhir
     * GET /api/qc/stocks/{id}
     */
    public function stockDetail(Request $request, $id): JsonResponse
    {
        if ($authError = $this->authorizeRequest($request)) {
            return $authError;
        }

        $item = Item::with(['category', 'unitRelation'])->find($id);
        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => "Data barang dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        // Ambil 5 riwayat penerimaan material masuk terakhir untuk barang ini
        $recentInbounds = MaterialIn::with(['qcUser'])
            ->where('item_id', $item->id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => $row->id,
                    'reference_number' => $row->reference_number,
                    'date' => $row->date ? $row->date->format('Y-m-d') : null,
                    'po_number' => $row->po_number,
                    'supplier' => $row->supplier,
                    'quantity' => (float) $row->quantity,
                    'unit' => $row->unit,
                    'qc_status' => $row->qc_status ?: 'pending_qc',
                    'qc_by' => $row->qcUser?->name,
                    'qc_at' => $row->qc_at ? $row->qc_at->toIso8601String() : null,
                    'qc_notes' => $row->qc_notes,
                ];
            });

        $stock = (float) $item->stock;
        $minStock = (float) $item->min_stock;
        $stockStatus = 'safe';
        if ($stock <= 0) {
            $stockStatus = 'out_of_stock';
        } elseif ($stock <= $minStock) {
            $stockStatus = 'low_stock';
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail data barang berhasil diambil.',
            'data' => [
                'id' => $item->id,
                'code_item' => $item->code_item,
                'name' => $item->name,
                'category' => $item->category ? [
                    'id' => $item->category->id,
                    'name' => $item->category->name,
                ] : null,
                'stock' => $stock,
                'unit' => $item->unit,
                'min_stock' => $minStock,
                'location' => $item->location,
                'manufacturing_date' => $item->manufacturing_date ? $item->manufacturing_date->format('Y-m-d') : null,
                'expiry_date' => $item->expiry_date ? $item->expiry_date->format('Y-m-d') : null,
                'is_expired' => $item->isExpired(),
                'is_near_expiry' => $item->isNearExpiry(),
                'stock_status' => $stockStatus,
                'wholesale' => $item->hasWholesaleConversion() ? [
                    'wholesale_unit' => $item->wholesale_unit,
                    'conversion_factor' => (float) $item->conversion_factor,
                    'stock_in_wholesale' => $item->stock_in_wholesale_unit,
                ] : null,
                'recent_inbounds' => $recentInbounds,
                'updated_at' => $item->updated_at ? $item->updated_at->toIso8601String() : null,
            ],
        ]);
    }

    /**
     * Menampilkan Data Material Masuk (Inbound Receipts untuk Pemeriksaan QC/QA)
     * GET /api/qc/material-ins
     */
    public function materialIns(Request $request): JsonResponse
    {
        if ($authError = $this->authorizeRequest($request)) {
            return $authError;
        }

        $query = MaterialIn::with(['item.category', 'unitRelation', 'qcUser', 'user']);

        // Filter: Status QC (pending_qc, approved, rejected, all)
        if ($request->filled('qc_status')) {
            $qcStatus = strtolower(trim($request->query('qc_status')));
            if ($qcStatus === 'pending' || $qcStatus === 'pending_qc') {
                $query->where(function ($q) {
                    $q->whereNull('qc_status')->orWhere('qc_status', 'pending_qc');
                });
            } elseif ($qcStatus === 'approved') {
                $query->where('qc_status', 'approved');
            } elseif ($qcStatus === 'rejected') {
                $query->where('qc_status', 'rejected');
            }
        }

        // Filter: Pencarian (Nomor Referensi, PO, Supplier, atau Nama Barang)
        if ($request->filled('search')) {
            $search = trim($request->query('search'));
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                  ->orWhere('po_number', 'like', "%{$search}%")
                  ->orWhere('supplier', 'like', "%{$search}%")
                  ->orWhereHas('item', function ($iq) use ($search) {
                      $iq->where('name', 'like', "%{$search}%")
                         ->orWhere('code_item', 'like', "%{$search}%");
                  });
            });
        }

        // Filter: Rentang Tanggal Penerimaan (date_from & date_to)
        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->query('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->query('date_to'));
        }

        // Filter: Supplier
        if ($request->filled('supplier')) {
            $query->where('supplier', 'like', '%' . trim($request->query('supplier')) . '%');
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'date');
        $sortDir = strtolower($request->query('sort_dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['date', 'reference_number', 'quantity', 'created_at', 'qc_status'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDir)->orderBy('id', 'desc');
        } else {
            $query->orderBy('date', 'desc')->orderBy('id', 'desc');
        }

        // Transform collection item
        $transformMaterialIn = function (MaterialIn $row) {
            $qcStatus = $row->qc_status ?: 'pending_qc';

            return [
                'id' => $row->id,
                'reference_number' => $row->reference_number,
                'date' => $row->date ? $row->date->format('Y-m-d') : null,
                'po_number' => $row->po_number,
                'supplier' => $row->supplier,
                'received_by' => $row->received_by,
                'item' => $row->item ? [
                    'id' => $row->item->id,
                    'code_item' => $row->item->code_item,
                    'name' => $row->item->name,
                    'category' => $row->item->category?->name,
                    'current_stock' => (float) $row->item->stock,
                ] : null,
                'quantity' => (float) $row->quantity,
                'unit' => $row->unit,
                'wholesale_qty' => $row->wholesale_qty !== null ? (float) $row->wholesale_qty : null,
                'wholesale_unit' => $row->wholesale_unit,
                'qc_status' => $qcStatus,
                'qc_by' => $row->qcUser?->name,
                'qc_at' => $row->qc_at ? $row->qc_at->toIso8601String() : null,
                'qc_notes' => $row->qc_notes,
                'manufacturing_date' => $row->manufacturing_date ? $row->manufacturing_date->format('Y-m-d') : null,
                'expiry_date' => $row->expiry_date ? $row->expiry_date->format('Y-m-d') : null,
                'notes' => $row->notes,
                'recorded_by' => $row->user?->name,
                'created_at' => $row->created_at ? $row->created_at->toIso8601String() : null,
            ];
        };

        // Format respon (Pagination atau All)
        $isAll = filter_var($request->query('all', false), FILTER_VALIDATE_BOOLEAN);
        if ($isAll) {
            $records = $query->get()->map($transformMaterialIn);
            return response()->json([
                'success' => true,
                'message' => 'Semua data material masuk berhasil diambil.',
                'data' => $records,
                'meta' => [
                    'total' => $records->count(),
                ],
            ]);
        }

        $perPage = max(1, min(100, (int) $request->query('per_page', 20)));
        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Data material masuk berhasil diambil.',
            'data' => collect($paginated->items())->map($transformMaterialIn),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Detail 1 Transaksi Material Masuk
     * GET /api/qc/material-ins/{id}
     */
    public function materialInDetail(Request $request, $id): JsonResponse
    {
        if ($authError = $this->authorizeRequest($request)) {
            return $authError;
        }

        $row = MaterialIn::with(['item.category', 'unitRelation', 'qcUser', 'user', 'materialRejects'])
            ->find($id);

        if (!$row) {
            return response()->json([
                'success' => false,
                'message' => "Data transaksi material masuk dengan ID {$id} tidak ditemukan.",
            ], 404);
        }

        $qcStatus = $row->qc_status ?: 'pending_qc';

        // Rejection data jika ada
        $rejects = $row->materialRejects->map(function ($rej) {
            return [
                'id' => $rej->id,
                'reference_number' => $rej->reference_number,
                'quantity' => (float) $rej->quantity,
                'unit' => $rej->unit,
                'damage_type' => $rej->damage_type,
                'disposition' => $rej->disposition,
                'disposition_status' => $rej->disposition_status,
                'description' => $rej->description,
                'evidence_photo_url' => $rej->evidence_photo ? asset('storage/' . $rej->evidence_photo) : null,
                'reported_by' => $rej->reported_by,
                'inspected_by' => $rej->inspected_by,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Detail data material masuk berhasil diambil.',
            'data' => [
                'id' => $row->id,
                'reference_number' => $row->reference_number,
                'date' => $row->date ? $row->date->format('Y-m-d') : null,
                'po_number' => $row->po_number,
                'supplier' => $row->supplier,
                'received_by' => $row->received_by,
                'item' => $row->item ? [
                    'id' => $row->item->id,
                    'code_item' => $row->item->code_item,
                    'name' => $row->item->name,
                    'category' => $row->item->category?->name,
                    'location' => $row->item->location,
                    'current_stock' => (float) $row->item->stock,
                ] : null,
                'quantity' => (float) $row->quantity,
                'unit' => $row->unit,
                'wholesale_qty' => $row->wholesale_qty !== null ? (float) $row->wholesale_qty : null,
                'wholesale_unit' => $row->wholesale_unit,
                'qc_status' => $qcStatus,
                'qc_by' => $row->qcUser?->name,
                'qc_at' => $row->qc_at ? $row->qc_at->toIso8601String() : null,
                'qc_notes' => $row->qc_notes,
                'manufacturing_date' => $row->manufacturing_date ? $row->manufacturing_date->format('Y-m-d') : null,
                'expiry_date' => $row->expiry_date ? $row->expiry_date->format('Y-m-d') : null,
                'notes' => $row->notes,
                'recorded_by' => $row->user?->name,
                'material_rejects' => $rejects,
                'created_at' => $row->created_at ? $row->created_at->toIso8601String() : null,
            ],
        ]);
    }
}
