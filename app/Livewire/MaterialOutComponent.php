<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\ImportStatus;
use App\Models\Item;
use App\Models\MaterialOut;
use App\Models\StockLog;
use App\Services\FastMaterialOutImportService;
use App\Services\MaterialOutExcelExportService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class MaterialOutComponent extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = 'tailwind';

    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $selectedItemId = '';
    public array $selectedItemIds = [];
    public $selectedProject = '';
    public $selectedUnit = '';

    // Category Module Tabs ('all', 'Consumable', 'Secondary Part', 'Cat / Painting')
    public $activeModule = 'all';

    // File Import states
    public $file;
    public $activeImportId = null;
    public $importErrorMessage = null;
    public $showImportCard = false;

    // Modal state
    public $isModalOpen = false;
    public $isEditMode = false;
    public $selectedId = null;
    public $activeTab = 'bulk'; // 'bulk' or 'single'
    public array $bulkRows = [];

    // Form fields
    public $date;
    public $item_id = '';
    public $quantity = 1;
    public $unit = 'PCS';
    public $project = '';
    public $pic = '';
    public $notes = '';

    // Wholesale UOM Conversion states
    public $input_mode = 'base'; // 'base' or 'wholesale'
    public $wholesale_qty = null;
    public $wholesale_unit = null;
    public $conversion_factor = 1;

    // Selected item preview
    public $selectedItemStock = null;
    public $selectedItemUnit = null;
    public $isCatItem = false;
    public $catManufacturingDate = null;

    protected $rules = [
        'date' => 'required|date',
        'item_id' => 'required|exists:items,id',
        'quantity' => 'required|numeric|min:0.001',
        'project' => 'required|string|max:255',
        'pic' => 'required|string|max:255',
        'notes' => 'nullable|string|max:500',
    ];

    public function mount()
    {
        $this->date = date('Y-m-d');
        if (request()->query('module')) {
            $this->activeModule = request()->query('module');
        }
    }

    public function setActiveModule($module)
    {
        $this->activeModule = $module;
        $this->resetPage();
    }

    public function setFilterLastMonth()
    {
        $latestDateStr = MaterialOut::max('date') ?? date('Y-m-d');
        $latestCarbon = \Carbon\Carbon::parse($latestDateStr);
        $this->dateTo = $latestCarbon->format('Y-m-d');
        $this->dateFrom = $latestCarbon->copy()->subDays(30)->format('Y-m-d');
        $this->resetPage();
    }

    public function setFilterLast7Days()
    {
        $latestDateStr = MaterialOut::max('date') ?? date('Y-m-d');
        $latestCarbon = \Carbon\Carbon::parse($latestDateStr);
        $this->dateTo = $latestCarbon->format('Y-m-d');
        $this->dateFrom = $latestCarbon->copy()->subDays(7)->format('Y-m-d');
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
        $this->selectedItemId = '';
        $this->selectedItemIds = [];
        $this->selectedProject = '';
        $this->selectedUnit = '';
        $this->resetPage();
    }

    public function toggleImportCard()
    {
        $this->showImportCard = !$this->showImportCard;
    }

    public function startImport()
    {
        @set_time_limit(1800);
        @ini_set('memory_limit', '1024M');

        $this->importErrorMessage = null;

        if (!$this->file) {
            $this->importErrorMessage = 'Silakan pilih file Excel/CSV terlebih dahulu.';
            return;
        }

        $extension = strtolower($this->file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $this->importErrorMessage = 'Format file harus berupa .xlsx, .xls, atau .csv. Format file Anda: .' . $extension;
            return;
        }

        try {
            $originalName = $this->file->getClientOriginalName();
            $path = $this->file->store('imports');
            $fullPath = storage_path('app/' . $path);

            $importStatus = ImportStatus::create([
                'user_id' => auth()->id() ?? 1,
                'filename' => $originalName,
                'total_rows' => 0,
                'status' => 'processing',
            ]);

            $this->activeImportId = $importStatus->id;

            // Run Fast Import Service
            $fastService = new FastMaterialOutImportService($importStatus->id, auth()->id() ?? 1);
            $totalProcessed = $fastService->import($fullPath);

            ActivityLog::record(
                activity: 'Import Excel Material Keluar',
                description: "Memproses import file Excel Material Keluar ({$originalName}). Total: {$totalProcessed} baris",
                module: 'Material Keluar'
            );

            $this->reset('file');
            session()->flash('success', "File \"{$originalName}\" berhasil diunggah! {$totalProcessed} baris pengeluaran material telah diproses.");
        } catch (\Throwable $e) {
            $this->importErrorMessage = 'Gagal memproses file Excel: ' . $e->getMessage();
        }
    }

    public function downloadTemplate()
    {
        $csvContent = "NO,TANGGAL,NAMA BARANG,JUMLAH,SATUAN,PROJECT,PIC\n";
        $csvContent .= "1,01 September 2026,KAWAT LAS SOLID 1,2MM,10,PCS,Project EPC Tuban,Budi Santoso\n";
        $csvContent .= "2,01 September 2026,CONTACTIP LONG 1,2 MM,5,PCS,Piping Gresik,Siti Rahma\n";

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, 'Template_Import_Material_Keluar.csv');
    }

    public function downloadErrorReport($importId)
    {
        $import = ImportStatus::findOrFail($importId);
        $errors = $import->error_logs ?? [];

        $csvData = "Baris,Kode/Nama Barang,Alasan Gagal\n";
        foreach ($errors as $err) {
            $csvData .= "\"{$err['row']}\",\"{$err['code']}\",\"{$err['reason']}\"\n";
        }

        return response()->streamDownload(function () use ($csvData) {
            echo $csvData;
        }, 'Laporan_Error_Import_Material_Keluar_' . $import->id . '.csv');
    }

    public function updatedItemId($value)
    {
        if ($value) {
            $item = Item::with('category')->find($value);
            if ($item) {
                $this->selectedItemStock = $item->stock;
                $this->selectedItemUnit = $item->unit;
                $this->unit = $item->unit;
                $this->wholesale_unit = $item->wholesale_unit;
                $this->conversion_factor = $item->conversion_factor ?: 1;
                $this->isCatItem = $item->isCat();
                $this->catManufacturingDate = $item->manufacturing_date ? format_date($item->manufacturing_date) : null;

                $parsedWholesale = str_replace(',', '.', (string)$this->wholesale_qty);
                if ($this->input_mode === 'wholesale' && is_numeric($parsedWholesale) && $this->conversion_factor > 1) {
                    $this->quantity = (float) $parsedWholesale * (float) $this->conversion_factor;
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
        $this->catManufacturingDate = null;
    }

    public function openCreateModal()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Role PPIC hanya memiliki hak akses melihat data.');
            return;
        }
        $this->resetForm();
        $this->date = date('Y-m-d');
        $this->isEditMode = false;
        $this->activeTab = 'bulk';
        $this->bulkRows = [
            ['item_id' => '', 'quantity' => 1, 'unit' => 'PCS', 'project' => '', 'pic' => '', 'notes' => ''],
            ['item_id' => '', 'quantity' => 1, 'unit' => 'PCS', 'project' => '', 'pic' => '', 'notes' => ''],
            ['item_id' => '', 'quantity' => 1, 'unit' => 'PCS', 'project' => '', 'pic' => '', 'notes' => ''],
        ];
        $this->isModalOpen = true;
    }

    public function addBulkRow($count = 1)
    {
        $count = max(1, min(50, (int) $count));
        for ($i = 0; $i < $count; $i++) {
            $this->bulkRows[] = ['item_id' => '', 'quantity' => 1, 'unit' => 'PCS', 'project' => '', 'pic' => '', 'notes' => ''];
        }
    }

    public function addBulkRows($count = 5)
    {
        $this->addBulkRow($count);
    }

    public function removeBulkRow($index)
    {
        if (count($this->bulkRows) > 1) {
            unset($this->bulkRows[$index]);
            $this->bulkRows = array_values($this->bulkRows);
        }
    }

    public function applyFirstProjectToAll($customProject = null)
    {
        $firstPrj = !empty($customProject) ? $customProject : ($this->bulkRows[0]['project'] ?? '');
        if (!empty($firstPrj)) {
            $this->bulkRows[0]['project'] = $firstPrj;
            foreach ($this->bulkRows as $idx => $r) {
                $this->bulkRows[$idx]['project'] = $firstPrj;
            }
            $this->dispatch('bulk-projects-applied', project: $firstPrj);
        }
    }

    public function applyFirstPicToAll($customPic = null)
    {
        $firstPic = !empty($customPic) ? $customPic : ($this->bulkRows[0]['pic'] ?? '');
        if (!empty($firstPic)) {
            $this->bulkRows[0]['pic'] = $firstPic;
            foreach ($this->bulkRows as $idx => $r) {
                $this->bulkRows[$idx]['pic'] = $firstPic;
            }
            $this->dispatch('bulk-pics-applied', pic: $firstPic);
        }
    }

    public function updatedBulkRows($value, $key)
    {
        if (str_contains($key, 'item_id')) {
            $parts = explode('.', $key);
            $index = ($parts[0] === 'bulkRows' && isset($parts[1])) ? (int) $parts[1] : (int) $parts[0];
            $itemId = $value ?: ($this->bulkRows[$index]['item_id'] ?? null);
            if ($itemId) {
                $item = Item::find($itemId);
                if ($item) {
                    $this->bulkRows[$index]['unit'] = $item->unit;
                }
            }
        }
    }

    public function applyCalculatedQuantity($quantity, $rowIndex = null, $unit = 'KG')
    {
        $qty = (float) str_replace(',', '.', (string)$quantity);
        if ($this->activeTab === 'bulk' && !$this->isEditMode && $rowIndex !== null) {
            $idx = (int) $rowIndex;
            if (isset($this->bulkRows[$idx])) {
                $this->bulkRows[$idx]['quantity'] = $qty;
                if (!empty($unit)) {
                    $this->bulkRows[$idx]['unit'] = $unit;
                }
            }
        } else {
            $this->quantity = $qty;
            if (!empty($unit)) {
                $this->unit = $unit;
            }
        }
    }

    public function openEditModal($id)
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Role PPIC hanya memiliki hak akses melihat data.');
            return;
        }
        $record = MaterialOut::findOrFail($id);
        $this->selectedId = $record->id;
        $this->date = $record->date->format('Y-m-d');
        $this->item_id = $record->item_id;
        $this->quantity = $record->quantity;
        $this->unit = $record->unit;
        $this->wholesale_qty = $record->wholesale_qty;
        $this->wholesale_unit = $record->wholesale_unit;
        $this->input_mode = $record->wholesale_qty ? 'wholesale' : 'base';
        $this->project = $record->project;
        $this->pic = $record->pic;
        $this->notes = $record->notes;

        $item = Item::with('category')->find($record->item_id);
        if ($item) {
            $this->selectedItemStock = $item->stock;
            $this->selectedItemUnit = $item->unit;
            $this->wholesale_unit = $item->wholesale_unit;
            $this->conversion_factor = $item->conversion_factor ?: 1;
            $this->isCatItem = $item->isCat();
            $this->catManufacturingDate = $item->manufacturing_date ? format_date($item->manufacturing_date) : null;
        }

        $this->isEditMode = true;
        $this->activeTab = 'single';
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->resetValidation();
        $this->selectedId = null;
        $this->date = date('Y-m-d');
        $this->item_id = '';
        $this->quantity = 1;
        $this->unit = 'PCS';
        $this->wholesale_qty = null;
        $this->wholesale_unit = null;
        $this->input_mode = 'base';
        $this->project = '';
        $this->pic = '';
        $this->notes = '';
        $this->bulkRows = [];
        $this->resetItemPreview();
    }

    public function save()
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            abort(403, 'Akses PPIC hanya untuk melihat data.');
        }

        if ($this->project === '__OTHER__') {
            $this->addError('project', 'Silakan masukkan atau ketik nama project / keperluan baru.');
            return;
        }

        // Bulk Mode Save
        if (!$this->isEditMode && $this->activeTab === 'bulk') {
            if (empty($this->date)) {
                $this->addError('date', 'Tanggal keluar wajib diisi.');
                return;
            }

            // Normalize comma in quantities
            foreach ($this->bulkRows as $idx => $r) {
                if (isset($r['quantity'])) {
                    $this->bulkRows[$idx]['quantity'] = (float) str_replace(',', '.', (string)$r['quantity']);
                }
            }

            $validRows = array_filter($this->bulkRows, fn($r) => !empty($r['item_id']) && (float)($r['quantity'] ?? 0) > 0);

            if (empty($validRows)) {
                session()->flash('error', 'Minimal pilih 1 barang dengan jumlah lebih dari 0.');
                $this->dispatch('swal:error', title: 'Gagal!', message: 'Minimal pilih 1 barang dengan jumlah lebih dari 0.');
                return;
            }

            // Bug 1 Fix: Akumulasikan total quantity per item_id agar barang duplikat di baris berbeda tidak menembus batas stok
            $requestedPerItem = [];
            foreach ($validRows as $row) {
                $itemId = $row['item_id'];
                $rowQty = (float) str_replace(',', '.', (string)($row['quantity'] ?? 0));
                $requestedPerItem[$itemId] = ($requestedPerItem[$itemId] ?? 0) + $rowQty;
            }

            $stockErrors = [];
            foreach ($requestedPerItem as $itemId => $totalQty) {
                $item = Item::find($itemId);
                if (!$item) continue;
                if ($totalQty > $item->stock) {
                    $stockErrors[] = "Total permintaan untuk {$item->name} ({$totalQty} {$item->unit}) melebihi stok yang tersedia ({$item->stock} {$item->unit}).";
                }
            }

            foreach ($validRows as $idx => $row) {
                $rowNum = $idx + 1;
                $rowProject = !empty($row['project']) ? trim($row['project']) : trim($this->project);
                $rowPic = !empty($row['pic']) ? trim($row['pic']) : trim($this->pic);

                if (empty($rowProject)) {
                    $stockErrors[] = "Baris #{$rowNum}: Nama Project / Keperluan wajib diisi.";
                }
                if (empty($rowPic)) {
                    $stockErrors[] = "Baris #{$rowNum}: PIC Penerima wajib diisi.";
                }
            }

            if (!empty($stockErrors)) {
                $errMsg = implode(' | ', $stockErrors);
                session()->flash('error', $errMsg);
                $this->dispatch('swal:error', title: 'Gagal!', message: $errMsg);
                return;
            }

            try {
                DB::transaction(function () use ($validRows) {
                    $insertedCount = 0;
                    foreach ($validRows as $row) {
                        // Optimasi 3: Pessimistic locking (lockForUpdate) untuk mencegah race condition
                        $item = Item::where('id', $row['item_id'])->lockForUpdate()->firstOrFail();
                        $qty = (float) str_replace(',', '.', (string)$row['quantity']);

                        if ($qty > $item->stock) {
                            throw new \Exception("Stok tidak mencukupi untuk {$item->name} saat pemrosesan (Sisa stok: {$item->stock} {$item->unit}, Diminta: {$qty}).");
                        }

                        $unit = !empty($item->unit) ? $item->unit : (!empty($row['unit']) ? $row['unit'] : 'PCS');
                        $rowProject = !empty($row['project']) ? trim($row['project']) : trim($this->project);
                        $rowPic = !empty($row['pic']) ? trim($row['pic']) : trim($this->pic);
                        $rowNotes = !empty($row['notes']) ? trim($row['notes']) : $this->notes;
                        $refNo = 'OUT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                        $projectObj = \App\Models\Project::firstOrCreate(
                            ['name' => $rowProject],
                            [
                                'code' => 'PRJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                                'status' => 'Ongoing',
                                'location' => $rowProject,
                                'created_by' => auth()->id(),
                            ]
                        );

                        MaterialOut::create([
                            'reference_number' => $refNo,
                            'date' => $this->date,
                            'item_id' => $item->id,
                            'unit_id' => $item->unit_id,
                            'quantity' => $qty,
                            'unit' => $unit,
                            'project' => $rowProject,
                            'project_id' => $projectObj->id,
                            'pic' => $rowPic,
                            'notes' => $rowNotes,
                            'user_id' => auth()->id(),
                        ]);

                        $item->decrement('stock', $qty);

                        StockLog::create([
                            'item_id' => $item->id,
                            'user_id' => auth()->id() ?? 1,
                            'type' => 'out',
                            'quantity' => $qty,
                            'recipient_or_provider' => $rowPic,
                            'reference_number' => $refNo,
                            'notes' => "Pengeluaran material (Bulk) untuk project {$rowProject} (PIC: {$rowPic})",
                        ]);

                        $insertedCount++;
                    }

                    session()->flash('success', "Berhasil mencatat {$insertedCount} pengeluaran material!");
                    $this->dispatch('swal:success', title: 'Berhasil!', message: "Berhasil mencatat {$insertedCount} pengeluaran material!");
                });
            } catch (\Exception $e) {
                session()->flash('error', $e->getMessage());
                $this->dispatch('swal:error', title: 'Gagal Transaksi!', message: $e->getMessage());
                return;
            }

            $this->closeModal();
            return;
        }

        // Single Mode Save
        if ($this->quantity !== null && $this->quantity !== '') {
            $this->quantity = (float) str_replace(',', '.', (string)$this->quantity);
        }
        if ($this->wholesale_qty !== null && $this->wholesale_qty !== '') {
            $this->wholesale_qty = (float) str_replace(',', '.', (string)$this->wholesale_qty);
        }

        $this->validate();

        $item = Item::findOrFail($this->item_id);

        if ($this->input_mode === 'wholesale' && is_numeric($this->wholesale_qty) && $this->conversion_factor > 1) {
            $this->quantity = (float) $this->wholesale_qty * (float) $this->conversion_factor;
        }

        if (!$this->isEditMode) {
            if ($this->quantity > $item->stock) {
                $this->addError('quantity', "Stok tidak mencukupi! Stok {$item->name} saat ini hanya {$item->stock} {$item->unit}.");
                return;
            }
        } else {
            $existing = MaterialOut::findOrFail($this->selectedId);
            $availableStock = $item->stock + ($existing->item_id == $item->id ? $existing->quantity : 0);
            if ($this->quantity > $availableStock) {
                $this->addError('quantity', "Stok tidak mencukupi! Stok maksimal tersedia: {$availableStock} {$item->unit}.");
                return;
            }
        }

        $wholesaleQty = ($this->input_mode === 'wholesale' && is_numeric($this->wholesale_qty)) ? (float) $this->wholesale_qty : null;
        $wholesaleUnit = ($this->input_mode === 'wholesale') ? $this->wholesale_unit : null;

        try {
            DB::transaction(function () use ($wholesaleQty, $wholesaleUnit) {
                $refNo = 'OUT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                $projectObj = \App\Models\Project::firstOrCreate(
                    ['name' => trim($this->project)],
                    [
                        'code' => 'PRJ-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4)),
                        'status' => 'Ongoing',
                        'location' => trim($this->project),
                        'created_by' => auth()->id(),
                    ]
                );

                if (!$this->isEditMode) {
                    // Optimasi 3: Pessimistic locking (lockForUpdate)
                    $item = Item::where('id', $this->item_id)->lockForUpdate()->firstOrFail();

                    if ($this->quantity > $item->stock) {
                        throw new \Exception("Stok tidak mencukupi! Stok {$item->name} saat ini hanya {$item->stock} {$item->unit}.");
                    }

                    MaterialOut::create([
                        'reference_number' => $refNo,
                        'date' => $this->date,
                        'item_id' => $this->item_id,
                        'unit_id' => $item->unit_id,
                        'quantity' => $this->quantity,
                        'wholesale_qty' => $wholesaleQty,
                        'wholesale_unit' => $wholesaleUnit,
                        'unit' => $this->unit ?: $item->unit,
                        'project' => $this->project,
                        'project_id' => $projectObj->id,
                        'pic' => $this->pic,
                        'notes' => $this->notes,
                        'user_id' => auth()->id(),
                    ]);

                    $item->decrement('stock', $this->quantity);

                    StockLog::create([
                        'item_id' => $item->id,
                        'user_id' => auth()->id() ?? 1,
                        'type' => 'out',
                        'quantity' => $this->quantity,
                        'recipient_or_provider' => $this->pic,
                        'reference_number' => $refNo,
                        'notes' => "Material Keluar untuk Project: {$this->project} (PIC: {$this->pic})",
                    ]);

                    ActivityLog::record(
                        activity: 'Tambah Material Keluar',
                        description: "Mengeluarkan " . format_quantity($this->quantity) . " {$item->unit} {$item->name} untuk Project: {$this->project} (PIC: {$this->pic})",
                        module: 'Material Keluar'
                    );

                    session()->flash('success', "Material Keluar '{$item->name}' sebanyak " . format_quantity($this->quantity) . " {$this->unit} berhasil dicatat!");
                    $this->dispatch('swal:success', title: 'Berhasil!', message: "Material Keluar '{$item->name}' sebanyak " . format_quantity($this->quantity) . " {$this->unit} berhasil dicatat!");
                } else {
                    $materialOut = MaterialOut::findOrFail($this->selectedId);

                    if ($materialOut->item_id != $this->item_id) {
                        $oldItem = Item::where('id', $materialOut->item_id)->lockForUpdate()->first();
                        if ($oldItem) {
                            $oldItem->increment('stock', $materialOut->quantity);
                        }

                        $item = Item::where('id', $this->item_id)->lockForUpdate()->firstOrFail();
                        if ($this->quantity > $item->stock) {
                            throw new \Exception("Stok tidak mencukupi! Stok {$item->name} saat ini hanya {$item->stock} {$item->unit}.");
                        }
                        $item->decrement('stock', $this->quantity);
                    } else {
                        $item = Item::where('id', $this->item_id)->lockForUpdate()->firstOrFail();
                        $diff = (float)$this->quantity - (float)$materialOut->quantity;
                        if ($diff > 0 && $diff > $item->stock) {
                            throw new \Exception("Stok tidak mencukupi! Tambahan stok yang dibutuhkan: {$diff} {$item->unit}, tersedia: {$item->stock} {$item->unit}.");
                        }
                        $item->decrement('stock', $diff);
                    }

                    $materialOut->update([
                        'date' => $this->date,
                        'item_id' => $this->item_id,
                        'unit_id' => $item->unit_id,
                        'quantity' => $this->quantity,
                        'wholesale_qty' => $wholesaleQty,
                        'wholesale_unit' => $wholesaleUnit,
                        'unit' => $this->unit ?: $item->unit,
                        'project' => $this->project,
                        'project_id' => $projectObj->id,
                        'pic' => $this->pic,
                        'notes' => $this->notes,
                    ]);

                    ActivityLog::record(
                        activity: 'Edit Material Keluar',
                        description: "Memperbarui data pengeluaran material ref {$materialOut->reference_number}",
                        module: 'Material Keluar'
                    );

                    session()->flash('success', "Data Material Keluar berhasil diperbarui!");
                    $this->dispatch('swal:success', title: 'Berhasil!', message: "Data Material Keluar berhasil diperbarui!");
                }
            });
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            $this->dispatch('swal:error', title: 'Gagal!', message: $e->getMessage());
            return;
        }

        $this->closeModal();
    }

    public function delete($id)
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            abort(403, 'Akses PPIC hanya untuk melihat data.');
        }

        $record = MaterialOut::findOrFail($id);

        DB::transaction(function () use ($record) {
            // Optimasi 3: Pessimistic lock saat mengembalikan stok
            $item = Item::where('id', $record->item_id)->lockForUpdate()->first();
            if ($item) {
                $item->increment('stock', $record->quantity);

                // Bug 3 Fix: Catat mutasi penyesuaian di StockLog agar audit log seimbang
                StockLog::create([
                    'item_id' => $item->id,
                    'user_id' => auth()->id() ?? 1,
                    'type' => 'in',
                    'quantity' => $record->quantity,
                    'recipient_or_provider' => 'Pembatalan Pengeluaran',
                    'reference_number' => $record->reference_number,
                    'notes' => "Pengembalian stok dari pembatalan/penghapusan pengeluaran material ref {$record->reference_number}",
                ]);
            }

            ActivityLog::record(
                activity: 'Hapus Material Keluar',
                description: "Menghapus pengeluaran material ref {$record->reference_number} ({$record->quantity} {$record->unit}) & mengembalikan stok",
                module: 'Material Keluar'
            );

            $record->delete();
        });

        session()->flash('success', 'Data Material Keluar berhasil dihapus dan stok barang telah dikembalikan.');
        $this->dispatch('swal:success', title: 'Deleted!', message: 'Data Material Keluar berhasil dihapus dan stok barang telah dikembalikan.');
    }

    public function exportExcel(?MaterialOutExcelExportService $exportService = null)
    {
        $exportService = $exportService ?? app(MaterialOutExcelExportService::class);
        $records = $this->getFilteredQuery()->get();

        return $exportService->export(
            records: $records,
            startDate: $this->dateFrom,
            endDate: $this->dateTo,
            module: $this->activeModule ?? 'all'
        );
    }

    public function exportCsv(?MaterialOutExcelExportService $exportService = null)
    {
        return $this->exportExcel($exportService);
    }

    private function getFilteredQuery()
    {
        $query = MaterialOut::with(['item.category']);

        // Module / Category filter
        if (!empty($this->activeModule) && $this->activeModule !== 'all') {
            $mod = $this->activeModule;
            $query->whereHas('item.category', function ($cq) use ($mod) {
                if ($mod === 'Cat / Painting') {
                    $cq->where('name', 'like', '%Cat%');
                } else {
                    $cq->where('name', $mod);
                }
            });
        }

        if (!empty($this->search)) {
            $s = $this->search;
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

        if (!empty($this->dateFrom)) {
            $query->whereDate('date', '>=', $this->dateFrom);
        }

        if (!empty($this->dateTo)) {
            $query->whereDate('date', '<=', $this->dateTo);
        }

        if (!empty($this->selectedItemIds)) {
            $query->whereIn('item_id', array_filter($this->selectedItemIds));
        } elseif (!empty($this->selectedItemId)) {
            $query->where('item_id', $this->selectedItemId);
        }

        if (!empty($this->selectedProject)) {
            $query->where('project', $this->selectedProject);
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

        return $query->latest('date')->latest('id');
    }

    public function render()
    {
        $perPage = CompanyProfile::getDefaultPerPage();
        $materialOuts = $this->getFilteredQuery()->paginate($perPage);
        $units = \App\Models\Unit::select(['id', 'name', 'symbol'])->where('is_active', true)->orderBy('name')->get();
        
        // Scope items list if activeModule is set
        $itemsQuery = Item::select(['id', 'name', 'code_item', 'category_id', 'stock', 'unit'])
            ->with('category:id,name')
            ->orderBy('name', 'asc');
        if (!empty($this->activeModule) && $this->activeModule !== 'all') {
            $mod = $this->activeModule;
            $itemsQuery->whereHas('category', function ($q) use ($mod) {
                if ($mod === 'Cat / Painting') {
                    $q->where('name', 'like', '%Cat%');
                } else {
                    $q->where('name', $mod);
                }
            });
        }
        $items = $itemsQuery->get();
        $allItems = ($this->activeModule === 'all' || empty($this->activeModule)) 
            ? $items 
            : Item::select(['id', 'name', 'code_item', 'category_id', 'stock', 'unit'])->orderBy('name', 'asc')->get();

        $allRegisteredProjects = \App\Models\Project::select(['id', 'name', 'code', 'location', 'status', 'client'])->orderBy('name', 'asc')->get();
        $registeredProjectNames = $allRegisteredProjects->pluck('name')->toArray();

        $historicalProjects = MaterialOut::select('project')
            ->distinct()
            ->whereNotNull('project')
            ->where('project', '!=', '')
            ->orderBy('project', 'asc')
            ->pluck('project')
            ->toArray();

        $otherProjectsList = array_values(array_diff($historicalProjects, $registeredProjectNames));

        $projectsList = array_unique(array_merge($registeredProjectNames, $historicalProjects));
        sort($projectsList);

        $filteredTotalQuantity = $this->getFilteredQuery()->sum('quantity');
        $filteredTotalRecords = $this->getFilteredQuery()->count();

        $userId = auth()->id() ?? 1;
        $activeImport = $this->activeImportId 
            ? ImportStatus::find($this->activeImportId)
            : ImportStatus::where('user_id', $userId)->latest()->first();

        // Stat metrics
        $totalTransactions = MaterialOut::count();
        $totalProjects = MaterialOut::distinct('project')->count('project');

        // Separate discrete and decimal outputs for this month
        $monthOutRows = MaterialOut::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->leftJoin('items', 'material_outs.item_id', '=', 'items.id')
            ->select('material_outs.quantity', DB::raw("UPPER(TRIM(COALESCE(material_outs.unit, items.unit, 'PCS'))) as item_unit"))
            ->get();

        $monthDiscreteQty = 0;
        $monthDecimalTotals = [];
        foreach ($monthOutRows as $mo) {
            $u = $mo->item_unit;
            $q = (float) $mo->quantity;
            if (is_decimal_unit($u)) {
                $monthDecimalTotals[$u] = ($monthDecimalTotals[$u] ?? 0) + $q;
            } else {
                $monthDiscreteQty += $q;
            }
        }
        $monthDecimalString = format_unit_breakdown($monthDecimalTotals);

        // Module specific counts in 1 consolidated query (menggantikan 3x whereHas bertingkat)
        $catCategoryIds = Category::getCatCategoryIds();
        $catIdsSql = !empty($catCategoryIds) ? implode(',', $catCategoryIds) : '0';
        $moduleCounts = MaterialOut::leftJoin('items', 'material_outs.item_id', '=', 'items.id')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->selectRaw("
                COUNT(CASE WHEN categories.name = 'Consumable' THEN 1 END) as consumable_count,
                COUNT(CASE WHEN categories.name = 'Secondary Part' THEN 1 END) as secondary_count,
                COUNT(CASE WHEN categories.name LIKE '%Cat%' OR items.category_id IN ({$catIdsSql}) THEN 1 END) as cat_count
            ")->first();

        $consumableCount = (int) ($moduleCounts->consumable_count ?? 0);
        $secondaryCount = (int) ($moduleCounts->secondary_count ?? 0);
        $catCount = (int) ($moduleCounts->cat_count ?? 0);

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

        $itemsJsonList = $items->map(fn($it) => [
            'id' => (string) $it->id,
            'name' => $it->name,
            'code' => $it->code_item ?? '',
            'category' => $it->category?->name ?? 'Consumable',
            'stock' => (float) $it->stock,
            'unit' => $it->unit ?? 'PCS',
        ])->values()->toArray();

        $picsFromOut = MaterialOut::select('pic')
            ->distinct()
            ->whereNotNull('pic')
            ->where('pic', '!=', '')
            ->pluck('pic')
            ->toArray();

        $userNames = \App\Models\User::pluck('name')->toArray();

        $picSuggestionsList = array_values(array_unique(array_filter(array_merge($picsFromOut, $userNames))));
        sort($picSuggestionsList, SORT_NATURAL | SORT_FLAG_CASE);

        return view('livewire.material-out-component', [
            'materialOuts' => $materialOuts,
            'units' => $units,
            'items' => $items,
            'allItems' => $allItems,
            'itemsJsonList' => $itemsJsonList,
            'projectsList' => $projectsList,
            'allRegisteredProjects' => $allRegisteredProjects,
            'projectsJsonList' => $projectsJsonList,
            'otherProjectsList' => $otherProjectsList,
            'picSuggestionsList' => $picSuggestionsList,
            'activeImport' => $activeImport,
            'totalTransactions' => $totalTransactions,
            'totalQuantityOutThisMonth' => $monthDiscreteQty,
            'monthDiscreteQty' => $monthDiscreteQty,
            'monthDecimalString' => $monthDecimalString,
            'totalProjects' => $totalProjects,
            'filteredTotalQuantity' => $filteredTotalQuantity,
            'filteredTotalRecords' => $filteredTotalRecords,
            'consumableCount' => $consumableCount,
            'secondaryCount' => $secondaryCount,
            'catCount' => $catCount,
        ]);
    }
}
