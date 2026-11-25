<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrderComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Filters & Search
    public string $search = '';
    public string $statusFilter = 'all';
    public string $projectFilter = '';
    public string $dateFrom = '';
    public string $dateTo = '';
    public int $perPage = 10;

    // Modal States
    public bool $isCreateModalOpen = false;
    public bool $isDetailModalOpen = false;
    public bool $isEditModalOpen = false;
    public bool $isCancelModalOpen = false;

    // Active Selection
    public ?int $selectedPoId = null;
    public ?PurchaseOrder $detailPo = null;
    public string $cancelReason = '';

    // Form fields (Create & Edit)
    public string $po_number = '';
    public string $po_date = '';
    public ?string $eta_date = null;
    public string $supplier = '';
    public ?int $project_id = null;
    public string $notes = '';
    public array $items = [];

    // Validation rules
    protected function rules(): array
    {
        $uniquePo = 'required|string|max:100|unique:purchase_orders,po_number';
        if ($this->selectedPoId) {
            $uniquePo .= ',' . $this->selectedPoId;
        }

        return [
            'po_number' => $uniquePo,
            'po_date' => 'required|date',
            'eta_date' => 'nullable|date',
            'supplier' => 'required|string|max:255',
            'project_id' => 'nullable|integer|exists:projects,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:items,id',
            'items.*.quantity_ordered' => 'required|numeric|min:0.01',
            'items.*.unit' => 'required|string|max:50',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:255',
        ];
    }

    protected $messages = [
        'po_number.required' => 'Nomor PO wajib diisi.',
        'po_number.unique' => 'Nomor PO sudah pernah digunakan.',
        'po_date.required' => 'Tanggal PO wajib diisi.',
        'supplier.required' => 'Nama Supplier / Vendor wajib diisi.',
        'items.required' => 'Minimal harus menambahkan 1 item barang pesanan.',
        'items.min' => 'Minimal harus menambahkan 1 item barang pesanan.',
        'items.*.item_id.required' => 'Pilih item barang yang dipesan.',
        'items.*.quantity_ordered.required' => 'Quantity pemesanan wajib diisi.',
        'items.*.quantity_ordered.min' => 'Quantity pemesanan minimal 0.01.',
        'items.*.unit.required' => 'Satuan barang wajib diisi.',
    ];

    public function mount()
    {
        $user = auth()->user();
        if (!$user || (!$user->isProcurement() && !$user->isWebAdmin())) {
            abort(403, 'Akses ditolak: Menu Purchase Order (PO) hanya dapat diakses oleh akun Procurement.');
        }

        $this->po_date = date('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function updatingProjectFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->statusFilter = 'all';
        $this->projectFilter = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    /**
     * Blank item row structure.
     */
    private function getBlankItemRow(): array
    {
        return [
            'id' => null,
            'item_id' => '',
            'item_code' => '',
            'item_name' => '',
            'quantity_ordered' => 1,
            'quantity_received' => 0,
            'unit' => 'PCS',
            'unit_price' => 0,
            'notes' => '',
        ];
    }

    /**
     * Open Create PO Modal.
     */
    public function openCreateModal()
    {
        if (!auth()->user() || !auth()->user()->canManageProcurement()) {
            session()->flash('error', 'Akses ditolak: Hanya akun Procurement atau Web Administrator yang dapat membuat Purchase Order.');
            return;
        }

        $this->resetValidation();
        $this->selectedPoId = null;
        $this->po_number = PurchaseOrder::generateUniquePoNumber();
        $this->po_date = date('Y-m-d');
        $this->eta_date = null;
        $this->supplier = '';
        $this->project_id = null;
        $this->notes = '';
        $this->items = [
            $this->getBlankItemRow()
        ];
        $this->isCreateModalOpen = true;
    }

    public function closeCreateModal()
    {
        $this->isCreateModalOpen = false;
        $this->resetValidation();
    }

    /**
     * Add a new item row to the form (supports adding multiple rows at once).
     */
    public function addItemRow(int $count = 1)
    {
        $count = max(1, min(20, $count));
        for ($i = 0; $i < $count; $i++) {
            $this->items[] = $this->getBlankItemRow();
        }
    }

    /**
     * Quick shortcut to add multiple item rows at once.
     */
    public function addItemRows(int $count = 5)
    {
        $this->addItemRow($count);
    }

    /**
     * Remove an item row from the form.
     */
    public function removeItemRow(int $index)
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    /**
     * Select item for specific row and auto-fill related details.
     */
    public function selectItemForRow(int $index, $itemId)
    {
        if (isset($this->items[$index])) {
            $this->items[$index]['item_id'] = $itemId ? (int) $itemId : '';
            if (!empty($itemId)) {
                $item = Item::find($itemId);
                if ($item) {
                    $this->items[$index]['item_code'] = $item->code_item ?? '';
                    $this->items[$index]['item_name'] = $item->name ?? '';
                    $this->items[$index]['unit'] = $item->unit ?: 'PCS';
                    if (empty($this->items[$index]['unit_price']) || (float)$this->items[$index]['unit_price'] == 0) {
                        $this->items[$index]['unit_price'] = (float) ($item->unit_price ?: 0);
                    }
                }
            } else {
                $this->items[$index]['item_code'] = '';
                $this->items[$index]['item_name'] = '';
            }
        }
    }

    /**
     * Computed property: Total estimated monetary value of all items in the form.
     */
    public function getTotalEstimatedAmountProperty(): float
    {
        $total = 0.0;
        foreach ($this->items as $row) {
            $qty = (float) ($row['quantity_ordered'] ?? 0);
            $price = (float) ($row['unit_price'] ?? 0);
            $total += ($qty * $price);
        }
        return $total;
    }

    /**
     * Auto-fill unit & price when item is selected.
     */
    public function updatedItems($value, $key)
    {
        // Check if an item_id was updated: items.0.item_id
        if (str_ends_with($key, '.item_id')) {
            $parts = explode('.', $key);
            $index = (int) $parts[1];

            if (!empty($value)) {
                $item = Item::find($value);
                if ($item) {
                    $this->items[$index]['item_code'] = $item->code_item;
                    $this->items[$index]['item_name'] = $item->name;
                    $this->items[$index]['unit'] = $item->unit ?: 'PCS';
                    $this->items[$index]['unit_price'] = (float) ($item->unit_price ?: 0);
                }
            } else {
                $this->items[$index]['item_code'] = '';
                $this->items[$index]['item_name'] = '';
            }
        }
    }

    /**
     * Save new Purchase Order.
     */
    public function saveCreate()
    {
        if (!auth()->user() || !auth()->user()->canManageProcurement()) {
            session()->flash('error', 'Akses ditolak: Hanya Procurement atau Web Administrator yang dapat membuat PO.');
            return;
        }

        $this->validate();

        DB::transaction(function () {
            $po = PurchaseOrder::create([
                'po_number' => strtoupper(trim($this->po_number)),
                'po_date' => $this->po_date,
                'supplier' => trim($this->supplier),
                'project_id' => $this->project_id ?: null,
                'status' => 'open',
                'eta_date' => $this->eta_date ?: null,
                'notes' => trim($this->notes) ?: null,
                'created_by' => auth()->id(),
            ]);

            foreach ($this->items as $row) {
                if (empty($row['item_id'])) {
                    continue;
                }

                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'item_id' => (int) $row['item_id'],
                    'quantity_ordered' => (float) $row['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit' => trim($row['unit']),
                    'unit_price' => !empty($row['unit_price']) ? (float) $row['unit_price'] : 0,
                    'notes' => !empty($row['notes']) ? trim($row['notes']) : null,
                ]);
            }

            ActivityLog::record(
                activity: 'Pembuatan Purchase Order',
                description: 'Membuat Purchase Order ' . $po->po_number . ' untuk supplier ' . $po->supplier . ' (' . count($this->items) . ' item).',
                module: 'Procurement'
            );
        });

        session()->flash('success', 'Purchase Order ' . $this->po_number . ' berhasil diterbitkan!');
        $this->closeCreateModal();
        $this->resetPage();
    }

    /**
     * Open View Detail PO Modal.
     */
    public function openDetailModal(int $id)
    {
        $this->detailPo = PurchaseOrder::with([
            'creator',
            'project',
            'items.item.category',
            'materialIns' => function ($q) {
                $q->latest()->with('item');
            }
        ])->findOrFail($id);

        $this->isDetailModalOpen = true;
    }

    public function closeDetailModal()
    {
        $this->isDetailModalOpen = false;
        $this->detailPo = null;
    }

    /**
     * Open Edit PO Modal.
     */
    public function openEditModal(int $id)
    {
        if (!auth()->user() || !auth()->user()->canManageProcurement()) {
            session()->flash('error', 'Akses ditolak: Hanya akun Procurement atau Web Administrator yang dapat mengedit Purchase Order.');
            return;
        }

        $po = PurchaseOrder::with('items.item')->findOrFail($id);

        if ($po->isCompleted()) {
            session()->flash('error', 'Purchase Order yang sudah selesai (Completed) tidak dapat diedit.');
            return;
        }

        if ($po->isCancelled()) {
            session()->flash('error', 'Purchase Order yang telah dibatalkan tidak dapat diedit.');
            return;
        }

        $this->resetValidation();
        $this->selectedPoId = $po->id;
        $this->po_number = $po->po_number;
        $this->po_date = $po->po_date ? $po->po_date->format('Y-m-d') : date('Y-m-d');
        $this->eta_date = $po->eta_date ? $po->eta_date->format('Y-m-d') : null;
        $this->supplier = $po->supplier;
        $this->project_id = $po->project_id;
        $this->notes = $po->notes ?? '';

        $this->items = [];
        foreach ($po->items as $it) {
            $this->items[] = [
                'id' => $it->id,
                'item_id' => $it->item_id,
                'item_code' => $it->item ? $it->item->code_item : '',
                'item_name' => $it->item ? $it->item->name : '',
                'quantity_ordered' => (float) $it->quantity_ordered,
                'quantity_received' => (float) $it->quantity_received,
                'unit' => $it->unit,
                'unit_price' => (float) $it->unit_price,
                'notes' => $it->notes ?? '',
            ];
        }

        $this->isEditModalOpen = true;
    }

    public function closeEditModal()
    {
        $this->isEditModalOpen = false;
        $this->resetValidation();
    }

    /**
     * Save updates to PO.
     */
    public function saveEdit()
    {
        if (!auth()->user() || !auth()->user()->canManageProcurement()) {
            session()->flash('error', 'Akses ditolak: Hanya akun Procurement atau Web Administrator yang dapat mengedit Purchase Order.');
            return;
        }

        $this->validate();

        $po = PurchaseOrder::with('items')->findOrFail($this->selectedPoId);

        if ($po->isCompleted() || $po->isCancelled()) {
            session()->flash('error', 'Purchase Order ini tidak dapat diedit lagi.');
            $this->closeEditModal();
            return;
        }

        DB::transaction(function () use ($po) {
            $po->update([
                'po_number' => strtoupper(trim($this->po_number)),
                'po_date' => $this->po_date,
                'supplier' => trim($this->supplier),
                'project_id' => $this->project_id ?: null,
                'eta_date' => $this->eta_date ?: null,
                'notes' => trim($this->notes) ?: null,
            ]);

            $keptItemIds = [];

            foreach ($this->items as $row) {
                if (empty($row['item_id'])) {
                    continue;
                }

                if (!empty($row['id'])) {
                    // Update existing row
                    $existingItem = PurchaseOrderItem::find($row['id']);
                    if ($existingItem && $existingItem->purchase_order_id === $po->id) {
                        $newOrdered = (float) $row['quantity_ordered'];
                        // Ordered qty cannot be less than received qty
                        if ($newOrdered < (float) $existingItem->quantity_received) {
                            $newOrdered = (float) $existingItem->quantity_received;
                        }

                        $existingItem->update([
                            'item_id' => (int) $row['item_id'],
                            'quantity_ordered' => $newOrdered,
                            'unit' => trim($row['unit']),
                            'unit_price' => !empty($row['unit_price']) ? (float) $row['unit_price'] : 0,
                            'notes' => !empty($row['notes']) ? trim($row['notes']) : null,
                        ]);
                        $keptItemIds[] = $existingItem->id;
                    }
                } else {
                    // Insert new row
                    $newItem = PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'item_id' => (int) $row['item_id'],
                        'quantity_ordered' => (float) $row['quantity_ordered'],
                        'quantity_received' => 0,
                        'unit' => trim($row['unit']),
                        'unit_price' => !empty($row['unit_price']) ? (float) $row['unit_price'] : 0,
                        'notes' => !empty($row['notes']) ? trim($row['notes']) : null,
                    ]);
                    $keptItemIds[] = $newItem->id;
                }
            }

            // Delete removed items only if no receipts have occurred
            foreach ($po->items as $oldItem) {
                if (!in_array($oldItem->id, $keptItemIds)) {
                    if ((float) $oldItem->quantity_received == 0) {
                        $oldItem->delete();
                    }
                }
            }

            // Recalculate status
            $po->recalculateStatus();

            ActivityLog::record(
                activity: 'Pembaruan Purchase Order',
                description: 'Memperbarui data Purchase Order ' . $po->po_number . '.',
                module: 'Procurement'
            );
        });

        session()->flash('success', 'Purchase Order ' . $po->po_number . ' berhasil diperbarui!');
        $this->closeEditModal();
    }

    /**
     * Open Cancel PO Modal.
     */
    public function openCancelModal(int $id)
    {
        if (!auth()->user() || !auth()->user()->canManageProcurement()) {
            session()->flash('error', 'Akses ditolak: Hanya akun Procurement atau Web Administrator yang dapat membatalkan Purchase Order.');
            return;
        }

        $po = PurchaseOrder::with('items')->findOrFail($id);

        if ($po->isCompleted()) {
            session()->flash('error', 'PO yang sudah berstatus Selesai (Completed) tidak dapat dibatalkan.');
            return;
        }

        $hasReceivedItems = $po->items->contains(fn($it) => (float) $it->quantity_received > 0);
        if ($hasReceivedItems) {
            session()->flash('error', 'PO ini sudah memiliki riwayat penerimaan barang di gudang sehingga tidak dapat dibatalkan.');
            return;
        }

        $this->selectedPoId = $po->id;
        $this->cancelReason = '';
        $this->isCancelModalOpen = true;
    }

    public function closeCancelModal()
    {
        $this->isCancelModalOpen = false;
        $this->selectedPoId = null;
        $this->cancelReason = '';
    }

    /**
     * Confirm PO Cancellation.
     */
    public function confirmCancel()
    {
        if (!auth()->user() || !auth()->user()->canManageProcurement()) {
            session()->flash('error', 'Akses ditolak.');
            return;
        }

        $po = PurchaseOrder::findOrFail($this->selectedPoId);

        $hasReceivedItems = $po->items->contains(fn($it) => (float) $it->quantity_received > 0);
        if ($hasReceivedItems) {
            session()->flash('error', 'PO tidak dapat dibatalkan karena ada barang yang sudah diterima gudang.');
            $this->closeCancelModal();
            return;
        }

        $reason = trim($this->cancelReason);
        $updatedNotes = $po->notes ? $po->notes . ' [DIBATALKAN: ' . ($reason ?: 'Dibatalkan oleh Procurement') . ']' : '[DIBATALKAN: ' . ($reason ?: 'Dibatalkan oleh Procurement') . ']';

        $po->update([
            'status' => 'cancelled',
            'notes' => $updatedNotes,
        ]);

        ActivityLog::record(
            activity: 'Pembatalan Purchase Order',
            description: 'Membatalkan Purchase Order ' . $po->po_number . '. Alasan: ' . ($reason ?: '-'),
            module: 'Procurement'
        );

        session()->flash('success', 'Purchase Order ' . $po->po_number . ' telah berhasil dibatalkan.');
        $this->closeCancelModal();
    }

    public function render()
    {
        $query = PurchaseOrder::query()
            ->with(['creator', 'project', 'items.item'])
            ->withCount('items');

        // Search
        if (!empty($this->search)) {
            $term = trim($this->search);
            $query->where(function ($q) use ($term) {
                $q->where('po_number', 'like', "%{$term}%")
                    ->orWhere('supplier', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhereHas('items.item', function ($iq) use ($term) {
                        $iq->where('name', 'like', "%{$term}%")
                            ->orWhere('code_item', 'like', "%{$term}%");
                    });
            });
        }

        // Status Filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Project Filter
        if (!empty($this->projectFilter)) {
            if ($this->projectFilter === 'general') {
                $query->whereNull('project_id');
            } else {
                $query->where('project_id', (int) $this->projectFilter);
            }
        }

        // Date Range
        if (!empty($this->dateFrom)) {
            $query->whereDate('po_date', '>=', $this->dateFrom);
        }
        if (!empty($this->dateTo)) {
            $query->whereDate('po_date', '<=', $this->dateTo);
        }

        $purchaseOrders = $query->latest('po_date')->latest('id')->paginate($this->perPage);

        // Summary metrics
        $metrics = [
            'total' => PurchaseOrder::count(),
            'open' => PurchaseOrder::where('status', 'open')->count(),
            'partial' => PurchaseOrder::where('status', 'partial')->count(),
            'completed' => PurchaseOrder::where('status', 'completed')->count(),
            'cancelled' => PurchaseOrder::where('status', 'cancelled')->count(),
        ];

        // All active projects for filter & dropdown
        $projects = Project::select('id', 'name', 'code')->orderBy('name')->get();

        $projectsJsonList = $projects->map(fn($p) => [
            'id' => (string) $p->id,
            'name' => $p->name,
            'code' => $p->code ?? '',
        ])->values();

        // Items for dropdown and searchable combobox in modal
        $availableItems = Item::with('category:id,name')
            ->select('id', 'category_id', 'code_item', 'name', 'unit', 'stock', 'unit_price')
            ->orderBy('name')
            ->get();

        $itemsJsonList = $availableItems->map(fn($it) => [
            'id' => (string) $it->id,
            'name' => $it->name,
            'code' => $it->code_item ?? '',
            'category' => $it->category?->name ?? '',
            'stock' => (float) $it->stock,
            'unit' => $it->unit ?? 'PCS',
            'unit_price' => (float) ($it->unit_price ?? 0),
        ])->values();

        return view('livewire.purchase-order-component', [
            'purchaseOrders' => $purchaseOrders,
            'metrics' => $metrics,
            'projects' => $projects,
            'projectsJsonList' => $projectsJsonList,
            'availableItems' => $availableItems,
            'itemsJsonList' => $itemsJsonList,
        ]);
    }
}
