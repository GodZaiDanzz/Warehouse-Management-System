<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\Project;
use App\Models\StockLog;
use App\Models\ActivityLog;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ItemTableComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $category_id = '';
    public $unit_id = '';
    public $stock_status = '';
    public $storage_filter = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 25;

    // Quick Barcode Action Modal States
    public bool $showQuickActionModal = false;
    public ?int $scannedItemId = null;
    public ?Item $scannedItem = null;
    public string $quickActionType = ''; // 'menu', 'in', 'out', 'success', 'not_found'
    public string $scannedBarcodeText = '';
    public string $quickErrorMessage = '';
    public string $quickSuccessMessage = '';

    // Quick Material In form fields:
    public int $quickInQty = 1;
    public string $quickInSupplier = '';
    public string $quickInDate = '';
    public string $quickInReceivedBy = 'Warehouse';
    public string $quickInNotes = '';

    // Quick Material Out form fields:
    public int $quickOutQty = 1;
    public $quickOutProjectId = null;
    public string $quickOutProjectName = '';
    public string $quickOutPic = '';
    public string $quickOutDate = '';
    public string $quickOutNotes = '';

    public function mount()
    {
        $this->perPage = session('item_per_page', CompanyProfile::getDefaultPerPage());
        if (request()->has('storage_filter')) {
            $this->storage_filter = request()->query('storage_filter');
        }
    }

    protected $queryString = [
        'search' => ['except' => ''],
        'category_id' => ['except' => ''],
        'unit_id' => ['except' => ''],
        'stock_status' => ['except' => ''],
        'storage_filter' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function updatingUnitId()
    {
        $this->resetPage();
    }

    public function updatingStockStatus()
    {
        $this->resetPage();
    }

    public function updatingStorageFilter()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['search', 'category_id', 'unit_id', 'stock_status', 'storage_filter']);
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete($id)
    {
        $user = auth()->user();
        if (!$user || !$user->canManageWarehouse()) {
            session()->flash('error', 'Akses Ditolak: Hanya Administrator Gudang yang dapat menghapus barang.');
            $this->dispatch('swal:error', title: 'Gagal!', message: 'Akses Ditolak: Hanya Administrator Gudang yang dapat menghapus barang.');
            return;
        }

        $item = Item::find($id);
        if (!$item) {
            session()->flash('error', 'Barang tidak ditemukan.');
            $this->dispatch('swal:error', title: 'Gagal!', message: 'Barang tidak ditemukan.');
            return;
        }

        $name = $item->name;
        $item->delete();

        \App\Models\ActivityLog::record(
            activity: 'Hapus Barang',
            description: "Menghapus barang '{$name}' dari stok gudang.",
            module: 'Stok Barang'
        );

        session()->flash('success', "Barang '{$name}' berhasil dihapus!");
        $this->dispatch('swal:success', title: 'Deleted!', message: "Barang '{$name}' berhasil dihapus!");
    }

    /**
     * Handle scanned barcode from mobile camera.
     */
    public function handleScannedBarcode(string $decodedText)
    {
        $clean = trim($decodedText);
        $this->scannedBarcodeText = $clean;
        $this->quickErrorMessage = '';
        $this->quickSuccessMessage = '';

        if (empty($clean)) return;

        // 1. Try exact match on code_item or name or numeric ID or ITM-xxxx
        $found = Item::with('category')->where(function($q) use ($clean) {
            $q->where('code_item', $clean)
              ->orWhere('name', $clean);
            if (preg_match('/^ITM-(\d+)$/i', $clean, $m)) {
                $q->orWhere('id', (int)$m[1]);
            }
            if (is_numeric($clean)) {
                $q->orWhere('id', (int)$clean);
            }
        })->first();

        // 2. Try partial match if not found
        if (!$found) {
            $found = Item::with('category')->where(function($q) use ($clean) {
                $q->where('code_item', 'like', "%{$clean}%")
                  ->orWhere('name', 'like', "%{$clean}%");
            })->first();
        }

        if (!$found) {
            $this->scannedItemId = null;
            $this->scannedItem = null;
            $this->quickActionType = 'not_found';
            $this->showQuickActionModal = true;
            $this->dispatch('quick-action-step-changed', step: 'not_found');
            return;
        }

        $this->scannedItemId = $found->id;
        $this->scannedItem = $found;
        $this->quickActionType = 'menu';

        // Reset quick input states
        $this->quickInQty = 1;
        $this->quickInSupplier = '';
        $this->quickInDate = date('Y-m-d');
        $this->quickInReceivedBy = 'Warehouse';
        $this->quickInNotes = '';

        $this->quickOutQty = 1;
        $firstProject = Project::orderBy('name')->first();
        $this->quickOutProjectId = $firstProject?->id ?? null;
        $this->quickOutProjectName = '';
        $this->quickOutPic = '';
        $this->quickOutDate = date('Y-m-d');
        $this->quickOutNotes = '';

        $this->showQuickActionModal = true;
        $this->dispatch('quick-action-step-changed', step: 'menu');
    }

    public function selectQuickAction(string $type)
    {
        $this->quickErrorMessage = '';
        $this->quickActionType = $type;
        if ($this->scannedItemId && (!$this->scannedItem || $this->scannedItem->id !== $this->scannedItemId)) {
            $this->scannedItem = Item::with('category')->find($this->scannedItemId);
        }
        $this->dispatch('quick-action-step-changed', step: $type);
    }

    public function backToQuickMenu()
    {
        $this->quickErrorMessage = '';
        $this->quickActionType = 'menu';
        $this->dispatch('quick-action-step-changed', step: 'menu');
    }

    public function closeQuickActionModal()
    {
        $this->showQuickActionModal = false;
        $this->scannedItemId = null;
        $this->scannedItem = null;
        $this->quickActionType = '';
        $this->quickErrorMessage = '';
        $this->quickSuccessMessage = '';
        $this->dispatch('unlock-body-scroll');
    }

    public function filterScannedItemInTable()
    {
        if ($this->scannedItem) {
            $this->search = $this->scannedItem->code_item ?: $this->scannedItem->name;
        }
        $this->closeQuickActionModal();
    }

    /**
     * Submit Quick Material In transaction
     */
    public function submitQuickMaterialIn()
    {
        $user = auth()->user();
        if ($user && $user->isPpic()) {
            $this->quickErrorMessage = 'Akses Ditolak: Akun PPIC hanya memiliki hak akses melihat.';
            return;
        }

        if (!$this->scannedItemId) {
            $this->quickErrorMessage = 'Data barang tidak valid.';
            return;
        }

        if ((float)$this->quickInQty <= 0) {
            $this->quickErrorMessage = 'Jumlah material masuk minimal lebih dari 0.';
            return;
        }

        if (empty(trim($this->quickInSupplier))) {
            $this->quickErrorMessage = 'Nama supplier / vendor wajib diisi.';
            return;
        }

        try {
            DB::transaction(function () {
                $item = Item::where('id', $this->scannedItemId)->lockForUpdate()->firstOrFail();
                $refNo = 'IN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                $receivedBy = !empty(trim($this->quickInReceivedBy)) ? trim($this->quickInReceivedBy) : 'Warehouse';

                MaterialIn::create([
                    'reference_number' => $refNo,
                    'date' => $this->quickInDate ?: date('Y-m-d'),
                    'item_id' => $item->id,
                    'quantity' => $this->quickInQty,
                    'unit' => $item->unit ?: 'Pcs',
                    'supplier' => trim($this->quickInSupplier),
                    'received_by' => $receivedBy,
                    'notes' => trim($this->quickInNotes) ?: 'Input Cepat Barcode Scan via Smartphone',
                    'qc_status' => 'approved',
                    'qc_by' => auth()->id() ?? 1,
                    'qc_at' => now(),
                    'qc_notes' => 'Otomatis Disetujui via Quick Barcode Scanner',
                    'user_id' => auth()->id() ?? 1,
                ]);

                $item->increment('stock', $this->quickInQty);

                StockLog::create([
                    'reference_number' => $refNo,
                    'item_id' => $item->id,
                    'type' => 'in',
                    'quantity' => $this->quickInQty,
                    'recipient_or_provider' => trim($this->quickInSupplier),
                    'notes' => "Quick Scan Masuk (+{$this->quickInQty} {$item->unit}) dari " . trim($this->quickInSupplier) . " (Diterima: {$receivedBy})",
                    'user_id' => auth()->id() ?? 1,
                ]);

                ActivityLog::record(
                    activity: 'Quick Scan Material Masuk',
                    description: "Penerimaan cepat {$this->quickInQty} {$item->unit} {$item->name} dari " . trim($this->quickInSupplier) . " [Ref: {$refNo}]",
                    module: 'Material Masuk'
                );

                $this->quickSuccessMessage = "Berhasil mencatat " . format_quantity($this->quickInQty) . " {$item->unit} Material Masuk untuk '{$item->name}'! Stok terkini: " . format_quantity($item->stock) . " {$item->unit}.";
                $this->scannedItem = $item;
            });

            $this->quickActionType = 'success';
            $this->dispatch('quick-action-step-changed', step: 'success');
            $this->dispatch('play-success-sound');
        } catch (\Throwable $e) {
            $this->quickErrorMessage = 'Gagal menyimpan Material Masuk: ' . $e->getMessage();
        }
    }

    /**
     * Submit Quick Material Out transaction
     */
    public function submitQuickMaterialOut()
    {
        $user = auth()->user();
        if ($user && $user->isPpic()) {
            $this->quickErrorMessage = 'Akses Ditolak: Akun PPIC hanya memiliki hak akses melihat.';
            return;
        }

        if (!$this->scannedItemId) {
            $this->quickErrorMessage = 'Data barang tidak valid.';
            return;
        }

        if ((float)$this->quickOutQty <= 0) {
            $this->quickErrorMessage = 'Jumlah material keluar minimal lebih dari 0.';
            return;
        }

        if (empty(trim($this->quickOutPic))) {
            $this->quickErrorMessage = 'Nama PIC / penerima wajib diisi.';
            return;
        }

        try {
            DB::transaction(function () {
                $item = Item::where('id', $this->scannedItemId)->lockForUpdate()->firstOrFail();

                if ((float)$this->quickOutQty > (float)$item->stock) {
                    throw new \Exception("Stok tidak mencukupi! Sisa stok '{$item->name}' saat ini hanya " . format_quantity($item->stock) . " {$item->unit}.");
                }

                $projectObj = null;
                $projectName = 'Project Umum / Workshop';
                $projectId = null;
                if (!empty($this->quickOutProjectId)) {
                    $projectObj = Project::find($this->quickOutProjectId);
                    if ($projectObj) {
                        $projectName = $projectObj->name;
                        $projectId = $projectObj->id;
                    }
                }
                if (!empty(trim($this->quickOutProjectName))) {
                    $projectName = trim($this->quickOutProjectName);
                }

                $refNo = 'OUT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                MaterialOut::create([
                    'reference_number' => $refNo,
                    'date' => $this->quickOutDate ?: date('Y-m-d'),
                    'item_id' => $item->id,
                    'quantity' => $this->quickOutQty,
                    'unit' => $item->unit ?: 'Pcs',
                    'project' => $projectName,
                    'project_id' => $projectId,
                    'pic' => trim($this->quickOutPic),
                    'notes' => trim($this->quickOutNotes) ?: 'Input Cepat Barcode Scan via Smartphone',
                    'user_id' => auth()->id() ?? 1,
                ]);

                $item->decrement('stock', $this->quickOutQty);

                StockLog::create([
                    'reference_number' => $refNo,
                    'item_id' => $item->id,
                    'type' => 'out',
                    'quantity' => $this->quickOutQty,
                    'recipient_or_provider' => trim($this->quickOutPic),
                    'notes' => "Quick Scan Keluar (-{$this->quickOutQty} {$item->unit}) untuk Project: {$projectName} (PIC: " . trim($this->quickOutPic) . ")",
                    'user_id' => auth()->id() ?? 1,
                ]);

                ActivityLog::record(
                    activity: 'Quick Scan Material Keluar',
                    description: "Pengeluaran cepat {$this->quickOutQty} {$item->unit} {$item->name} untuk Project: {$projectName} (PIC: " . trim($this->quickOutPic) . ") [Ref: {$refNo}]",
                    module: 'Material Keluar'
                );

                $this->quickSuccessMessage = "Berhasil mengeluarkan {$this->quickOutQty} {$item->unit} Material Keluar untuk '{$item->name}'! Sisa stok terkini: " . number_format($item->stock) . " {$item->unit}.";
                $this->scannedItem = $item;
            });

            $this->quickActionType = 'success';
            $this->dispatch('quick-action-step-changed', step: 'success');
            $this->dispatch('play-success-sound');
        } catch (\Throwable $e) {
            $this->quickErrorMessage = 'Gagal menyimpan Material Keluar: ' . $e->getMessage();
        }
    }

    public function render()
    {
        if ($this->scannedItemId && (!$this->scannedItem || $this->scannedItem->id !== $this->scannedItemId)) {
            $this->scannedItem = Item::with('category')->find($this->scannedItemId);
        }

        $totalActiveTonnage = Project::getTotalActiveTonnage();
        $activeProjects = Project::getActiveProjects();

        $query = Item::select(['id', 'code_item', 'name', 'category_id', 'unit_id', 'stock', 'unit', 'min_stock', 'requirement_per_ton', 'unit_price', 'location', 'manufacturing_date', 'expiry_date', 'wholesale_unit', 'conversion_factor', 'created_at', 'updated_at'])
            ->with(['category:id,name', 'unitRelation:id,name,symbol']);

        // Search Server-Side
        if (!empty($this->search)) {
            $search = trim($this->search);
            $words = array_filter(preg_split('/\s+/', $search));
            if (!empty($words)) {
                $query->where(function ($q) use ($words, $search) {
                    $q->where(function ($sub) use ($words) {
                        foreach ($words as $word) {
                            $sub->where('name', 'like', "%{$word}%");
                        }
                    })->orWhere('code_item', 'like', "%{$search}%");
                });
            }
        }

        // Filter Kategori (Mendukung Umbrella Category Cat)
        if (!empty($this->category_id)) {
            $catId = $this->category_id;
            $selectedCat = Category::find($catId);
            if ($selectedCat && $selectedCat->isCatUmbrella()) {
                $query->whereIn('category_id', Category::getCatCategoryIds());
            } else {
                $query->where('category_id', $catId);
            }
        }

        // Filter Satuan Terpusat
        if (!empty($this->unit_id)) {
            $uId = $this->unit_id;
            $unitObj = Unit::find($uId);
            $query->where(function ($q) use ($uId, $unitObj) {
                $q->where('unit_id', $uId);
                if ($unitObj) {
                    $q->orWhere('unit', $unitObj->symbol)
                      ->orWhere('unit', $unitObj->name);
                }
            });
        }

        // Filter Status Stok (Memperhitungkan Minimal Stok Otomatis Berbasis Tonase Project)
        if ($this->stock_status === 'low') {
            $query->whereRaw('stock <= COALESCE(requirement_per_ton * ?, min_stock)', [$totalActiveTonnage]);
        } elseif ($this->stock_status === 'empty') {
            $query->where('stock', '<=', 0);
        } elseif ($this->stock_status === 'safe') {
            $query->whereRaw('stock > COALESCE(requirement_per_ton * ?, min_stock)', [$totalActiveTonnage]);
        }

        // Filter Lokasi Storage (STORAGE 1 vs STORAGE 2)
        if (!empty($this->storage_filter)) {
            $query->inStorage($this->storage_filter);
        }

        $query->orderBy($this->sortField, $this->sortDirection);
        if ($this->sortField !== 'id') {
            $query->orderBy('id', $this->sortDirection);
        }

        $items = $query->paginate($this->perPage);

        $categories = Category::select(['id', 'name'])->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();

        // Mini Dashboard Metrics untuk PPIC & Warehouse (Katalog & Status Stok)
        $metrics = Item::selectRaw('
            COUNT(*) as total_items,
            COUNT(CASE WHEN stock <= COALESCE(requirement_per_ton * ?, min_stock) THEN 1 END) as critical_count,
            COUNT(CASE WHEN stock <= 0 THEN 1 END) as out_of_stock_count
        ', [$totalActiveTonnage])->first();

        $totalItemsCount = (int) ($metrics->total_items ?? 0);
        $criticalTotalCount = (int) ($metrics->critical_count ?? 0);
        $outOfStockCount = (int) ($metrics->out_of_stock_count ?? 0);
        $safeStockCount = max(0, $totalItemsCount - $criticalTotalCount);

        // Storage 1 & Storage 2 Counts
        $storage1Count = Item::inStorage(Item::STORAGE_1)->count();
        $storage2Count = Item::inStorage(Item::STORAGE_2)->count();

        // Akumulasi Stok Terpisah per Satuan (mencegah pencampuran satuan berbeda seperti KG dengan PCS)
        $stockByUnitsRaw = Item::selectRaw('COALESCE(unit, "PCS") as unit_symbol, COUNT(*) as item_count, SUM(stock) as total_stock')
            ->groupBy('unit')
            ->orderByDesc('item_count')
            ->get();

        $stockByUnits = $stockByUnitsRaw->map(function ($row) use ($units) {
            $matchedUnit = $units->first(function ($u) use ($row) {
                return strcasecmp($u->symbol, $row->unit_symbol) === 0 || strcasecmp($u->name, $row->unit_symbol) === 0;
            });
            return (object) [
                'unit' => strtoupper($row->unit_symbol),
                'unit_id' => $matchedUnit?->id,
                'name' => $matchedUnit?->name ?? $row->unit_symbol,
                'item_count' => (int) $row->item_count,
                'total_stock' => (float) $row->total_stock,
            ];
        });

        // Jika user sedang memfilter berdasarkan satuan tertentu (misal: KG)
        $selectedUnitObj = !empty($this->unit_id) ? $units->firstWhere('id', $this->unit_id) : null;
        $selectedUnitStock = 0;
        $selectedUnitItemCount = 0;
        if ($selectedUnitObj) {
            $unitRow = $stockByUnits->first(function ($u) use ($selectedUnitObj) {
                return ($u->unit_id && $u->unit_id === $selectedUnitObj->id) 
                    || strcasecmp($u->unit, $selectedUnitObj->symbol) === 0 
                    || strcasecmp($u->unit, $selectedUnitObj->name) === 0;
            });
            if ($unitRow) {
                $selectedUnitStock = $unitRow->total_stock;
                $selectedUnitItemCount = $unitRow->item_count;
            }
        }

        $catPaintingCategory = Category::where('name', 'like', '%Cat%')->first();
        $catPaintingCount = Item::whereIn('category_id', Category::getCatCategoryIds())->count();

        $selectedCatObj = !empty($this->category_id) ? $categories->firstWhere('id', $this->category_id) : null;
        $isFilteringCat = $selectedCatObj 
            && (str_contains(strtolower($selectedCatObj->name), 'cat') 
                || str_contains(strtolower($selectedCatObj->name), 'thinner') 
                || str_contains(strtolower($selectedCatObj->name), 'paint'));

        // Total Nilai Aset Stok Gudang & Filtered Valuation (Hanya dihitung jika user diizinkan melihat harga)
        $canViewPrices = auth()->user() && auth()->user()->canViewPrices();
        $isFiltered = !empty($this->search) || !empty($this->category_id) || !empty($this->unit_id) || !empty($this->stock_status) || !empty($this->storage_filter);
        $totalAssetValue = $canViewPrices ? (float) Item::sum(DB::raw('COALESCE(stock, 0) * COALESCE(unit_price, 0)')) : 0;
        $filteredAssetValue = $canViewPrices 
            ? ($isFiltered 
                ? (float) (clone $query)->reorder()->sum(DB::raw('COALESCE(stock, 0) * COALESCE(unit_price, 0)'))
                : $totalAssetValue)
            : 0;
        $itemsWithPriceCount = $canViewPrices ? (int) Item::where('unit_price', '>', 0)->count() : 0;

        $projects = Project::select(['id', 'name', 'code'])->orderBy('name')->get();

        return view('livewire.item-table-component', [
            'items' => $items,
            'categories' => $categories,
            'units' => $units,
            'projects' => $projects,
            'totalActiveTonnage' => $totalActiveTonnage,
            'activeProjects' => $activeProjects,
            'totalItemsCount' => $totalItemsCount,
            'safeStockCount' => $safeStockCount,
            'criticalTotalCount' => $criticalTotalCount,
            'outOfStockCount' => $outOfStockCount,
            'storage_filter' => $this->storage_filter,
            'storage1Count' => $storage1Count,
            'storage2Count' => $storage2Count,
            'stockByUnits' => $stockByUnits,
            'selectedUnitObj' => $selectedUnitObj,
            'selectedUnitStock' => $selectedUnitStock,
            'selectedUnitItemCount' => $selectedUnitItemCount,
            'catPaintingCount' => $catPaintingCount,
            'catPaintingId' => $catPaintingCategory->id ?? null,
            'isFilteringCat' => $isFilteringCat,
            'totalAssetValue' => $totalAssetValue,
            'filteredAssetValue' => $filteredAssetValue,
            'itemsWithPriceCount' => $itemsWithPriceCount,
            'showQuickActionModal' => $this->showQuickActionModal,
            'scannedItem' => $this->scannedItem,
            'quickActionType' => $this->quickActionType,
        ]);
    }
}
