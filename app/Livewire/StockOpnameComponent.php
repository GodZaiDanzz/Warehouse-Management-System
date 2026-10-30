<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\DailyStockOpname;
use App\Models\Item;
use App\Models\MaterialOut;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockOpnameComponent extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    // Tab Navigation
    public string $activeTab = 'daily'; // 'daily' or 'recap'

    // TAB 1: Opname Harian States
    public string $selectedDate = '';
    public string $selectedShift = 'Shift Harian';
    public string $search = '';
    public string $categoryId = '';
    public string $unitId = '';
    public string $statusFilter = ''; // '', 'pending', 'completed', 'discrepancy', 'synced'
    public int $dailyPerPage = 25;

    // Form editing states (indexed by opname_id)
    public array $finalStocks = [];
    public array $notes = [];
    public array $initialStocks = [];

    // Modal Bulk Sore
    public bool $showBulkModal = false;
    public array $bulkInputs = [];

    // Modal Review Penyesuaian Stok (Stock Adjustment Approval)
    public bool $showAdjustmentModal = false;
    public ?int $selectedOpnameIdForAdjustment = null;
    public ?DailyStockOpname $selectedOpnameRecord = null;
    public string $adjustmentNotes = '';

    // TAB 2: Rekap Harian States
    public string $recapSearch = '';
    public string $recapStartDate = '';
    public string $recapEndDate = '';
    public string $recapShift = '';
    public string $recapItemId = '';
    public string $recapCategoryId = '';
    public string $recapUnitId = '';
    public string $recapStatus = '';
    public int $recapPerPage = 25;

    public function mount()
    {
        $this->selectedDate = date('Y-m-d');
        $this->selectedShift = 'Shift Harian';
        $this->recapStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->recapEndDate = date('Y-m-d');

        $this->initDailyOpnames();
    }

    public function updatingDailyPerPage(): void
    {
        $this->resetPage('dailyPage');
    }

    public function updatingSearch(): void
    {
        $this->resetPage('dailyPage');
    }

    public function updatingCategoryId(): void
    {
        $this->resetPage('dailyPage');
    }

    public function updatingUnitId(): void
    {
        $this->resetPage('dailyPage');
    }

    public function updatingRecapUnitId(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage('dailyPage');
    }

    public function updatingRecapPerPage(): void
    {
        $this->resetPage();
    }

    public function resetDailyFilters(): void
    {
        $this->search = '';
        $this->categoryId = '';
        $this->unitId = '';
        $this->statusFilter = '';
        $this->resetPage('dailyPage');
    }

    public function resetRecapFilters(): void
    {
        $this->recapSearch = '';
        $this->recapStartDate = Carbon::now()->subDays(30)->format('Y-m-d');
        $this->recapEndDate = date('Y-m-d');
        $this->recapShift = '';
        $this->recapItemId = '';
        $this->recapCategoryId = '';
        $this->recapUnitId = '';
        $this->recapStatus = '';
        $this->resetPage();
    }

    /**
     * Auto-initialize today's stock opname records using rolling stock.
     */
    public function initDailyOpnames(): void
    {
        if (empty($this->selectedDate)) {
            $this->selectedDate = date('Y-m-d');
        }
        $this->selectedShift = 'Shift Harian';

        // Jalankan inisialisasi otomatis rolling stock Harian
        DailyStockOpname::initializeForDate($this->selectedDate, $this->selectedShift);

        // Load input values ke local state array
        $records = DailyStockOpname::where('date', $this->selectedDate)->get();
        foreach ($records as $rec) {
            $this->finalStocks[$rec->id] = $rec->final_stock;
            $this->notes[$rec->id] = $rec->notes ?? '';
            $this->initialStocks[$rec->id] = $rec->initial_stock;
        }
    }

    public function setShift(string $shift): void
    {
        $this->selectedShift = $shift;
        $this->initDailyOpnames();
    }

    /**
     * Saat tanggal dipilih berubah, reload data.
     */
    public function updatedSelectedDate(): void
    {
        $this->initDailyOpnames();
        $this->resetPage();
    }

    public function setDatePreset(string $preset): void
    {
        if ($preset === 'today') {
            $this->selectedDate = date('Y-m-d');
        } elseif ($preset === 'yesterday') {
            $this->selectedDate = Carbon::yesterday()->format('Y-m-d');
        } elseif ($preset === 'tomorrow') {
            $this->selectedDate = Carbon::tomorrow()->format('Y-m-d');
        }

        $this->initDailyOpnames();
    }

    /**
     * Simpan stok akhir per baris (Inline update).
     * Otomatis hitung: Stok Awal - Stok Akhir = Total Terpakai.
     * Otomatis rolling stock per shift: stok akhir ini menjadi stok awal shift berikutnya.
     */
    public function saveRowFinalStock(int $opnameId): void
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Role PPIC hanya memiliki hak akses melihat data.');
            return;
        }

        $opname = DailyStockOpname::findOrFail($opnameId);
        $val = $this->finalStocks[$opnameId] ?? null;

        if ($val === '' || $val === null) {
            $opname->final_stock = null;
        } else {
            $val = max(0, (float) str_replace(',', '.', (string)$val));
            $opname->final_stock = $val;
            $this->finalStocks[$opnameId] = $val;
        }

        $opname->notes = $this->notes[$opnameId] ?? null;
        $opname->counted_by = auth()->id();
        $opname->recalculate()->save();

        // Rolling stock ke shift berikutnya jika stok akhir terisi
        if ($opname->final_stock !== null) {
            DailyStockOpname::rollForward($opname->item_id, $opname->date->format('Y-m-d'), $opname->shift, $opname->final_stock);
        }

        session()->flash('row_saved_' . $opnameId, 'Tersimpan!');
        $this->dispatch('opname-saved', ['id' => $opnameId]);
    }

    /**
     * Koreksi Stok Awal (jika ada penyesuaian awal shift oleh admin/warehouse).
     */
    public function saveRowInitialStock(int $opnameId): void
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Role PPIC hanya memiliki hak akses melihat data.');
            return;
        }

        $opname = DailyStockOpname::findOrFail($opnameId);
        $val = $this->initialStocks[$opnameId] ?? 0;
        $val = max(0, (float) str_replace(',', '.', (string)$val));

        $opname->initial_stock = $val;
        $this->initialStocks[$opnameId] = $val;
        $opname->recalculate()->save();

        session()->flash('row_saved_' . $opnameId, 'Stok awal diperbarui!');
    }

    /**
     * Simpan seluruh input baris sekaligus (Batch save).
     */
    public function saveAllRows(): void
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Role PPIC hanya memiliki hak akses melihat data.');
            return;
        }

        $records = DailyStockOpname::where('date', $this->selectedDate)
            ->where('shift', $this->selectedShift)
            ->get();
        $count = 0;

        DB::transaction(function () use ($records, &$count) {
            foreach ($records as $opname) {
                $val = $this->finalStocks[$opname->id] ?? null;
                $hasChanged = false;

                if ($val !== null && $val !== '') {
                    $val = max(0, (float) str_replace(',', '.', (string)$val));
                    if ($opname->final_stock !== $val) {
                        $opname->final_stock = $val;
                        $hasChanged = true;
                    }
                }

                $noteVal = $this->notes[$opname->id] ?? null;
                if ($opname->notes !== $noteVal) {
                    $opname->notes = $noteVal;
                    $hasChanged = true;
                }

                if ($hasChanged) {
                    $opname->counted_by = auth()->id();
                    $opname->recalculate()->save();

                    if ($opname->final_stock !== null) {
                        DailyStockOpname::rollForward($opname->item_id, $opname->date->format('Y-m-d'), $opname->shift, $opname->final_stock);
                    }
                    $count++;
                }
            }
        });

        $msg = "Berhasil menyimpan {$count} data stok opname {$this->selectedShift}!";
        session()->flash('success', $msg);
        $this->dispatch('swal:success', title: 'Berhasil!', message: $msg);
    }

    /**
     * Helper otomatis: Isi stok akhir dengan estimasi sistem (Stok Awal - Pengeluaran Hari Ini).
     */
    public function autoFillExpectedStock(): void
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Role PPIC hanya memiliki hak akses melihat data.');
            $this->dispatch('swal:error', title: 'Gagal!', message: 'Role PPIC hanya memiliki hak akses melihat data.');
            return;
        }

        $records = DailyStockOpname::where('date', $this->selectedDate)
            ->where('shift', $this->selectedShift)
            ->get();
        $filledCount = 0;

        foreach ($records as $opname) {
            if ($opname->final_stock === null) {
                $expected = max(0, (float)$opname->initial_stock - (float)$opname->system_out_qty);
                $this->finalStocks[$opname->id] = $expected;
                $opname->final_stock = $expected;
                $opname->counted_by = auth()->id();
                $opname->recalculate()->save();

                DailyStockOpname::rollForward($opname->item_id, $opname->date->format('Y-m-d'), $opname->shift, $expected);
                $filledCount++;
            }
        }

        $msg = "Otomatis mengisi {$filledCount} item ({$this->selectedShift}) sesuai estimasi sistem. Silakan sesuaikan fisik riil.";
        session()->flash('success', $msg);
        $this->dispatch('swal:success', title: 'Berhasil!', message: $msg);
    }

    /**
     * Tutup Buku & Sinkronkan ke Master Stok Barang WMS.
     */
    public function closeBookAndSync(): void
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Role PPIC hanya memiliki hak akses melihat data.');
            $this->dispatch('swal:error', title: 'Gagal!', message: 'Role PPIC hanya memiliki hak akses melihat data.');
            return;
        }

        $records = DailyStockOpname::where('date', $this->selectedDate)
            ->where('shift', $this->selectedShift)
            ->whereNotNull('final_stock')
            ->get();

        if ($records->isEmpty()) {
            $msg = "Belum ada item yang diinput stok akhirnya untuk {$this->selectedShift} tanggal ini.";
            session()->flash('error', $msg);
            $this->dispatch('swal:error', title: 'Gagal!', message: $msg);
            return;
        }

        DB::transaction(function () use ($records) {
            $userId = auth()->id();
            $now = Carbon::now();

            foreach ($records as $opname) {
                // Update master stok barang sesuai stok fisik akhir shift
                Item::where('id', $opname->item_id)->update([
                    'stock' => $opname->final_stock,
                ]);

                // Update status opname menjadi 'synced'
                $opname->update([
                    'status' => 'synced',
                    'closed_by' => $userId,
                    'closed_at' => $now,
                ]);

                // Pastikan rolling stock ke shift berikutnya terupdate
                DailyStockOpname::rollForward($opname->item_id, $opname->date->format('Y-m-d'), $opname->shift, $opname->final_stock);
            }

            ActivityLog::record(
                activity: 'Tutup Buku Stock Opname ' . $this->selectedShift,
                description: "Menutup buku dan menyinkronkan {$records->count()} item ke master stok untuk {$this->selectedShift} tanggal {$this->selectedDate}",
                module: 'Stock Opname'
            );
        });

        $msg = "Tutup buku {$this->selectedShift} berhasil! Stok {$records->count()} barang telah disinkronkan ke Master WMS.";
        session()->flash('success', $msg);
        $this->dispatch('swal:success', title: 'Berhasil!', message: $msg);
    }

    /**
     * Buka modal review penyesuaian stok opname fisik (Web Administrator only).
     */
    public function openAdjustmentModal(int $opnameId): void
    {
        $user = auth()->user();
        if (!$user || !$user->isWebAdmin()) {
            session()->flash('error', 'Akses Ditolak: Hanya Web Administrator yang berwenang menyetujui penyesuaian master stok.');
            $this->dispatch('swal:error', title: 'Akses Ditolak!', message: 'Hanya Web Administrator yang berwenang menyetujui penyesuaian master stok.');
            return;
        }

        $this->selectedOpnameIdForAdjustment = $opnameId;
        $this->selectedOpnameRecord = DailyStockOpname::with('item.category')->findOrFail($opnameId);
        $this->adjustmentNotes = $this->selectedOpnameRecord->adjustment_notes ?? $this->selectedOpnameRecord->notes ?? '';
        $this->showAdjustmentModal = true;
    }

    /**
     * Tutup modal review penyesuaian stok.
     */
    public function closeAdjustmentModal(): void
    {
        $this->showAdjustmentModal = false;
        $this->selectedOpnameIdForAdjustment = null;
        $this->selectedOpnameRecord = null;
        $this->adjustmentNotes = '';
    }

    /**
     * Setujui & Terapkan penyesuaian stok master berdasarkan hasil opname fisik.
     */
    public function approveAdjustment(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isWebAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Web Administrator yang berwenang menyetujui penyesuaian master stok.');
        }

        if (!$this->selectedOpnameIdForAdjustment) {
            return;
        }

        $opname = DailyStockOpname::with('item')->findOrFail($this->selectedOpnameIdForAdjustment);
        $item = $opname->item;

        if (!$item) {
            session()->flash('error', 'Barang tidak ditemukan.');
            return;
        }

        if ($opname->final_stock === null) {
            session()->flash('error', 'Stok fisik akhir belum diisi, tidak dapat melakukan penyesuaian stok.');
            $this->dispatch('swal:error', title: 'Gagal!', message: 'Stok fisik akhir belum diisi.');
            return;
        }

        DB::transaction(function () use ($opname, $item, $user) {
            $diff = (float)$opname->difference;
            $oldMasterStock = $item->stock;
            $newMasterStock = $opname->final_stock;

            // 1. Update Master Stock barang
            $item->update(['stock' => $newMasterStock]);

            // 2. Generate Reference Number & Catat riwayat audit di StockLog
            $refNo = 'ADJ-OPN-' . $opname->date->format('Ymd') . '-' . sprintf('%04d', $opname->id);
            $formattedDiff = $diff > 0 ? ('+' . format_quantity($diff)) : format_quantity($diff);
            $logNotes = trim($this->adjustmentNotes) ?: ("Penyesuaian selisih fisik Stok Opname ({$formattedDiff} {$item->unit})");

            \App\Models\StockLog::create([
                'reference_number' => $refNo,
                'item_id' => $item->id,
                'type' => 'adjustment',
                'quantity' => abs($diff),
                'recipient_or_provider' => 'Penyesuaian Opname Fisik',
                'notes' => $logNotes,
                'user_id' => $user->id,
            ]);

            // 3. Update status DailyStockOpname
            $opname->update([
                'status' => 'synced',
                'adjustment_status' => 'approved',
                'adjusted_by' => $user->id,
                'adjusted_at' => now(),
                'adjustment_notes' => trim($this->adjustmentNotes) ?: 'Disetujui oleh Web Administrator',
                'closed_by' => $user->id,
                'closed_at' => now(),
            ]);

            // Pastikan rolling stock ke shift berikutnya terupdate
            DailyStockOpname::rollForward($opname->item_id, $opname->date->format('Y-m-d'), $opname->shift, $newMasterStock);

            // 4. Catat ActivityLog
            ActivityLog::record(
                activity: 'Approval Penyesuaian Stok Opname',
                description: "Web Administrator ({$user->name}) menyetujui penyesuaian master stok '{$item->name}' dari {$oldMasterStock} menjadi {$newMasterStock} {$item->unit} (Selisih: {$diff}). Ref: {$refNo}.",
                module: 'Stock Opname'
            );
        });

        $this->closeAdjustmentModal();
        session()->flash('success', "Penyesuaian stok master '{$item->name}' berhasil disetujui & disinkronkan ke {$opname->final_stock} {$item->unit}!");
        $this->dispatch('swal:success', title: 'Berhasil!', message: "Stok master berhasil disesuaikan!");
    }

    /**
     * Tolak penyesuaian stok dan minta petugas gudang untuk menghitung ulang fisik.
     */
    public function rejectAdjustment(): void
    {
        $user = auth()->user();
        if (!$user || !$user->isWebAdmin()) {
            abort(403, 'Akses Ditolak: Hanya Web Administrator yang berwenang menolak penyesuaian stok.');
        }

        if (!$this->selectedOpnameIdForAdjustment) {
            return;
        }

        $opname = DailyStockOpname::with('item')->findOrFail($this->selectedOpnameIdForAdjustment);
        $itemName = $opname->item->name ?? 'Barang';

        $notes = trim($this->adjustmentNotes) ?: 'Ditolak: Perlu verifikasi dan hitung ulang fisik oleh tim gudang.';

        $opname->update([
            'adjustment_status' => 'rejected',
            'adjusted_by' => $user->id,
            'adjusted_at' => now(),
            'adjustment_notes' => $notes,
        ]);

        ActivityLog::record(
            activity: 'Penolakan Penyesuaian Stok Opname',
            description: "Web Administrator ({$user->name}) menolak penyesuaian selisih stok '{$itemName}'. Alasan: {$notes}",
            module: 'Stock Opname'
        );

        $this->closeAdjustmentModal();
        session()->flash('success', "Permintaan penyesuaian stok ditolak. Staf gudang diminta untuk menghitung ulang fisik.");
        $this->dispatch('swal:info', title: 'Ditolak', message: "Penyesuaian stok ditolak. Diminta hitung ulang fisik.");
    }

    // Modal Bulk Input Sore
    public function openBulkModal(): void
    {
        $records = DailyStockOpname::with('item')
            ->where('date', $this->selectedDate)
            ->where('shift', $this->selectedShift)
            ->get();
        $this->bulkInputs = [];
        foreach ($records as $r) {
            $this->bulkInputs[$r->id] = $r->final_stock ?? '';
        }
        $this->showBulkModal = true;
    }

    public function closeBulkModal(): void
    {
        $this->showBulkModal = false;
    }

    public function saveBulkModal(): void
    {
        if (auth()->user() && auth()->user()->isPpic()) {
            session()->flash('error', 'Role PPIC hanya memiliki hak akses melihat data.');
            $this->dispatch('swal:error', title: 'Gagal!', message: 'Role PPIC hanya memiliki hak akses melihat data.');
            return;
        }

        DB::transaction(function () {
            foreach ($this->bulkInputs as $opnameId => $val) {
                if ($val !== '' && $val !== null) {
                    $opname = DailyStockOpname::find($opnameId);
                    if ($opname) {
                        $valFloat = max(0, (float) str_replace(',', '.', (string)$val));
                        $opname->final_stock = $valFloat;
                        $opname->counted_by = auth()->id();
                        $opname->recalculate()->save();

                        DailyStockOpname::rollForward($opname->item_id, $opname->date->format('Y-m-d'), $opname->shift, $valFloat);
                        $this->finalStocks[$opnameId] = $valFloat;
                    }
                }
            }
        });

        $this->closeBulkModal();
        $msg = "Semua data fisik {$this->selectedShift} berhasil diperbarui!";
        session()->flash('success', $msg);
        $this->dispatch('swal:success', title: 'Berhasil!', message: $msg);
    }

    // TAB 2: Rekap Preset Filters
    public function setRecapPreset(string $preset): void
    {
        $today = Carbon::now();
        if ($preset === 'today') {
            $this->recapStartDate = $today->format('Y-m-d');
            $this->recapEndDate = $today->format('Y-m-d');
        } elseif ($preset === '7days') {
            $this->recapStartDate = $today->copy()->subDays(6)->format('Y-m-d');
            $this->recapEndDate = $today->format('Y-m-d');
        } elseif ($preset === '30days') {
            $this->recapStartDate = $today->copy()->subDays(29)->format('Y-m-d');
            $this->recapEndDate = $today->format('Y-m-d');
        } elseif ($preset === 'this_month') {
            $this->recapStartDate = $today->copy()->startOfMonth()->format('Y-m-d');
            $this->recapEndDate = $today->copy()->endOfMonth()->format('Y-m-d');
        }
        $this->resetPage();
    }

    public function render()
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $allItems = Item::select(['id', 'name', 'code_item', 'brand'])->orderBy('name')->get();
        $units = \App\Models\Unit::select(['id', 'name', 'symbol'])->where('is_active', true)->orderBy('name')->get();

        if ($this->activeTab === 'daily') {
            // QUERY TAB 1: OPNAME HARIAN
            $dailyQuery = DailyStockOpname::with(['item.category', 'countedByUser', 'closedByUser'])
                ->where('date', $this->selectedDate);

            if (!empty($this->search)) {
                $s = $this->search;
                $dailyQuery->whereHas('item', function ($q) use ($s) {
                    $q->where('name', 'like', "%{$s}%")
                        ->orWhere('code_item', 'like', "%{$s}%")
                        ->orWhere('brand', 'like', "%{$s}%");
                });
            }

            if (!empty($this->categoryId)) {
                $dailyQuery->whereHas('item', function ($q) {
                    $q->where('category_id', $this->categoryId);
                });
            }

            if (!empty($this->unitId)) {
                $u = $this->unitId;
                $dailyQuery->whereHas('item', function ($q) use ($u) {
                    $q->where('unit_id', $u)->orWhere('unit', $u);
                });
            }

            if (!empty($this->statusFilter)) {
                $dailyQuery->where('status', $this->statusFilter);
            }

            // Metrics untuk Opname Hari Ini dihitung dari seluruh item hasil filter
            $allDailyRecords = (clone $dailyQuery)->get();
            $totalItemsCount = $allDailyRecords->count();
            $completedCount = $allDailyRecords->whereNotNull('final_stock')->count();
            $pendingCount = $totalItemsCount - $completedCount;
            $totalUsedToday = $allDailyRecords->sum('used_qty');
            $totalDifferenceCount = $allDailyRecords->where('difference', '!=', 0)->whereNotNull('final_stock')->count();
            $isAllSynced = $totalItemsCount > 0 && $allDailyRecords->where('status', 'synced')->count() === $totalItemsCount;

            // Paginasi tabel fisik opname harian
            $dailyOpnames = (clone $dailyQuery)->orderBy('id', 'asc')->paginate($this->dailyPerPage, ['*'], 'dailyPage');

            // Placeholder untuk Tab Rekap (menghemat query DB saat tab Harian aktif)
            $recapData = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->recapPerPage, 1);
            $recapTotalUsed = 0;
            $recapTotalRecords = 0;
            $recapSyncedCount = 0;
        } else {
            // QUERY TAB 2: REKAP RIWAYAT PEMAKAIAN PER ITEM
            $recapQuery = DailyStockOpname::with(['item.category', 'countedByUser', 'closedByUser']);

            if (!empty($this->recapStartDate)) {
                $recapQuery->whereDate('date', '>=', $this->recapStartDate);
            }
            if (!empty($this->recapEndDate)) {
                $recapQuery->whereDate('date', '<=', $this->recapEndDate);
            }
            if (!empty($this->recapShift)) {
                $recapQuery->where('shift', $this->recapShift);
            }
            if (!empty($this->recapItemId)) {
                $recapQuery->where('item_id', $this->recapItemId);
            }
            if (!empty($this->recapCategoryId)) {
                $recapQuery->whereHas('item', function ($q) {
                    $q->where('category_id', $this->recapCategoryId);
                });
            }
            if (!empty($this->recapUnitId)) {
                $u = $this->recapUnitId;
                $recapQuery->whereHas('item', function ($q) use ($u) {
                    $q->where('unit_id', $u)->orWhere('unit', $u);
                });
            }
            if (!empty($this->recapStatus)) {
                $recapQuery->where('status', $this->recapStatus);
            }
            if (!empty($this->recapSearch)) {
                $rs = $this->recapSearch;
                $recapQuery->whereHas('item', function ($q) use ($rs) {
                    $q->where('name', 'like', "%{$rs}%")
                        ->orWhere('code_item', 'like', "%{$rs}%");
                });
            }

            // Metrics untuk Rekap
            $recapTotalUsed = (clone $recapQuery)->sum('used_qty');
            $recapTotalRecords = (clone $recapQuery)->count();
            $recapSyncedCount = (clone $recapQuery)->where('status', 'synced')->count();

            $recapData = $recapQuery->orderBy('date', 'desc')->orderBy('shift', 'desc')->orderBy('item_id', 'asc')->paginate($this->recapPerPage);

            // Placeholder untuk Tab Harian (menghemat query DB saat tab Rekap aktif)
            $dailyOpnames = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $this->dailyPerPage, 1, ['pageName' => 'dailyPage']);
            $totalItemsCount = 0;
            $completedCount = 0;
            $pendingCount = 0;
            $totalUsedToday = 0;
            $totalDifferenceCount = 0;
            $isAllSynced = false;
        }

        return view('livewire.stock-opname-component', [
            'companyProfile' => CompanyProfile::getProfile(),
            'categories' => $categories,
            'allItems' => $allItems,
            'units' => $units,
            'dailyOpnames' => $dailyOpnames,
            'selectedShift' => $this->selectedShift,
            'totalItemsCount' => $totalItemsCount,
            'completedCount' => $completedCount,
            'pendingCount' => $pendingCount,
            'totalUsedToday' => $totalUsedToday,
            'totalDifferenceCount' => $totalDifferenceCount,
            'isAllSynced' => $isAllSynced,
            'recapData' => $recapData,
            'recapTotalUsed' => $recapTotalUsed,
            'recapTotalRecords' => $recapTotalRecords,
            'recapSyncedCount' => $recapSyncedCount,
        ]);
    }
}
