<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\MaterialReject;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\StockLog;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class MaterialInComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Tab Navigation
    public $activeTab = 'inbound'; // 'inbound' atau 'rejects'

    // Inbound Filters
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedSupplier = '';
    public $selectedItemId = '';
    public array $selectedItemIds = [];
    public $selectedQcStatus = '';
    public $selectedUnit = '';

    // Modal state (Create & Edit)
    public $isModalOpen = false;
    public $isEditMode = false;
    public $selectedId = null;

    // Form fields
    public $date;
    public $item_id = '';
    public $quantity = 1;
    public $unit = 'PCS';
    public $supplier = '';
    public $received_by = '';
    public $notes = '';
    public $manufacturing_date = '';
    public $expiry_date = '';
    public $qc_status = 'pending_qc';

    // Direct Allocation to Project (Penerimaan Langsung Keluar ke Proyek)
    public bool $directToProject = false;
    public $directProjectId = '';
    public $directProjectName = '';
    public $directPic = '';
    public $directNotes = '';

    // Multi-Item Batch Inbound State
    public array $inboundItems = [];

    // Wholesale UOM Conversion states
    public $input_mode = 'base'; // 'base' atau 'wholesale'
    public $wholesale_qty = null;
    public $wholesale_unit = null;
    public $conversion_factor = 1;

    // Selected item preview
    public $selectedItemStock = null;
    public $selectedItemUnit = null;
    public $isCatItem = false;
    public bool $isConsumableItem = false;

    // Off-Cut (Sisa Potongan Raw Material) Return Modal States
    public bool $isOffCutModalOpen = false;
    public $offCutParentItemId = '';
    public $offCutDimension = '';
    public $offCutQuantity = 1;
    public $offCutUnit = 'Pcs';
    public $offCutOriginProjectId = '';
    public $offCutOriginProjectName = '';
    public $offCutHeatNumber = '';
    public $offCutLocation = 'STORAGE 2 (Area Sisa / Off-Cut)';
    public $offCutNotes = '';

    // Terima Barang dari Purchase Order (PO) Modal States
    public bool $isReceivePoModalOpen = false;
    public $selectedPoId = '';
    public string $poDeliveryOrderNumber = '';
    public string $poReceivedDate = '';
    public string $poReceivedBy = '';
    public string $poLocation = 'STORAGE 1';
    public string $poGeneralNotes = '';
    public array $poItemsToReceive = [];

    // QC Inspection Modal
    public bool $isQcModalOpen = false;
    public ?int $qcSelectedId = null;
    public $qcSelectedRecord = null;
    public string $qcDecision = 'approved'; // 'approved' (OK) atau 'rejected' (Reject)
    public string $qcNotes = '';

    // Print Receipt Modal
    public bool $isPrintReceiptModalOpen = false;
    public $receiptRecord = null;

    // =========================================================================
    // Material Rusak & Reject (Report / Reject Material) States
    // =========================================================================
    public $rejectSearch = '';
    public $rejectDateFrom = '';
    public $rejectDateTo = '';
    public $rejectSupplier = '';
    public $rejectDamageType = '';
    public $rejectDisposition = '';
    public $rejectStatus = '';

    // Modal Catat / Edit Reject
    public bool $isRejectModalOpen = false;
    public bool $isRejectEditMode = false;
    public ?int $selectedRejectId = null;
    public $rejectItemId = '';
    public $rejectMaterialInId = null;
    public $rejectDate = '';
    public $rejectSupplierName = '';
    public $rejectPoNumber = '';
    public $rejectQuantity = 1;
    public $rejectUnit = 'PCS';
    public $rejectDamageTypeChoice = 'Cacat Fisik / Pecah';
    public $rejectDispositionChoice = 'karantina';
    public $rejectDescription = '';

    // Modal Update Disposisi & Status Retur
    public bool $isDispositionModalOpen = false;
    public ?int $dispositionSelectedId = null;
    public $dispositionRecord = null;
    public $dispositionAction = 'karantina';
    public $dispositionStatus = 'pending';
    public $resolutionNotes = '';

    protected $rules = [
        'date' => 'required|date',
        'item_id' => 'required|exists:items,id',
        'quantity' => 'required|numeric|min:0.001',
        'supplier' => 'required|string|max:255',
        'received_by' => 'required|string|max:255',
        'notes' => 'nullable|string|max:500',
        'manufacturing_date' => 'nullable|date',
        'qc_status' => 'required|string|in:pending_qc,approved,rejected',
    ];

    public function mount()
    {
        $this->date = date('Y-m-d');
        $this->received_by = auth()->user()->name ?? 'Petugas Gudang';
        $this->rejectDate = date('Y-m-d');

        if (request()->query('tab') === 'rejects') {
            $this->activeTab = 'rejects';
        }

        // Pastikan penerimaan barang kategori consumable yang pernah tersimpan pending langsung disetujui & masuk stok
        $this->syncPendingConsumables();
    }

    private function syncPendingConsumables()
    {
        // Early check to avoid expensive relation lookups when no pending records exist
        if (!MaterialIn::where('qc_status', 'pending_qc')->exists()) {
            return;
        }

        $pending = MaterialIn::with('item.category')
            ->where('qc_status', 'pending_qc')
            ->whereHas('item.category', function ($q) {
                $q->where('name', 'like', '%consumable%');
            })
            ->get();

        foreach ($pending as $record) {
            DB::transaction(function () use ($record) {
                $record->update([
                    'qc_status' => 'approved',
                    'qc_notes' => 'Bebas QC - Material Consumable otomatis disetujui & masuk stok',
                    'qc_at' => now(),
                    'qc_by' => auth()->id() ?? 1,
                ]);

                if ($record->item) {
                    $record->item->increment('stock', $record->quantity);
                    StockLog::create([
                        'reference_number' => $record->reference_number,
                        'item_id' => $record->item_id,
                        'type' => 'in',
                        'quantity' => $record->quantity,
                        'recipient_or_provider' => trim($record->supplier) ?: (trim($record->received_by) ?: 'Vendor'),
                        'notes' => 'Penerimaan Material Consumable (Bebas QC - Masuk Stok Otomatis)',
                        'user_id' => auth()->id() ?? 1,
                    ]);
                }
            });
        }
    }

    public function setFilterThisMonth()
    {
        $this->dateFrom = date('Y-m-01');
        $this->dateTo = date('Y-m-t');
        $this->resetPage();
    }

    public function setFilterLast7Days()
    {
        $this->dateTo = date('Y-m-d');
        $this->dateFrom = date('Y-m-d', strtotime('-7 days'));
        $this->resetPage();
    }

    public function updatingSelectedUnit()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->selectedSupplier = '';
        $this->selectedItemId = '';
        $this->selectedItemIds = [];
        $this->selectedQcStatus = '';
        $this->selectedUnit = '';
        $this->resetPage();
    }

    public function updatedItemId($val)
    {
        if ($val) {
            $item = Item::with('category')->find($val);
            if ($item) {
                $this->selectedItemStock = $item->stock;
                $this->selectedItemUnit = $item->unit;
                $this->unit = $item->unit;
                $this->wholesale_unit = $item->wholesale_unit;
                $this->conversion_factor = $item->conversion_factor ?: 1;
                $this->isCatItem = $item->isCat();
                $this->isConsumableItem = $item->isConsumable();

                // Consumable bebas QC, otomatis langsung disetujui (approved) & masuk ke stok
                if ($this->isConsumableItem) {
                    $this->qc_status = 'approved';
                } else {
                    if (!$this->isEditMode) {
                        $this->qc_status = 'pending_qc';
                    }
                }

                if ($this->isCatItem && empty($this->manufacturing_date)) {
                    $this->manufacturing_date = $item->manufacturing_date ? $item->manufacturing_date->format('Y-m-d') : date('Y-m-d');
                }
                if ($this->isCatItem && empty($this->expiry_date)) {
                    $this->expiry_date = $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '';
                }
            } else {
                $this->resetItemPreview();
            }
        } else {
            $this->resetItemPreview();
        }
    }

    public function updatedWholesaleQty($val)
    {
        $val = str_replace(',', '.', (string)$val);
        if ($this->input_mode === 'wholesale' && is_numeric($val) && $this->conversion_factor > 1) {
            $this->quantity = (float) $val * (float) $this->conversion_factor;
        }
    }

    public function setInputMode($mode)
    {
        $this->input_mode = $mode;
        $val = str_replace(',', '.', (string)$this->wholesale_qty);
        if ($mode === 'wholesale' && is_numeric($val) && $this->conversion_factor > 1) {
            $this->quantity = (float) $val * (float) $this->conversion_factor;
        }
    }

    private function resetItemPreview()
    {
        $this->selectedItemStock = null;
        $this->selectedItemUnit = null;
        $this->wholesale_unit = null;
        $this->conversion_factor = 1;
        $this->isCatItem = false;
        $this->isConsumableItem = false;
    }

    public function getBlankInboundItem(): array
    {
        return [
            'item_id' => '',
            'quantity' => 1,
            'unit' => 'PCS',
            'wholesale_qty' => null,
            'wholesale_unit' => null,
            'conversion_factor' => 1,
            'input_mode' => 'base',
            'manufacturing_date' => '',
            'expiry_date' => '',
            'notes' => '',
            'is_cat' => false,
            'is_consumable' => false,
            'current_stock' => null,
        ];
    }

    public function addInboundItemRow(): void
    {
        $this->inboundItems[] = $this->getBlankInboundItem();
    }

    public function removeInboundItemRow(int $index): void
    {
        if (count($this->inboundItems) > 1) {
            unset($this->inboundItems[$index]);
            $this->inboundItems = array_values($this->inboundItems);
        }
    }

    public function updatedInboundItems($value, $key): void
    {
        $parts = explode('.', $key);
        if (count($parts) >= 2) {
            $index = (int) $parts[0];
            $field = $parts[1];

            if (isset($this->inboundItems[$index])) {
                if ($field === 'item_id') {
                    if ($value) {
                        $item = Item::with('category')->find($value);
                        if ($item) {
                            $this->inboundItems[$index]['unit'] = $item->unit;
                            $this->inboundItems[$index]['wholesale_unit'] = $item->wholesale_unit;
                            $this->inboundItems[$index]['conversion_factor'] = $item->conversion_factor ?: 1;
                            $this->inboundItems[$index]['current_stock'] = $item->stock;
                            $this->inboundItems[$index]['is_cat'] = $item->isCat();
                            $this->inboundItems[$index]['is_consumable'] = $item->isConsumable();
                            if ($item->isCat()) {
                                $this->inboundItems[$index]['manufacturing_date'] = $item->manufacturing_date ? $item->manufacturing_date->format('Y-m-d') : date('Y-m-d');
                                $this->inboundItems[$index]['expiry_date'] = $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '';
                            } else {
                                $this->inboundItems[$index]['manufacturing_date'] = '';
                                $this->inboundItems[$index]['expiry_date'] = '';
                            }
                        }
                    } else {
                        $this->inboundItems[$index]['unit'] = 'PCS';
                        $this->inboundItems[$index]['wholesale_unit'] = null;
                        $this->inboundItems[$index]['conversion_factor'] = 1;
                        $this->inboundItems[$index]['current_stock'] = null;
                        $this->inboundItems[$index]['is_cat'] = false;
                        $this->inboundItems[$index]['is_consumable'] = false;
                        $this->inboundItems[$index]['manufacturing_date'] = '';
                        $this->inboundItems[$index]['expiry_date'] = '';
                    }
                } elseif ($field === 'wholesale_qty') {
                    $val = str_replace(',', '.', (string)$value);
                    $factor = (float) ($this->inboundItems[$index]['conversion_factor'] ?? 1);
                    if (($this->inboundItems[$index]['input_mode'] ?? 'base') === 'wholesale' && is_numeric($val) && $factor > 1) {
                        $this->inboundItems[$index]['quantity'] = (float) $val * $factor;
                    }
                }
            }
        }
    }

    public function setRowInputMode(int $index, string $mode): void
    {
        if (isset($this->inboundItems[$index])) {
            $this->inboundItems[$index]['input_mode'] = $mode;
            $val = str_replace(',', '.', (string)($this->inboundItems[$index]['wholesale_qty'] ?? ''));
            $factor = (float) ($this->inboundItems[$index]['conversion_factor'] ?? 1);
            if ($mode === 'wholesale' && is_numeric($val) && $factor > 1) {
                $this->inboundItems[$index]['quantity'] = (float) $val * $factor;
            }
        }
    }

    public function openCreateModal()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC hanya memiliki hak akses melihat riwayat.');
            return;
        }

        $this->resetForm();
        $this->date = date('Y-m-d');
        $this->received_by = auth()->user()->name ?? 'Petugas Gudang';
        $this->qc_status = 'pending_qc';
        $this->isConsumableItem = false;
        $this->input_mode = 'base';
        $this->isEditMode = false;
        $this->directToProject = false;
        $this->directProjectId = '';
        $this->directProjectName = '';
        $this->directPic = '';
        $this->directNotes = '';
        $this->inboundItems = [
            $this->getBlankInboundItem()
        ];
        $this->isModalOpen = true;
    }

    public function openEditModal($id)
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC hanya memiliki hak akses melihat riwayat.');
            return;
        }

        $record = MaterialIn::findOrFail($id);
        $this->selectedId = $record->id;
        $this->date = $record->date->format('Y-m-d');
        $this->item_id = $record->item_id;
        $this->quantity = $record->quantity;
        $this->unit = $record->unit;
        $this->supplier = $record->supplier;
        $this->received_by = $record->received_by;
        $this->notes = $record->notes;
        $this->qc_status = $record->qc_status ?: 'approved';
        $this->manufacturing_date = $record->manufacturing_date ? $record->manufacturing_date->format('Y-m-d') : '';
        $this->expiry_date = $record->expiry_date ? $record->expiry_date->format('Y-m-d') : '';
        $this->wholesale_qty = $record->wholesale_qty;
        $this->wholesale_unit = $record->wholesale_unit;

        $item = Item::with('category')->find($record->item_id);
        if ($item) {
            $this->selectedItemStock = $item->stock;
            $this->selectedItemUnit = $item->unit;
            $this->conversion_factor = $item->conversion_factor ?: 1;
            $this->isCatItem = $item->isCat();
            $this->isConsumableItem = $item->isConsumable();
        }

        if ($this->isConsumableItem) {
            $this->qc_status = 'approved';
        }

        $this->input_mode = !empty($record->wholesale_qty) ? 'wholesale' : 'base';
        $this->isEditMode = true;
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
        self::clearPerformanceCaches();
    }

    public function openOffCutModal()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC hanya memiliki hak akses melihat riwayat.');
            return;
        }

        $this->resetOffCutForm();
        $this->date = date('Y-m-d');
        $this->isOffCutModalOpen = true;
    }

    public function closeOffCutModal()
    {
        $this->isOffCutModalOpen = false;
        $this->resetOffCutForm();
        self::clearPerformanceCaches();
    }

    public function resetOffCutForm()
    {
        $this->offCutParentItemId = '';
        $this->offCutDimension = '';
        $this->offCutQuantity = 1;
        $this->offCutUnit = 'Pcs';
        $this->offCutOriginProjectId = '';
        $this->offCutOriginProjectName = '';
        $this->offCutHeatNumber = '';
        $this->offCutLocation = 'STORAGE 2 (Area Sisa / Off-Cut)';
        $this->offCutNotes = '';
        $this->resetErrorBag();
    }

    public function updatedOffCutParentItemId($val)
    {
        if ($val) {
            $parent = Item::find($val);
            if ($parent) {
                $this->offCutUnit = $parent->unit ?: 'Pcs';
            }
        }
    }

    public function submitOffCutReturn()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC tidak memiliki izin input retur sisa.');
            return;
        }

        $this->validate([
            'offCutParentItemId' => 'required|exists:items,id',
            'offCutDimension' => 'required|string|max:255',
            'offCutQuantity' => 'required|numeric|min:0.01',
            'offCutUnit' => 'required|string|max:50',
            'offCutOriginProjectName' => 'nullable|string|max:255',
            'offCutHeatNumber' => 'nullable|string|max:100',
            'offCutLocation' => 'nullable|string|max:255',
            'offCutNotes' => 'nullable|string',
        ], [
            'offCutParentItemId.required' => 'Pilih material asal yang dipotong.',
            'offCutDimension.required' => 'Masukkan dimensi/ukuran sisa (misal: Panjang 2.4 m atau 1200 x 800 mm).',
            'offCutQuantity.required' => 'Jumlah sisa wajib diisi.',
            'offCutQuantity.min' => 'Jumlah sisa minimal 0.01.',
            'offCutUnit.required' => 'Satuan sisa wajib diisi.',
        ]);

        DB::transaction(function () {
            $parent = Item::with('category')->findOrFail($this->offCutParentItemId);
            $offCutCategory = Category::firstOrCreate(
                ['name' => 'Raw Material - Off Cut'],
                ['description' => 'Sisa potongan raw material layak pakai dari proses fabrikasi/cutting']
            );

            $cleanDim = trim($this->offCutDimension);
            $originPrj = trim($this->offCutOriginProjectName ?? '');
            $heatNo = trim($this->offCutHeatNumber ?? '');
            $loc = !empty($this->offCutLocation) ? trim($this->offCutLocation) : 'STORAGE 2 (Area Sisa / Off-Cut)';

            // Cek apakah item off-cut dengan spesifikasi identik sudah ada di database
            $existing = Item::where('parent_item_id', $parent->id)
                ->where('dimension', $cleanDim)
                ->when(!empty($heatNo), fn($q) => $q->where('heat_number', $heatNo))
                ->first();

            if ($existing) {
                $offCutItem = $existing;
                $offCutItem->increment('stock', (float) $this->offCutQuantity);
                if (!empty($originPrj) && empty($offCutItem->origin_project)) {
                    $offCutItem->update(['origin_project' => $originPrj]);
                }
            } else {
                $offCutName = "{$parent->name} (Off-Cut: {$cleanDim})";
                $codeItem = Item::generateUniqueCodeItem($offCutCategory->id);

                $offCutItem = Item::create([
                    'category_id' => $offCutCategory->id,
                    'unit_id' => $parent->unit_id,
                    'code_item' => $codeItem,
                    'name' => $offCutName,
                    'brand' => $parent->brand,
                    'is_offcut' => true,
                    'parent_item_id' => $parent->id,
                    'dimension' => $cleanDim,
                    'origin_project' => $originPrj ?: null,
                    'heat_number' => $heatNo ?: null,
                    'stock' => (float) $this->offCutQuantity,
                    'unit' => !empty($this->offCutUnit) ? $this->offCutUnit : $parent->unit,
                    'min_stock' => 0,
                    'location' => $loc,
                    'unit_price' => $parent->unit_price ?? 0,
                ]);
            }

            $refNo = 'IN-OFF-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $supplierLabel = !empty($originPrj) ? "Retur Workshop ({$originPrj})" : 'Retur Sisa Produksi Workshop';
            $logNotes = "Retur sisa potongan dari {$parent->name} (Dimensi: {$cleanDim})" . ($originPrj ? " ex-Project: {$originPrj}" : '');
            if (!empty($this->offCutNotes)) {
                $logNotes .= " - Catatan: " . trim($this->offCutNotes);
            }

            MaterialIn::create([
                'reference_number' => $refNo,
                'date' => $this->date ?: now()->toDateString(),
                'item_id' => $offCutItem->id,
                'unit_id' => $offCutItem->unit_id,
                'quantity' => (float) $this->offCutQuantity,
                'unit' => $offCutItem->unit,
                'is_offcut' => true,
                'parent_item_id' => $parent->id,
                'dimension' => $cleanDim,
                'origin_project' => $originPrj ?: null,
                'heat_number' => $heatNo ?: null,
                'supplier' => $supplierLabel,
                'received_by' => auth()->user()?->name ?? 'Petugas Gudang',
                'notes' => $logNotes,
                'qc_status' => 'approved',
                'qc_by' => auth()->id() ?? 1,
                'qc_at' => now(),
                'qc_notes' => 'Sisa material layak pakai telah diperiksa fisik di bengkel/Storage 2',
                'user_id' => auth()->id() ?? 1,
            ]);

            StockLog::create([
                'reference_number' => $refNo,
                'item_id' => $offCutItem->id,
                'type' => 'in',
                'quantity' => (float) $this->offCutQuantity,
                'recipient_or_provider' => $supplierLabel,
                'notes' => $logNotes,
                'user_id' => auth()->id() ?? 1,
            ]);

            ActivityLog::record(
                'Retur Sisa Potongan Raw Material',
                "Mencatat penerimaan sisa potongan {$this->offCutQuantity} {$offCutItem->unit} {$offCutItem->name} ke Storage 2.",
                'Material Masuk'
            );

            session()->flash('success', "Berhasil mencatat retur sisa potongan {$offCutItem->name} (+{$this->offCutQuantity} {$offCutItem->unit}) ke Storage 2!");
            $this->dispatch('swal:success', title: 'Berhasil!', message: "Sisa potongan {$offCutItem->name} berhasil dicatat dan masuk ke inventaris Storage 2!");
        });

        $this->closeOffCutModal();
    }

    public function openReceivePoModal()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC hanya memiliki hak akses melihat riwayat.');
            return;
        }

        $this->poReceivedDate = date('Y-m-d');
        $this->poReceivedBy = auth()->user()->name ?? 'Staff Gudang';
        $this->selectedPoId = '';
        $this->poDeliveryOrderNumber = '';
        $this->poLocation = 'STORAGE 1';
        $this->poGeneralNotes = '';
        $this->poItemsToReceive = [];
        $this->resetErrorBag();
        $this->isReceivePoModalOpen = true;
    }

    public function closeReceivePoModal()
    {
        $this->isReceivePoModalOpen = false;
        $this->poItemsToReceive = [];
        $this->resetErrorBag();
        self::clearPerformanceCaches();
    }

    public function updatedSelectedPoId($val)
    {
        $this->poItemsToReceive = [];

        if (empty($val)) {
            return;
        }

        $po = PurchaseOrder::with(['items.item', 'project'])->find($val);
        if (!$po) {
            return;
        }

        $this->poGeneralNotes = "Penerimaan PO #{$po->po_number} - Supplier: {$po->supplier}";

        foreach ($po->items as $item) {
            $remaining = (float) $item->quantity_remaining;
            if ($remaining > 0) {
                $this->poItemsToReceive[] = [
                    'po_item_id' => $item->id,
                    'item_id' => $item->item_id,
                    'item_code' => $item->item ? $item->item->item_code : '-',
                    'item_name' => $item->item ? $item->item->name : '-',
                    'quantity_ordered' => (float) $item->quantity_ordered,
                    'quantity_received_so_far' => (float) $item->quantity_received,
                    'quantity_remaining' => $remaining,
                    'quantity_to_receive' => $remaining,
                    'unit' => $item->unit,
                    'location' => 'STORAGE 1',
                    'manufacturing_date' => null,
                    'expiry_date' => null,
                    'notes' => '',
                    'selected' => true,
                ];
            }
        }
    }

    public function submitReceivePo()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC tidak memiliki izin penerimaan barang.');
            return;
        }

        $this->validate([
            'selectedPoId' => 'required|exists:purchase_orders,id',
            'poDeliveryOrderNumber' => 'required|string|max:100',
            'poReceivedDate' => 'required|date',
            'poReceivedBy' => 'required|string|max:255',
            'poItemsToReceive' => 'required|array|min:1',
        ], [
            'selectedPoId.required' => 'Silakan pilih Purchase Order yang akan diterima.',
            'poDeliveryOrderNumber.required' => 'Nomor Surat Jalan / Delivery Order (DO) vendor wajib diisi.',
            'poReceivedDate.required' => 'Tanggal penerimaan fisik wajib diisi.',
            'poReceivedBy.required' => 'Nama staff penerima gudang wajib diisi.',
            'poItemsToReceive.required' => 'Tidak ada item yang dapat diterima pada PO ini.',
        ]);

        $itemsToProcess = [];
        foreach ($this->poItemsToReceive as $idx => $row) {
            if (!empty($row['selected']) && (float) ($row['quantity_to_receive'] ?? 0) > 0) {
                $qty = (float) $row['quantity_to_receive'];
                $remaining = (float) $row['quantity_remaining'];

                if ($qty > $remaining) {
                    $this->addError("poItemsToReceive.{$idx}.quantity_to_receive", "Jumlah fisik yang dihitung ({$qty}) melebihi sisa pesanan ({$remaining}).");
                    return;
                }

                $itemsToProcess[] = $row;
            }
        }

        if (empty($itemsToProcess)) {
            session()->flash('error', 'Silakan centang minimal 1 item barang yang diterima fisik dengan jumlah lebih dari 0.');
            return;
        }

        DB::transaction(function () use ($itemsToProcess) {
            $po = PurchaseOrder::lockForUpdate()->findOrFail($this->selectedPoId);
            $totalReceivedItems = 0;

            foreach ($itemsToProcess as $row) {
                $poItem = PurchaseOrderItem::lockForUpdate()->findOrFail($row['po_item_id']);
                $item = Item::lockForUpdate()->findOrFail($row['item_id']);
                $qty = (float) $row['quantity_to_receive'];
                $unit = $poItem->unit ?: $item->unit;
                $location = !empty($row['location']) ? $row['location'] : $this->poLocation;

                $refNo = 'IN-PO-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                // 1. Buat record MaterialIn (qc_status = 'approved' atas instruksi: tidak perlu QC untuk penerimaan PO)
                MaterialIn::create([
                    'purchase_order_id' => $po->id,
                    'purchase_order_item_id' => $poItem->id,
                    'reference_number' => $refNo,
                    'po_number' => trim($this->poDeliveryOrderNumber),
                    'delivery_order_number' => trim($this->poDeliveryOrderNumber),
                    'date' => $this->poReceivedDate,
                    'item_id' => $item->id,
                    'unit_id' => $item->unit_id,
                    'quantity' => $qty,
                    'unit' => $unit,
                    'supplier' => $po->supplier,
                    'received_by' => trim($this->poReceivedBy),
                    'location' => $location,
                    'notes' => !empty($row['notes']) ? trim($row['notes']) : $this->poGeneralNotes,
                    'manufacturing_date' => !empty($row['manufacturing_date']) ? $row['manufacturing_date'] : null,
                    'expiry_date' => !empty($row['expiry_date']) ? $row['expiry_date'] : null,
                    'qc_status' => 'approved',
                    'qc_by' => auth()->id() ?? 1,
                    'qc_at' => now(),
                    'qc_notes' => "ACC Otomatis dari PO #{$po->po_number} (Surat Jalan: {$this->poDeliveryOrderNumber})",
                    'user_id' => auth()->id() ?? 1,
                ]);

                // 2. Tambah stok master barang
                $item->increment('stock', $qty);

                if (!empty($row['expiry_date'])) {
                    $item->update(['expiry_date' => $row['expiry_date']]);
                }

                // 3. Catat Stock Log
                StockLog::create([
                    'reference_number' => $refNo,
                    'item_id' => $item->id,
                    'type' => 'in',
                    'quantity' => $qty,
                    'recipient_or_provider' => $po->supplier,
                    'notes' => "Penerimaan PO #{$po->po_number} (SJ: {$this->poDeliveryOrderNumber})",
                    'user_id' => auth()->id() ?? 1,
                ]);

                // 4. Update quantity_received di PurchaseOrderItem
                $poItem->quantity_received = (float) $poItem->quantity_received + $qty;
                $poItem->save();

                $totalReceivedItems++;
            }

            // 5. Update status PO (open -> partial -> completed)
            $po->recalculateStatus();

            ActivityLog::record(
                activity: 'Penerimaan Barang PO',
                description: 'Gudang menerima ' . $totalReceivedItems . ' item dari PO #' . $po->po_number . ' (Surat Jalan: ' . $this->poDeliveryOrderNumber . ') dan langsung di-ACC ke stok.',
                module: 'Material Masuk'
            );

            session()->flash('success', "Penerimaan PO #{$po->po_number} berhasil di-ACC! {$totalReceivedItems} item telah otomatis ditambahkan ke stok gudang.");
            $this->dispatch('swal:success', title: 'Berhasil!', message: "Penerimaan barang dari PO #{$po->po_number} telah di-ACC dan otomatis masuk ke inventaris stok gudang!");
        });

        $this->closeReceivePoModal();
    }

    private function resetForm()
    {
        $this->selectedId = null;
        $this->item_id = '';
        $this->quantity = 1;
        $this->unit = 'PCS';
        $this->supplier = '';
        $this->notes = '';
        $this->manufacturing_date = '';
        $this->expiry_date = '';
        $this->qc_status = 'pending_qc';
        $this->wholesale_qty = null;
        $this->directToProject = false;
        $this->directProjectId = '';
        $this->directProjectName = '';
        $this->directPic = '';
        $this->directNotes = '';
        $this->inboundItems = [
            $this->getBlankInboundItem()
        ];
        $this->resetItemPreview();
        $this->resetErrorBag();
    }

    public function save()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC hanya memiliki hak akses melihat.');
            return;
        }

        // 1. MODE EDIT: Edit single item yang sudah ada
        if ($this->isEditMode) {
            if ($this->wholesale_qty !== null) {
                $this->wholesale_qty = str_replace(',', '.', (string) $this->wholesale_qty);
            }
            if ($this->quantity !== null) {
                $this->quantity = str_replace(',', '.', (string) $this->quantity);
            }
            if ($this->input_mode === 'wholesale' && is_numeric($this->wholesale_qty) && $this->conversion_factor > 1) {
                $this->quantity = (float) $this->wholesale_qty * (float) $this->conversion_factor;
            }

            $this->validate();

            $item = Item::with('category')->findOrFail($this->item_id);
            $isConsumable = $item->isConsumable();
            if ($isConsumable) {
                $this->qc_status = 'approved';
            }

            DB::transaction(function () use ($item, $isConsumable) {
                $existing = MaterialIn::findOrFail($this->selectedId);
                $oldQty = $existing->quantity;
                $oldStatus = $existing->qc_status;
                $newQty = $this->quantity;
                $newStatus = $isConsumable ? 'approved' : $this->qc_status;

                $existing->update([
                    'date' => $this->date,
                    'quantity' => $this->quantity,
                    'unit_id' => $item->unit_id,
                    'unit' => $this->unit ?: $item->unit,
                    'wholesale_qty' => $this->input_mode === 'wholesale' ? $this->wholesale_qty : null,
                    'wholesale_unit' => $this->input_mode === 'wholesale' ? $this->wholesale_unit : null,
                    'supplier' => trim($this->supplier),
                    'received_by' => trim($this->received_by),
                    'notes' => $this->notes,
                    'manufacturing_date' => !empty($this->manufacturing_date) ? $this->manufacturing_date : null,
                    'expiry_date' => !empty($this->expiry_date) ? $this->expiry_date : null,
                    'qc_status' => $newStatus,
                    'qc_notes' => $isConsumable ? 'Bebas QC (Consumable langsung masuk ke stok)' : $existing->qc_notes,
                ]);

                if ($oldStatus === 'approved' && $newStatus === 'approved') {
                    $diff = $newQty - $oldQty;
                    if ($diff !== 0) {
                        $item->increment('stock', $diff);
                    }
                } elseif ($oldStatus !== 'approved' && $newStatus === 'approved') {
                    $item->increment('stock', $newQty);
                } elseif ($oldStatus === 'approved' && $newStatus !== 'approved') {
                    $item->decrement('stock', $oldQty);
                }

                ActivityLog::record(
                    'Update Penerimaan Material',
                    "Memperbarui data penerimaan ({$item->name})",
                    'Material Masuk'
                );

                session()->flash('success', "Data penerimaan berhasil diperbarui.");
                $this->dispatch('swal:success', title: 'Berhasil!', message: "Data penerimaan berhasil diperbarui.");
            });

            $this->closeModal();
            return;
        }

        // 2. MODE CREATE: Input Banyak Barang Sekaligus (Multi-Item Batch)
        $hasInboundItems = !empty($this->inboundItems) && (
            count($this->inboundItems) > 1 || !empty($this->inboundItems[0]['item_id'])
        );

        if ($hasInboundItems) {
            foreach ($this->inboundItems as $k => $itemRow) {
                if (isset($itemRow['quantity'])) {
                    $this->inboundItems[$k]['quantity'] = str_replace(',', '.', (string) $itemRow['quantity']);
                }
                if (isset($itemRow['wholesale_qty'])) {
                    $this->inboundItems[$k]['wholesale_qty'] = str_replace(',', '.', (string) $itemRow['wholesale_qty']);
                }
            }

            $validationRules = [
                'date' => 'required|date',
                'supplier' => 'required|string|max:255',
                'received_by' => 'required|string|max:255',
                'qc_status' => 'required|string|in:pending_qc,approved',
                'notes' => 'nullable|string|max:500',
                'inboundItems' => 'required|array|min:1',
                'inboundItems.*.item_id' => 'required|exists:items,id',
                'inboundItems.*.quantity' => 'required|numeric|min:0.001',
                'inboundItems.*.unit' => 'nullable|string|max:50',
                'inboundItems.*.manufacturing_date' => 'nullable|date',
                'inboundItems.*.expiry_date' => 'nullable|date',
                'inboundItems.*.notes' => 'nullable|string|max:500',
            ];

            $validationMessages = [
                'supplier.required' => 'Supplier / vendor penyedia wajib diisi.',
                'received_by.required' => 'Petugas penerima gudang wajib diisi.',
                'inboundItems.*.item_id.required' => 'Silakan pilih barang pada setiap baris.',
                'inboundItems.*.item_id.exists' => 'Barang yang dipilih tidak valid.',
                'inboundItems.*.quantity.required' => 'Jumlah barang wajib diisi.',
                'inboundItems.*.quantity.min' => 'Jumlah barang minimal lebih dari 0.',
            ];

            if ($this->directToProject) {
                if (empty($this->directProjectId) && empty($this->directProjectName)) {
                    $validationRules['directProjectId'] = 'required';
                    $validationMessages['directProjectId.required'] = 'Silakan pilih proyek tujuan alokasi.';
                }
                $validationRules['directPic'] = 'required|string|max:255';
                $validationRules['directNotes'] = 'nullable|string|max:500';
                $validationMessages['directPic.required'] = 'Nama PIC penerima lapangan wajib diisi.';
            }

            $this->validate($validationRules, $validationMessages);

            $createdCount = 0;
            $supplierName = trim($this->supplier);
            $receivedByName = trim($this->received_by);
            $globalNotes = trim($this->notes);
            $isDirect = (bool) $this->directToProject;
            $targetProject = null;
            if ($isDirect) {
                if (!empty($this->directProjectId)) {
                    $targetProject = Project::find($this->directProjectId);
                }
                if (!$targetProject && !empty($this->directProjectName)) {
                    $targetProject = Project::where('name', trim($this->directProjectName))->first();
                    if (!$targetProject) {
                        $targetProject = Project::firstOrCreate(
                            ['name' => trim($this->directProjectName)],
                            [
                                'code' => 'PRJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                                'status' => 'Ongoing',
                                'location' => trim($this->directProjectName),
                                'created_by' => auth()->id() ?? 1,
                            ]
                        );
                    }
                }
            }
            $targetProjectName = $targetProject ? $targetProject->name : trim($this->directProjectName);
            $targetPic = $isDirect ? trim($this->directPic) : '';
            $directNotesText = $isDirect ? trim($this->directNotes) : '';
            $defaultQcStatus = $isDirect ? 'approved' : $this->qc_status;

            DB::transaction(function () use (
                &$createdCount, $supplierName, $receivedByName, $globalNotes, $defaultQcStatus,
                $isDirect, $targetProject, $targetProjectName, $targetPic, $directNotesText
            ) {
                foreach ($this->inboundItems as $row) {
                    $item = Item::with('category')->findOrFail($row['item_id']);
                    $isConsumable = $item->isConsumable();
                    $qty = (float) $row['quantity'];
                    $unit = !empty($row['unit']) ? $row['unit'] : $item->unit;
                    $rowNotes = !empty($row['notes']) ? trim($row['notes']) : $globalNotes;

                    $rowQcStatus = ($isDirect || $isConsumable) ? 'approved' : $defaultQcStatus;
                    $rowQcNotes = $isDirect
                        ? "Direct Allocation ke Proyek: {$targetProjectName} (PIC: {$targetPic})"
                        : ($isConsumable ? 'Bebas QC - Material Consumable langsung masuk ke stok' : null);

                    $refNo = 'IN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                    $mfgDate = !empty($row['manufacturing_date']) ? $row['manufacturing_date'] : null;
                    $expDate = !empty($row['expiry_date']) ? $row['expiry_date'] : null;

                    $wholesaleQty = (!empty($row['input_mode']) && $row['input_mode'] === 'wholesale') ? ($row['wholesale_qty'] ?? null) : null;
                    $wholesaleUnit = (!empty($row['input_mode']) && $row['input_mode'] === 'wholesale') ? ($row['wholesale_unit'] ?? null) : null;

                    MaterialIn::create([
                        'reference_number' => $refNo,
                        'date' => $this->date,
                        'item_id' => $item->id,
                        'unit_id' => $item->unit_id,
                        'quantity' => $qty,
                        'unit' => $unit,
                        'wholesale_qty' => $wholesaleQty,
                        'wholesale_unit' => $wholesaleUnit,
                        'supplier' => $supplierName,
                        'received_by' => $receivedByName,
                        'notes' => $rowNotes,
                        'manufacturing_date' => $mfgDate,
                        'expiry_date' => $expDate,
                        'qc_status' => $rowQcStatus,
                        'qc_by' => $rowQcStatus === 'approved' ? (auth()->id() ?? 1) : null,
                        'qc_at' => $rowQcStatus === 'approved' ? now() : null,
                        'qc_notes' => $rowQcNotes,
                        'user_id' => auth()->id() ?? 1,
                    ]);

                    if ($isDirect) {
                        $outRefNo = 'OUT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                        $outNotes = !empty($directNotesText)
                            ? $directNotesText
                            : "Alokasi langsung dari penerimaan supplier {$supplierName} (Ref Masuk: {$refNo})";

                        MaterialOut::create([
                            'reference_number' => $outRefNo,
                            'date' => $this->date,
                            'item_id' => $item->id,
                            'unit_id' => $item->unit_id,
                            'quantity' => $qty,
                            'unit' => $unit,
                            'wholesale_qty' => $wholesaleQty,
                            'wholesale_unit' => $wholesaleUnit,
                            'project_id' => $targetProject?->id,
                            'project' => $targetProjectName,
                            'pic' => $targetPic,
                            'notes' => $outNotes,
                            'user_id' => auth()->id() ?? 1,
                        ]);

                        // Efek fisik netto: bertambah lalu langsung keluar ke proyek
                        $item->increment('stock', $qty);
                        $item->decrement('stock', $qty);

                        if (!empty($expDate)) {
                            $item->update(['expiry_date' => $expDate]);
                        }

                        // 1. Log Material Masuk (IN)
                        StockLog::create([
                            'reference_number' => $refNo,
                            'item_id' => $item->id,
                            'type' => 'in',
                            'quantity' => $qty,
                            'recipient_or_provider' => $supplierName ?: ($receivedByName ?: 'Vendor'),
                            'notes' => "Penerimaan Material dari {$supplierName} [Direct Issue Proyek: {$targetProjectName}]",
                            'user_id' => auth()->id() ?? 1,
                        ]);

                        // 2. Log Material Keluar (OUT)
                        StockLog::create([
                            'reference_number' => $outRefNo,
                            'item_id' => $item->id,
                            'type' => 'out',
                            'quantity' => $qty,
                            'recipient_or_provider' => "Proyek: {$targetProjectName} (PIC: {$targetPic})",
                            'notes' => "Pengeluaran Langsung ke Proyek {$targetProjectName} dari penerimaan {$supplierName}",
                            'user_id' => auth()->id() ?? 1,
                        ]);

                        $fmtBatchQty = format_quantity($qty);
                        ActivityLog::record(
                            'Direct Material Masuk & Keluar Proyek',
                            "Penerimaan {$fmtBatchQty} {$unit} {$item->name} dari {$supplierName} langsung dialokasikan ke Proyek {$targetProjectName} (PIC: {$targetPic})",
                            'Material Masuk'
                        );
                    } elseif ($rowQcStatus === 'approved') {
                        $item->increment('stock', $qty);
                        if (!empty($expDate)) {
                            $item->update(['expiry_date' => $expDate]);
                        }

                        StockLog::create([
                            'reference_number' => $refNo,
                            'item_id' => $item->id,
                            'type' => 'in',
                            'quantity' => $qty,
                            'recipient_or_provider' => $supplierName ?: ($receivedByName ?: 'Vendor'),
                            'notes' => $isConsumable
                                ? "Penerimaan Material Consumable (Bebas QC - Langsung Masuk Stok) dari {$supplierName}"
                                : "Penerimaan Material dari {$supplierName}",
                            'user_id' => auth()->id() ?? 1,
                        ]);

                        $fmtBatchQty = format_quantity($qty);
                        ActivityLog::record(
                            'Penerimaan Material Baru',
                            $isConsumable
                                ? "Penerimaan {$fmtBatchQty} {$unit} {$item->name} [Consumable - Bebas QC langsung masuk ke stok]"
                                : "Mencatat penerimaan {$fmtBatchQty} {$unit} {$item->name} dari {$supplierName}",
                            'Material Masuk'
                        );
                    } else {
                        $fmtBatchQty = format_quantity($qty);
                        ActivityLog::record(
                            'Penerimaan Fisik Gudang',
                            "Mencatat penerimaan fisik {$fmtBatchQty} {$unit} {$item->name} [Menunggu Inspeksi QC]",
                            'Material Masuk'
                        );
                    }

                    $createdCount++;
                }
            });

            $msg = $isDirect
                ? "Berhasil mencatat {$createdCount} material masuk dan langsung dialokasikan ke Proyek {$targetProjectName} (otomatis tercatat di Material Keluar & Rekap Proyek)!"
                : "Berhasil mencatat {$createdCount} material masuk dari {$supplierName}!";
            session()->flash('success', $msg);
            $this->dispatch('swal:success', title: 'Berhasil!', message: $msg);

            $this->closeModal();
            return;
        }

        // 3. FALLBACK: Single item direct submission (kompatibilitas test programmatic)
        if ($this->wholesale_qty !== null) {
            $this->wholesale_qty = str_replace(',', '.', (string) $this->wholesale_qty);
        }
        if ($this->quantity !== null) {
            $this->quantity = str_replace(',', '.', (string) $this->quantity);
        }
        if ($this->input_mode === 'wholesale' && is_numeric($this->wholesale_qty) && $this->conversion_factor > 1) {
            $this->quantity = (float) $this->wholesale_qty * (float) $this->conversion_factor;
        }

        $this->validate();

        if ($this->directToProject) {
            $directRules = [
                'directPic' => 'required|string|max:255',
                'directNotes' => 'nullable|string|max:500',
            ];
            $directMessages = [
                'directPic.required' => 'Nama PIC penerima lapangan wajib diisi.',
            ];
            if (empty($this->directProjectId) && empty($this->directProjectName)) {
                $directRules['directProjectId'] = 'required';
                $directMessages['directProjectId.required'] = 'Silakan pilih proyek tujuan alokasi.';
            }
            $this->validate($directRules, $directMessages);
        }

        $item = Item::with('category')->findOrFail($this->item_id);
        $isConsumable = $item->isConsumable();
        $isDirect = (bool) $this->directToProject;
        $targetProject = null;
        if ($isDirect) {
            if (!empty($this->directProjectId)) {
                $targetProject = Project::find($this->directProjectId);
            }
            if (!$targetProject && !empty($this->directProjectName)) {
                $targetProject = Project::where('name', trim($this->directProjectName))->first();
                if (!$targetProject) {
                    $targetProject = Project::firstOrCreate(
                        ['name' => trim($this->directProjectName)],
                        [
                            'code' => 'PRJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                            'status' => 'Ongoing',
                            'location' => trim($this->directProjectName),
                            'created_by' => auth()->id() ?? 1,
                        ]
                    );
                }
            }
        }
        $targetProjectName = $targetProject ? $targetProject->name : trim($this->directProjectName);
        $targetPic = $isDirect ? trim($this->directPic) : '';
        $directNotesText = $isDirect ? trim($this->directNotes) : '';

        if ($isDirect || $isConsumable) {
            $this->qc_status = 'approved';
        }

        DB::transaction(function () use (
            $item, $isConsumable, $isDirect, $targetProject, $targetProjectName, $targetPic, $directNotesText
        ) {
            $refNo = 'IN-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            $qcStatus = ($isDirect || $isConsumable) ? 'approved' : $this->qc_status;
            $qcNotes = $isDirect
                ? "Direct Allocation ke Proyek: {$targetProjectName} (PIC: {$targetPic})"
                : ($isConsumable ? 'Bebas QC - Material Consumable langsung masuk ke stok' : null);

            $materialIn = MaterialIn::create([
                'reference_number' => $refNo,
                'date' => $this->date,
                'item_id' => $this->item_id,
                'unit_id' => $item->unit_id,
                'quantity' => $this->quantity,
                'unit' => $this->unit ?: $item->unit,
                'wholesale_qty' => $this->input_mode === 'wholesale' ? $this->wholesale_qty : null,
                'wholesale_unit' => $this->input_mode === 'wholesale' ? $this->wholesale_unit : null,
                'supplier' => trim($this->supplier),
                'received_by' => trim($this->received_by),
                'notes' => $this->notes,
                'manufacturing_date' => !empty($this->manufacturing_date) ? $this->manufacturing_date : null,
                'expiry_date' => !empty($this->expiry_date) ? $this->expiry_date : null,
                'qc_status' => $qcStatus,
                'qc_by' => $qcStatus === 'approved' ? (auth()->id() ?? 1) : null,
                'qc_at' => $qcStatus === 'approved' ? now() : null,
                'qc_notes' => $qcNotes,
                'user_id' => auth()->id() ?? 1,
            ]);

            if ($isDirect) {
                $outRefNo = 'OUT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                $outNotes = !empty($directNotesText)
                    ? $directNotesText
                    : "Alokasi langsung dari penerimaan supplier {$this->supplier} (Ref Masuk: {$refNo})";

                MaterialOut::create([
                    'reference_number' => $outRefNo,
                    'date' => $this->date,
                    'item_id' => $item->id,
                    'unit_id' => $item->unit_id,
                    'quantity' => $this->quantity,
                    'unit' => $this->unit ?: $item->unit,
                    'wholesale_qty' => $this->input_mode === 'wholesale' ? $this->wholesale_qty : null,
                    'wholesale_unit' => $this->input_mode === 'wholesale' ? $this->wholesale_unit : null,
                    'project_id' => $targetProject?->id,
                    'project' => $targetProjectName,
                    'pic' => $targetPic,
                    'notes' => $outNotes,
                    'user_id' => auth()->id() ?? 1,
                ]);

                $item->increment('stock', $this->quantity);
                $item->decrement('stock', $this->quantity);

                if (!empty($this->expiry_date)) {
                    $item->update(['expiry_date' => $this->expiry_date]);
                }

                StockLog::create([
                    'reference_number' => $refNo,
                    'item_id' => $item->id,
                    'type' => 'in',
                    'quantity' => $this->quantity,
                    'recipient_or_provider' => trim($this->supplier) ?: (trim($this->received_by) ?: 'Vendor'),
                    'notes' => "Penerimaan Material dari " . trim($this->supplier) . " [Direct Issue Proyek: {$targetProjectName}]",
                    'user_id' => auth()->id() ?? 1,
                ]);

                StockLog::create([
                    'reference_number' => $outRefNo,
                    'item_id' => $item->id,
                    'type' => 'out',
                    'quantity' => $this->quantity,
                    'recipient_or_provider' => "Proyek: {$targetProjectName} (PIC: {$targetPic})",
                    'notes' => "Pengeluaran Langsung ke Proyek {$targetProjectName} dari penerimaan " . trim($this->supplier),
                    'user_id' => auth()->id() ?? 1,
                ]);

                $fmtSingleQty = format_quantity($this->quantity);
                ActivityLog::record(
                    'Direct Material Masuk & Keluar Proyek',
                    "Penerimaan {$fmtSingleQty} {$item->unit} {$item->name} dari {$this->supplier} langsung dialokasikan ke Proyek {$targetProjectName} (PIC: {$targetPic})",
                    'Material Masuk'
                );

                $msg = "Material masuk berhasil dicatat dan langsung dialokasikan ke Proyek {$targetProjectName} (tercatat di Material Keluar & Rekap Proyek)!";
                session()->flash('success', $msg);
                $this->dispatch('swal:success', title: 'Berhasil!', message: $msg);
            } elseif ($qcStatus === 'approved') {
                $item->increment('stock', $this->quantity);
                if (!empty($this->expiry_date)) {
                    $item->update(['expiry_date' => $this->expiry_date]);
                }

                StockLog::create([
                    'reference_number' => $refNo,
                    'item_id' => $item->id,
                    'type' => 'in',
                    'quantity' => $this->quantity,
                    'recipient_or_provider' => trim($this->supplier) ?: (trim($this->received_by) ?: 'Vendor'),
                    'notes' => $isConsumable
                        ? "Penerimaan Material Consumable (Bebas QC - Langsung Masuk Stok) dari " . trim($this->supplier)
                        : "Penerimaan Material dari " . trim($this->supplier),
                    'user_id' => auth()->id() ?? 1,
                ]);

                $fmtSingleQty = format_quantity($this->quantity);
                ActivityLog::record(
                    'Penerimaan Material Baru',
                    $isConsumable
                        ? "Penerimaan {$fmtSingleQty} {$item->unit} {$item->name} [Consumable - Bebas QC langsung masuk ke stok]"
                        : "Mencatat penerimaan {$fmtSingleQty} {$item->unit} {$item->name} dari {$this->supplier}",
                    'Material Masuk'
                );

                $msg = $isConsumable
                    ? "Material consumable berhasil dicatat & langsung masuk ke stok barang tanpa QC! Stok {$item->name} bertambah +{$fmtSingleQty} {$item->unit}."
                    : "Material masuk berhasil dicatat & disetujui QC! Stok {$item->name} bertambah +{$fmtSingleQty} {$item->unit}.";

                session()->flash('success', $msg);
                $this->dispatch('swal:success', title: 'Berhasil!', message: $msg);
            } else {
                $fmtSingleQty = format_quantity($this->quantity);
                ActivityLog::record(
                    'Penerimaan Fisik Gudang',
                    "Mencatat penerimaan fisik {$fmtSingleQty} {$item->unit} {$item->name} [Menunggu Inspeksi QC]",
                    'Material Masuk'
                );

                session()->flash('success', "Tanda terima fisik berhasil disimpan! Status: [Menunggu QC]. Stok master belum bertambah sampai QC menyetujui.");
                $this->dispatch('swal:success', title: 'Berhasil!', message: "Tanda terima fisik berhasil disimpan! Status: [Menunggu QC]. Stok master belum bertambah sampai QC menyetujui.");
            }
        });

        $this->closeModal();
    }

    public function openQcModal($id)
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC tidak memiliki izin inspeksi QC.');
            $this->dispatch('swal:error', title: 'Gagal!', message: 'Akses Ditolak: Akun PPIC tidak memiliki izin inspeksi QC.');
            return;
        }

        $record = MaterialIn::with('item.category')->findOrFail($id);
        if ($record->item && $record->item->isConsumable()) {
            session()->flash('info', 'Barang kategori Consumable bebas QC dan telah langsung masuk ke stok barang.');
            $this->dispatch('swal:info', title: 'Bebas QC', message: 'Barang kategori Consumable bebas QC dan telah langsung masuk ke stok barang.');
            return;
        }

        $this->qcSelectedId = $record->id;
        $this->qcSelectedRecord = $record;
        $this->qcDecision = $record->qc_status === 'rejected' ? 'rejected' : 'approved';
        $this->qcNotes = $record->qc_notes ?? '';
        $this->isQcModalOpen = true;
    }

    public function closeQcModal()
    {
        $this->isQcModalOpen = false;
        $this->qcSelectedId = null;
        $this->qcSelectedRecord = null;
        $this->qcNotes = '';
    }

    public function submitQc()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC tidak memiliki izin inspeksi QC.');
            $this->dispatch('swal:error', title: 'Gagal!', message: 'Akses Ditolak: Akun PPIC tidak memiliki izin inspeksi QC.');
            return;
        }

        $record = MaterialIn::findOrFail($this->qcSelectedId);
        $item = Item::findOrFail($record->item_id);

        DB::transaction(function () use ($record, $item) {
            $oldStatus = $record->qc_status;
            $newStatus = $this->qcDecision;

            $record->update([
                'qc_status' => $newStatus,
                'qc_by' => auth()->id() ?? 1,
                'qc_at' => now(),
                'qc_notes' => trim($this->qcNotes),
            ]);

            if ($newStatus === 'approved' && $oldStatus !== 'approved') {
                $item->increment('stock', $record->quantity);
                if (!empty($record->expiry_date)) {
                    $item->update(['expiry_date' => $record->expiry_date]);
                }

                StockLog::create([
                    'reference_number' => $record->reference_number,
                    'item_id' => $item->id,
                    'type' => 'in',
                    'quantity' => $record->quantity,
                    'recipient_or_provider' => trim($record->supplier) ?: 'Vendor',
                    'notes' => "Penerimaan Material disetujui QC (OK) dari {$record->supplier}",
                    'user_id' => auth()->id() ?? 1,
                ]);

                    $roleLabel = (auth()->user() && auth()->user()->isWebAdmin()) ? 'Web Administrator' : 'Quality Control (QC)';
                    $fmtQty = format_quantity($record->quantity);

                    ActivityLog::record(
                        'QC Approval OK',
                        "{$roleLabel} menyetujui penerimaan {$fmtQty} {$item->unit} {$item->name}. Stok aktif bertambah.",
                        'Material Masuk'
                    );

                    session()->flash('success', "Inspeksi QC Berhasil: Status disetujui [OK]! Stok {$item->name} bertambah +{$fmtQty} {$item->unit}.");
                    $this->dispatch('swal:success', title: 'Berhasil!', message: "Inspeksi QC Berhasil: Status disetujui [OK]! Stok {$item->name} bertambah +{$fmtQty} {$item->unit}.");
                } elseif ($newStatus === 'rejected') {
                    if ($oldStatus === 'approved') {
                        $item->decrement('stock', $record->quantity);
                    }

                    // Buat atau sinkronkan data di tabel material_rejects
                    $rejRefNo = 'REJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
                    MaterialReject::firstOrCreate(
                        ['material_in_id' => $record->id],
                        [
                            'reference_number' => $rejRefNo,
                            'item_id' => $record->item_id,
                            'date' => $record->date ?? now()->toDateString(),
                            'supplier' => $record->supplier,
                            'po_number' => $record->po_number,
                            'quantity' => $record->quantity,
                            'unit' => $record->unit,
                            'damage_type' => 'Ditolak Inspeksi QC (Reject)',
                            'disposition' => 'karantina',
                            'disposition_status' => 'pending',
                            'description' => trim($this->qcNotes) ?: 'Material ditolak pada saat inspeksi mutu Quality Control.',
                            'reported_by' => auth()->id() ?? 1,
                            'inspected_by' => auth()->id() ?? 1,
                        ]
                    );

                    $roleLabel = (auth()->user() && auth()->user()->isWebAdmin()) ? 'Web Administrator' : 'Quality Control (QC)';

                    ActivityLog::record(
                        'QC Reject',
                        "{$roleLabel} menolak (Reject) penerimaan ({$item->name}). Catatan: {$this->qcNotes}. Laporan reject otomatis dicatat.",
                        'Material Reject'
                    );

                    session()->flash('warning', "Inspeksi QC: Status ditolak [Reject]. Laporan material reject otomatis dicatat di modul Material Rusak/Reject.");
                    $this->dispatch('swal:warning', title: 'Material Ditolak (Reject)', message: "Inspeksi QC: Status ditolak [Reject]. Laporan material reject otomatis dicatat.");
                }
            });

            $this->closeQcModal();
        }

        public function openPrintReceiptModal($id)
        {
            $this->receiptRecord = MaterialIn::with(['item.category', 'user', 'qcUser'])->findOrFail($id);
            $this->isPrintReceiptModalOpen = true;
        }

        public function closePrintReceiptModal()
        {
            $this->isPrintReceiptModalOpen = false;
            $this->receiptRecord = null;
        }

        public function delete($id)
        {
            if (auth()->user() && auth()->user()->isPpic()) {
                session()->flash('error', 'Akses Ditolak: Akun PPIC tidak memiliki izin hapus.');
                $this->dispatch('swal:error', title: 'Gagal!', message: 'Akses Ditolak: Akun PPIC tidak memiliki izin hapus.');
                return;
            }

            $record = MaterialIn::findOrFail($id);
            $item = Item::find($record->item_id);

            if ($record->qc_status === 'approved' && $item && $item->stock < $record->quantity) {
                session()->flash('error', "Gagal menghapus! Stok barang {$item->name} saat ini ({$item->stock}) lebih kecil dari kuantitas yang akan ditarik ({$record->quantity}).");
                $this->dispatch('swal:error', title: 'Gagal!', message: "Gagal menghapus! Stok barang {$item->name} saat ini ({$item->stock}) lebih kecil dari kuantitas yang akan ditarik ({$record->quantity}).");
                return;
            }

            DB::transaction(function () use ($record, $item) {
                $qty = $record->quantity;

                if ($record->qc_status === 'approved' && $item) {
                    $item->decrement('stock', $qty);
                }

                $record->delete();

                ActivityLog::record(
                    'Hapus Penerimaan Material',
                    "Menghapus data penerimaan dan menyesuaikan stok {$item->name}",
                    'Material Masuk'
                );
            });

            self::clearPerformanceCaches();

            session()->flash('success', "Data penerimaan berhasil dihapus. Stok barang telah disesuaikan.");
            $this->dispatch('swal:success', title: 'Deleted!', message: "Data penerimaan berhasil dihapus. Stok barang telah disesuaikan.");
        }

        public function export()
        {
            $records = $this->buildQuery()->get();

            $filename = 'Laporan_Material_Masuk_' . date('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            return response()->stream(function () use ($records) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['No', 'Tanggal', 'Kode Barang', 'Nama Barang', 'Jumlah Masuk', 'Satuan', 'Status QC', 'Tgl Pembuatan (Cat)', 'Supplier / Vendor', 'Diterima Oleh', 'Catatan Penerimaan', 'Catatan QC / Inspeksi']);

                $no = 1;
                foreach ($records as $row) {
                    $isConsumable = $row->item && $row->item->isConsumable();
                    $qcLabel = $isConsumable
                        ? 'Bebas QC (Consumable)'
                        : ($row->qc_status === 'approved' ? 'OK (Approved)' : ($row->qc_status === 'rejected' ? 'REJECT' : 'PENDING QC'));

                    fputcsv($handle, [
                        $no++,
                        format_date($row->date),
                        $row->item->code_item ?? '-',
                        $row->item->name ?? '-',
                        $row->quantity,
                        $row->unit,
                        $qcLabel,
                        $row->manufacturing_date ? format_date($row->manufacturing_date) : '-',
                        $row->supplier,
                        $row->received_by,
                        $row->notes ?? '-',
                        $isConsumable ? 'Bebas QC (Consumable langsung masuk stok)' : ($row->qc_notes ?: '-'),
                    ]);
                }
                fclose($handle);
            }, 200, $headers);
        }

    public function setTab($tab)
    {
        $this->activeTab = in_array($tab, ['inbound', 'rejects']) ? $tab : 'inbound';
        $this->resetPage();
    }

    public function updatedRejectItemId($val)
    {
        if ($val) {
            $item = Item::find($val);
            if ($item) {
                $this->rejectUnit = $item->unit;
            }
        }
    }

    protected function rejectRules()
    {
        return [
            'rejectItemId' => 'required|exists:items,id',
            'rejectDate' => 'required|date',
            'rejectSupplierName' => 'required|string|max:255',
            'rejectPoNumber' => 'nullable|string|max:100',
            'rejectQuantity' => 'required|numeric|min:0.001',
            'rejectUnit' => 'required|string|max:50',
            'rejectDamageTypeChoice' => 'required|string|max:100',
            'rejectDispositionChoice' => 'required|string|in:karantina,retur_supplier,replacement,scrap',
            'rejectDescription' => 'nullable|string|max:1000',
        ];
    }

    public function openCreateRejectModal($materialInId = null)
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC hanya memiliki hak akses melihat.');
            $this->dispatch('swal:error', title: 'Akses Ditolak!', message: 'Akun PPIC hanya memiliki hak akses melihat.');
            return;
        }

        $this->resetRejectForm();
        $this->rejectDate = date('Y-m-d');
        $this->rejectDamageTypeChoice = 'Cacat Fisik / Pecah';
        $this->rejectDispositionChoice = 'karantina';

        if ($materialInId) {
            $matIn = MaterialIn::with('item')->find($materialInId);
            if ($matIn) {
                $this->rejectMaterialInId = $matIn->id;
                $this->rejectItemId = $matIn->item_id;
                $this->rejectSupplierName = $matIn->supplier;
                $this->rejectPoNumber = $matIn->po_number ?? '';
                $this->rejectQuantity = $matIn->quantity;
                $this->rejectUnit = $matIn->unit;
            }
        }

        $this->isRejectEditMode = false;
        $this->isRejectModalOpen = true;
    }

    public function openEditRejectModal($id)
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC tidak dapat mengedit data.');
            $this->dispatch('swal:error', title: 'Akses Ditolak!', message: 'Akun PPIC tidak dapat mengedit data.');
            return;
        }

        $record = MaterialReject::findOrFail($id);
        $this->selectedRejectId = $record->id;
        $this->rejectMaterialInId = $record->material_in_id;
        $this->rejectItemId = $record->item_id;
        $this->rejectDate = $record->date->format('Y-m-d');
        $this->rejectSupplierName = $record->supplier;
        $this->rejectPoNumber = $record->po_number ?? '';
        $this->rejectQuantity = $record->quantity;
        $this->rejectUnit = $record->unit;
        $this->rejectDamageTypeChoice = $record->damage_type;
        $this->rejectDispositionChoice = $record->disposition;
        $this->rejectDescription = $record->description ?? '';

        $this->isRejectEditMode = true;
        $this->isRejectModalOpen = true;
    }

    public function closeRejectModal()
    {
        $this->isRejectModalOpen = false;
        $this->resetRejectForm();
    }

    private function resetRejectForm()
    {
        $this->selectedRejectId = null;
        $this->rejectMaterialInId = null;
        $this->rejectItemId = '';
        $this->rejectDate = date('Y-m-d');
        $this->rejectSupplierName = '';
        $this->rejectPoNumber = '';
        $this->rejectQuantity = 1;
        $this->rejectUnit = 'PCS';
        $this->rejectDamageTypeChoice = 'Cacat Fisik / Pecah';
        $this->rejectDispositionChoice = 'karantina';
        $this->rejectDescription = '';
        $this->resetErrorBag();
    }

    public function saveReject()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC tidak dapat menyimpan data.');
            return;
        }

        if ($this->rejectQuantity !== null) {
            $this->rejectQuantity = str_replace(',', '.', (string) $this->rejectQuantity);
        }

        $this->validate($this->rejectRules());

        $item = Item::findOrFail($this->rejectItemId);

        DB::transaction(function () use ($item) {
            if (!$this->isRejectEditMode) {
                $refNo = 'REJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                $reject = MaterialReject::create([
                    'reference_number' => $refNo,
                    'material_in_id' => $this->rejectMaterialInId,
                    'item_id' => $this->rejectItemId,
                    'date' => $this->rejectDate,
                    'supplier' => trim($this->rejectSupplierName),
                    'po_number' => trim($this->rejectPoNumber) ?: null,
                    'quantity' => $this->rejectQuantity,
                    'unit' => $this->rejectUnit ?: $item->unit,
                    'damage_type' => $this->rejectDamageTypeChoice,
                    'disposition' => $this->rejectDispositionChoice,
                    'disposition_status' => 'pending',
                    'description' => trim($this->rejectDescription),
                    'reported_by' => auth()->id() ?? 1,
                    'inspected_by' => (auth()->user() && (auth()->user()->isQc() || auth()->user()->isAdmin())) ? auth()->id() : null,
                ]);

                ActivityLog::record(
                    'Catat Material Rusak / Reject',
                    "Mencatat {$this->rejectQuantity} {$reject->unit} {$item->name} rusak/reject dari supplier {$reject->supplier}. Alasan: {$reject->damage_type}. Disposisi: {$reject->disposition_label} (No: {$refNo})",
                    'Material Reject'
                );

                $msg = "Laporan material rusak/reject ({$refNo}) berhasil dicatat!";
                session()->flash('success', $msg);
                $this->dispatch('swal:success', title: 'Berhasil Dicatat!', message: $msg);
            } else {
                $reject = MaterialReject::findOrFail($this->selectedRejectId);
                $updateData = [
                    'item_id' => $this->rejectItemId,
                    'date' => $this->rejectDate,
                    'supplier' => trim($this->rejectSupplierName),
                    'po_number' => trim($this->rejectPoNumber) ?: null,
                    'quantity' => $this->rejectQuantity,
                    'unit' => $this->rejectUnit ?: $item->unit,
                    'damage_type' => $this->rejectDamageTypeChoice,
                    'disposition' => $this->rejectDispositionChoice,
                    'description' => trim($this->rejectDescription),
                ];

                if (empty($reject->inspected_by) && auth()->user() && (auth()->user()->isQc() || auth()->user()->isAdmin())) {
                    $updateData['inspected_by'] = auth()->id();
                }

                $reject->update($updateData);

                ActivityLog::record(
                    'Update Laporan Material Reject',
                    "Memperbarui data laporan material reject {$reject->reference_number} ({$item->name})",
                    'Material Reject'
                );

                session()->flash('success', "Laporan material reject {$reject->reference_number} berhasil diperbarui.");
                $this->dispatch('swal:success', title: 'Berhasil Diperbarui!', message: "Laporan material reject {$reject->reference_number} berhasil diperbarui.");
            }
        });

        $this->closeRejectModal();
    }

    public function openDispositionModal($id)
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC tidak memiliki izin memperbarui disposisi.');
            $this->dispatch('swal:error', title: 'Akses Ditolak!', message: 'Akun PPIC tidak memiliki izin memperbarui disposisi.');
            return;
        }

        $record = MaterialReject::with('item')->findOrFail($id);
        $this->dispositionSelectedId = $record->id;
        $this->dispositionRecord = $record;
        $this->dispositionAction = $record->disposition;
        $this->dispositionStatus = $record->disposition_status;
        $this->resolutionNotes = $record->resolution_notes ?? '';
        $this->isDispositionModalOpen = true;
    }

    public function closeDispositionModal()
    {
        $this->isDispositionModalOpen = false;
        $this->dispositionSelectedId = null;
        $this->dispositionRecord = null;
        $this->resolutionNotes = '';
    }

    public function updateDisposition()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Akses Ditolak: Akun PPIC tidak memiliki izin.');
            return;
        }

        $record = MaterialReject::findOrFail($this->dispositionSelectedId);
        $newStatus = $this->dispositionStatus;

        $record->update([
            'disposition' => $this->dispositionAction,
            'disposition_status' => $newStatus,
            'resolution_notes' => trim($this->resolutionNotes),
            'resolved_at' => $newStatus === 'completed' ? now() : null,
            'resolved_by' => $newStatus === 'completed' ? (auth()->id() ?? 1) : null,
        ]);

        ActivityLog::record(
            'Update Disposisi Material Reject',
            "Memperbarui disposisi {$record->reference_number} menjadi {$record->disposition_label} (Status: {$record->status_label})",
            'Material Reject'
        );

        $msg = "Disposisi laporan {$record->reference_number} berhasil diperbarui menjadi [{$record->status_label}].";
        session()->flash('success', $msg);
        $this->dispatch('swal:success', title: 'Disposisi Diperbarui!', message: $msg);

        $this->closeDispositionModal();
    }

    public function deleteReject($id)
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            session()->flash('error', 'Akses Ditolak: Hanya Web Administrator yang memiliki wewenang menghapus laporan reject.');
            $this->dispatch('swal:error', title: 'Akses Ditolak!', message: 'Hanya Web Administrator yang memiliki wewenang menghapus laporan reject.');
            return;
        }

        $record = MaterialReject::with('item')->findOrFail($id);
        $refNo = $record->reference_number;
        $itemName = $record->item->name ?? 'Barang';

        $record->delete();

        ActivityLog::record(
            'Hapus Laporan Material Reject',
            "Menghapus laporan material reject {$refNo} ({$itemName})",
            'Material Reject'
        );

        session()->flash('success', "Laporan material reject {$refNo} berhasil dihapus.");
        $this->dispatch('swal:success', title: 'Deleted!', message: "Laporan material reject {$refNo} berhasil dihapus.");
    }

    public function exportRejects()
    {
        $records = $this->buildRejectQuery()->get();

        $filename = 'Laporan_Material_Rusak_Reject_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        ActivityLog::record(
            'Ekspor Laporan Material Reject',
            'Mengunduh data rekap material rusak / reject ke format CSV',
            'Material Reject'
        );

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'Tanggal Laporan', 'No. Referensi BAP', 'Kode Barang', 'Nama Barang', 'Supplier / Vendor', 'No. PO', 'Qty Rusak/Reject', 'Satuan', 'Jenis Kerusakan / Cacat', 'Disposisi Tindakan', 'Status Penanganan', 'Pelapor', 'Inspektor QC', 'Catatan Kerusakan', 'Catatan Penyelesaian']);

            $no = 1;
            foreach ($records as $row) {
                fputcsv($handle, [
                    $no++,
                    format_date($row->date),
                    $row->reference_number,
                    $row->item->code_item ?? '-',
                    $row->item->name ?? '-',
                    $row->supplier,
                    $row->po_number ?? '-',
                    $row->quantity,
                    $row->unit,
                    $row->damage_type,
                    $row->disposition_label,
                    $row->status_label,
                    $row->reportedBy->name ?? 'Gudang',
                    $row->inspectedBy->name ?? 'QC',
                    $row->description ?? '-',
                    $row->resolution_notes ?? '-',
                ]);
            }
            fclose($handle);
        }, 200, $headers);
    }

    private function buildRejectBaseQuery()
    {
        $query = MaterialReject::with(['item.category', 'reportedBy', 'inspectedBy', 'resolvedBy'])->latest('date')->latest('id');

        if (!empty($this->rejectSearch)) {
            $s = trim($this->rejectSearch);
            $query->where(function ($q) use ($s) {
                $q->where('reference_number', 'like', "%{$s}%")
                    ->orWhere('supplier', 'like', "%{$s}%")
                    ->orWhere('po_number', 'like', "%{$s}%")
                    ->orWhere('damage_type', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhereHas('item', function ($iq) use ($s) {
                        $iq->where('name', 'like', "%{$s}%")
                            ->orWhere('code_item', 'like', "%{$s}%");
                    });
            });
        }

        if (!empty($this->rejectDateFrom)) {
            $query->whereDate('date', '>=', $this->rejectDateFrom);
        }

        if (!empty($this->rejectDateTo)) {
            $query->whereDate('date', '<=', $this->rejectDateTo);
        }

        if (!empty($this->rejectSupplier)) {
            $query->where('supplier', $this->rejectSupplier);
        }

        if (!empty($this->rejectDamageType)) {
            $query->where('damage_type', $this->rejectDamageType);
        }

        if (!empty($this->rejectDisposition)) {
            $query->where('disposition', $this->rejectDisposition);
        }

        if (!empty($this->rejectStatus)) {
            $query->where('disposition_status', $this->rejectStatus);
        }

        return $query;
    }

    private function buildRejectQuery()
    {
        return $this->buildRejectBaseQuery();
    }

    private function buildBaseQuery()
    {
        $query = MaterialIn::with(['item.category', 'qcUser'])->latest('date')->latest('id');

        if (!empty($this->search)) {
            $s = trim($this->search);
            $query->where(function ($q) use ($s) {
                $q->where('supplier', 'like', "%{$s}%")
                    ->orWhere('received_by', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhere('qc_notes', 'like', "%{$s}%")
                    ->orWhereHas('item', function ($iq) use ($s) {
                        $iq->where('name', 'like', "%{$s}%")
                            ->orWhere('code_item', 'like', "%{$s}%");
                    });
            });
        }

        if (!empty($this->dateFrom)) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        if (!empty($this->selectedSupplier)) {
            $query->where('supplier', $this->selectedSupplier);
        }

        if (!empty($this->selectedItemId)) {
            $query->where('item_id', $this->selectedItemId);
        }

        if (!empty($this->selectedItemIds)) {
            $query->whereIn('item_id', $this->selectedItemIds);
        }

        if (!empty($this->selectedUnit)) {
            $u = $this->selectedUnit;
            $query->where(function ($uq) use ($u) {
                $uq->where('unit_id', $u)
                    ->orWhere('unit', $u)
                    ->orWhereHas('item', function ($iq) use ($u) {
                        $iq->where('unit_id', $u)->orWhere('unit', $u);
                    });
            });
        }

        return $query;
    }

    private function buildQuery()
    {
        $query = $this->buildBaseQuery();

        if (!empty($this->selectedQcStatus)) {
            $query->where('qc_status', $this->selectedQcStatus);
        }

        return $query;
    }

    public static function clearPerformanceCaches(): void
    {
        Cache::forget('wms_inbound_items_list');
        Cache::forget('wms_inbound_suppliers');
        Cache::forget('wms_inbound_projects_payload');
        Cache::forget('wms_pic_suggestions');
        Cache::forget('wms_raw_material_parents');
        Cache::forget('wms_active_units');
    }

    public function render()
    {
        $perPage = CompanyProfile::getDefaultPerPage();
        $units = Cache::remember('wms_active_units', 300, fn() => \App\Models\Unit::where('is_active', true)->orderBy('name')->get());

        $cachedItems = Cache::remember('wms_inbound_items_list', 180, function () {
            $items = Item::select(['id', 'name', 'code_item', 'category_id', 'brand', 'stock', 'unit'])
                ->with('category:id,name')
                ->orderBy('name')
                ->get();

            $itemsJsonList = $items->map(fn($it) => [
                'id' => (string) $it->id,
                'name' => $it->name,
                'code' => $it->code_item ?? '',
                'brand' => $it->brand ?? '',
                'category' => $it->category?->name ?? 'Consumable',
                'stock' => (float) $it->stock,
                'unit' => $it->unit ?? 'PCS',
            ])->values()->toArray();

            return ['items' => $items, 'itemsJsonList' => $itemsJsonList];
        });

        $items = $cachedItems['items'];
        $itemsJsonList = $cachedItems['itemsJsonList'];

        // Total Pending QC Global (selalu dihitung karena digunakan di badge tab header)
        $globalPendingQcCount = MaterialIn::where('qc_status', 'pending_qc')
            ->whereDoesntHave('item.category', function ($q) {
                $q->where('name', 'like', '%consumable%');
            })
            ->count();

        if ($this->activeTab === 'inbound') {
            $records = $this->buildQuery()->paginate($perPage, ['*'], 'inboundPage');
            $suppliers = Cache::remember('wms_inbound_suppliers', 300, function () {
                return MaterialIn::select('supplier')->distinct()->whereNotNull('supplier')->where('supplier', '!=', '')->orderBy('supplier')->pluck('supplier');
            });

            // Mini Dashboard Metrics untuk Inbound
            $baseQuery = $this->buildBaseQuery();
            $totalTransactionsPeriod = (clone $baseQuery)->count();
            $totalApprovedPeriod = (clone $baseQuery)->where('qc_status', 'approved')->count();
            $totalPendingQcPeriod = (clone $baseQuery)->where('qc_status', 'pending_qc')
                ->whereDoesntHave('item.category', function ($q) {
                    $q->where('name', 'like', '%consumable%');
                })
                ->count();
            $totalRejectedPeriod = (clone $baseQuery)->where('qc_status', 'rejected')->count();
            $totalQtyApproved = (clone $baseQuery)->where('qc_status', 'approved')->sum('quantity');

            // Rejects placeholder saat tab tidak aktif (menghemat 6 query DB)
            $rejectRecords = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, 1, ['pageName' => 'rejectPage']);
            $totalRejectCases = 0;
            $totalRejectQty = 0;
            $totalPendingDisposition = 0;
            $totalCompletedDisposition = 0;
            $rejectSuppliers = collect();
        } else {
            // Rejects Data & Metrics saat tab Rejects aktif
            $rejectBaseQuery = $this->buildRejectBaseQuery();
            $rejectRecords = $this->buildRejectQuery()->paginate($perPage, ['*'], 'rejectPage');
            $totalRejectCases = (clone $rejectBaseQuery)->count();
            $totalRejectQty = (clone $rejectBaseQuery)->sum('quantity');
            $totalPendingDisposition = (clone $rejectBaseQuery)->where('disposition_status', 'pending')->count();
            $totalCompletedDisposition = (clone $rejectBaseQuery)->where('disposition_status', 'completed')->count();
            $rejectSuppliers = MaterialReject::select('supplier')->distinct()->whereNotNull('supplier')->where('supplier', '!=', '')->orderBy('supplier')->pluck('supplier');

            // Inbound placeholder saat tab tidak aktif (menghemat 6 query DB)
            $records = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, 1, ['pageName' => 'inboundPage']);
            $suppliers = collect();
            $totalTransactionsPeriod = 0;
            $totalApprovedPeriod = 0;
            $totalPendingQcPeriod = 0;
            $totalRejectedPeriod = 0;
            $totalQtyApproved = 0;
        }

        $cachedProjects = Cache::remember('wms_inbound_projects_payload', 300, function () {
            $allRegisteredProjects = Project::whereNull('deleted_at')->orderBy('name')->get();
            $registeredProjectNames = $allRegisteredProjects->pluck('name')->toArray();
            $historicalProjects = MaterialOut::select('project')
                ->distinct()
                ->whereNotNull('project')
                ->where('project', '!=', '')
                ->orderBy('project', 'asc')
                ->pluck('project')
                ->toArray();
            $otherProjectsList = array_values(array_diff($historicalProjects, $registeredProjectNames));

            $projectsJsonList = $allRegisteredProjects->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'code' => $p->code ?? '-',
                'location' => $p->location ?? '',
                'status' => $p->status ?? 'Ongoing',
                'client' => $p->client ?? '',
            ])->values()->toArray();

            foreach ($otherProjectsList as $otherName) {
                if (!empty($otherName)) {
                    $projectsJsonList[] = [
                        'id' => null,
                        'name' => $otherName,
                        'code' => '-',
                        'status' => 'Historical',
                        'client' => '',
                    ];
                }
            }

            return [
                'allRegisteredProjects' => $allRegisteredProjects,
                'projectsJsonList' => $projectsJsonList,
            ];
        });

        $allRegisteredProjects = $cachedProjects['allRegisteredProjects'];
        $projectsJsonList = $cachedProjects['projectsJsonList'];

        $picSuggestionsList = Cache::remember('wms_pic_suggestions', 300, function () {
            $picsFromOut = MaterialOut::select('pic')->distinct()->whereNotNull('pic')->where('pic', '!=', '')->pluck('pic')->toArray();
            $userNames = User::pluck('name')->toArray();
            $list = array_values(array_unique(array_filter(array_merge($picsFromOut, $userNames))));
            sort($list, SORT_NATURAL | SORT_FLAG_CASE);
            return $list;
        });

        $rawMaterialParents = Cache::remember('wms_raw_material_parents', 300, function () {
            return Item::inStorage(Item::STORAGE_2)
                ->where('is_offcut', false)
                ->whereDoesntHave('category', fn($q) => $q->where('name', 'like', '%off%'))
                ->orderBy('name')
                ->get(['id', 'name', 'code_item', 'stock', 'unit', 'location']);
        });

        $openPurchaseOrders = PurchaseOrder::whereIn('status', ['open', 'partial'])
            ->with(['items.item', 'project'])
            ->latest('po_date')
            ->get();

        return view('livewire.material-in-component', [
            'records' => $records,
            'units' => $units,
            'items' => $items,
            'itemsJsonList' => $itemsJsonList,
            'suppliers' => $suppliers,
            'projects' => $allRegisteredProjects,
            'projectsJsonList' => $projectsJsonList,
            'picSuggestions' => $picSuggestionsList,
            'picSuggestionsList' => $picSuggestionsList,
            'totalTransactionsPeriod' => $totalTransactionsPeriod,
            'rawMaterialParents' => $rawMaterialParents,
            'totalApprovedPeriod' => $totalApprovedPeriod,
            'totalPendingQcPeriod' => $totalPendingQcPeriod,
            'totalRejectedPeriod' => $totalRejectedPeriod,
            'totalQtyApproved' => $totalQtyApproved,
            'globalPendingQcCount' => $globalPendingQcCount,
            // Rejects Data & Metrics
            'rejectRecords' => $rejectRecords,
            'totalRejectCases' => $totalRejectCases,
            'totalRejectQty' => $totalRejectQty,
            'totalPendingDisposition' => $totalPendingDisposition,
            'totalCompletedDisposition' => $totalCompletedDisposition,
            'rejectSuppliers' => $rejectSuppliers,
            'openPurchaseOrders' => $openPurchaseOrders,
        ]);
    }
}
