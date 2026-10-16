<?php

namespace App\Http\Controllers;

use App\Exports\ItemExport;
use App\Exports\ItemTemplateExport;
use App\Imports\ItemImport;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Item;
use App\Models\StockLog;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ItemController extends Controller
{
    /**
     * Display listing of inventory items.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->isQc()) {
            return redirect()->route('material-in.index')->with('error', 'Akses dibatasi: Akun QC hanya memiliki izin untuk mengakses Material Masuk.');
        }

        // Master data untuk dropdown modal Tambah Barang
        $categories = Category::select('id', 'name')->orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();

        return view('pages.items.index', [
            'title' => 'Stok Barang Gudang',
            'categories' => $categories,
            'units' => $units,
        ]);
    }

    /**
     * Store a newly created item in database (CREATE).
     */
    public function store(Request $request)
    {
        // Check if category is Cat / Painting
        $selectedCat = Category::find($request->category_id);
        $isCatCategory = $selectedCat && str_contains(strtolower($selectedCat->name), 'cat');

        // 1. Bulk / Multi-Item Creation
        if ($request->has('items') && is_array($request->items)) {
            $rawItems = $request->input('items', []);
            $itemsToInsert = [];

            foreach ($rawItems as $idx => $row) {
                $name = trim($row['name'] ?? '');

                // Only process rows where name is provided
                if ($name !== '') {
                    $stockStr = isset($row['stock']) ? str_replace(',', '.', (string)$row['stock']) : null;
                    $minStockStr = isset($row['min_stock']) ? str_replace(',', '.', (string)$row['min_stock']) : null;
                    $convStr = !empty($row['conversion_factor']) ? str_replace(',', '.', (string)$row['conversion_factor']) : null;
                    $unitPriceStr = isset($row['unit_price']) ? str_replace(['Rp', 'rp', '.', ' '], '', str_replace(',', '.', (string)$row['unit_price'])) : null;

                    $itemsToInsert[] = [
                        'row_num' => $idx + 1,
                        'code_item' => !empty($row['code_item']) ? trim($row['code_item']) : null,
                        'name' => $name,
                        'category_id' => !empty($row['category_id']) ? (int) $row['category_id'] : null,
                        'location' => trim($row['location'] ?? ''),
                        'stock' => ($stockStr !== null && is_numeric($stockStr)) ? max(0, (float) $stockStr) : 0,
                        'unit' => trim($row['unit'] ?? '') ?: 'Pcs',
                        'unit_price' => (auth()->user()?->canViewPrices() && $unitPriceStr !== null && is_numeric($unitPriceStr)) ? max(0, (float) $unitPriceStr) : 0,
                        'min_stock' => ($minStockStr !== null && is_numeric($minStockStr)) ? max(0, (float) $minStockStr) : 20,
                        'manufacturing_date' => !empty($row['manufacturing_date']) ? $row['manufacturing_date'] : null,
                        'expiry_date' => !empty($row['expiry_date']) ? $row['expiry_date'] : null,
                        'wholesale_unit' => !empty($row['wholesale_unit']) ? trim($row['wholesale_unit']) : null,
                        'conversion_factor' => ($convStr !== null && is_numeric($convStr)) ? max(1, (float) $convStr) : 1,
                    ];
                }
            }

            if (empty($itemsToInsert)) {
                return redirect()->back()->withInput()->with('error', 'Mohon isi setidaknya 1 baris data barang baru sebelum menyimpan.');
            }

            // Validation checks across submitted rows
            $errors = [];
            $categoryIds = Category::pluck('id')->toArray();
            $seenNamesInBatch = [];

            foreach ($itemsToInsert as $item) {
                $row = $item['row_num'];
                $cleanName = trim($item['name']);
                $nameLower = strtolower($cleanName);

                if (empty($cleanName)) {
                    $errors[] = "Baris #{$row}: Nama barang wajib diisi.";
                }

                if (empty($item['category_id']) || !in_array($item['category_id'], $categoryIds)) {
                    $errors[] = "Baris #{$row}: Kategori barang wajib dipilih.";
                }

                // Check duplicate within submitted batch
                if (in_array($nameLower, $seenNamesInBatch)) {
                    $errors[] = "Baris #{$row}: Nama barang '{$cleanName}' ganda dalam daftar input massal ini.";
                } else {
                    $seenNamesInBatch[] = $nameLower;
                }

                // Check duplicate in database
                $existsInDb = Item::whereRaw('LOWER(TRIM(name)) = ?', [$nameLower])->exists();
                if ($existsInDb) {
                    $errors[] = "Baris #{$row}: Nama barang '{$cleanName}' sudah terdaftar di sistem. Silakan perbarui stok barang yang sudah ada.";
                }
            }

            if (!empty($errors)) {
                return redirect()->back()->withInput()->with('error', implode('<br>', array_slice($errors, 0, 5)) . (count($errors) > 5 ? '<br>...dan ' . (count($errors) - 5) . ' kesalahan lainnya.' : ''));
            }

            DB::beginTransaction();
            try {
                $createdCount = 0;
                $userId = auth()->id() ?? 1;

                foreach ($itemsToInsert as $data) {
                    $rawUnit = trim($data['unit']);
                    $unitObj = Unit::where('symbol', $rawUnit)->orWhere('name', $rawUnit)->first();

                    $item = Item::create([
                        'code_item' => $data['code_item'],
                        'name' => $data['name'],
                        'category_id' => $data['category_id'],
                        'unit_id' => $unitObj?->id,
                        'location' => $data['location'] ?: null,
                        'stock' => $data['stock'],
                        'unit' => $unitObj ? $unitObj->symbol : $data['unit'],
                        'unit_price' => $data['unit_price'] ?? 0,
                        'min_stock' => $data['min_stock'],
                        'manufacturing_date' => $data['manufacturing_date'],
                        'wholesale_unit' => $data['wholesale_unit'],
                        'conversion_factor' => $data['conversion_factor'],
                    ]);

                    // Log initial stock if stock > 0
                    if ($item->stock > 0) {
                        StockLog::create([
                            'item_id' => $item->id,
                            'user_id' => $userId,
                            'type' => 'in',
                            'quantity' => $item->stock,
                            'recipient_or_provider' => 'Stok Awal Sistem',
                            'notes' => 'Pencatatan stok awal barang baru: ' . $item->name,
                        ]);
                    }

                    $createdCount++;
                }

                DB::commit();

                ActivityLog::record(
                    activity: 'Tambah Barang Massal',
                    description: "Menambahkan {$createdCount} barang baru secara bersamaan melalui input tabel massal.",
                    module: 'Stok Barang'
                );

                return redirect()->route('items.index')->with('success', "Berhasil menambahkan {$createdCount} barang baru sekaligus!");
            } catch (\Exception $e) {
                DB::rollBack();
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan barang massal: ' . $e->getMessage());
            }
        }

        // 2. Single-item Creation Fallback
        foreach (['stock', 'min_stock', 'conversion_factor'] as $field) {
            if ($request->has($field) && $request->input($field) !== null) {
                $request->merge([$field => str_replace(',', '.', (string)$request->input($field))]);
            }
        }

        if ($request->has('unit_price') && $request->input('unit_price') !== null) {
            $cleanPrice = str_replace(['Rp', 'rp', '.', ' '], '', str_replace(',', '.', (string)$request->input('unit_price')));
            $request->merge(['unit_price' => $cleanPrice ?: 0]);
        }

        $rules = [
            'code_item' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'unit_id' => 'required_without:unit|nullable|exists:units,id',
            'unit' => 'required_without:unit_id|nullable|string|max:50',
            'min_stock' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'wholesale_unit' => 'nullable|string|max:50',
            'conversion_factor' => 'nullable|numeric|min:0.001',
            'manufacturing_date' => $isCatCategory ? 'required|date' : 'nullable|date',
            'expiry_date' => 'nullable|date',
        ];

        $messages = [
            'name.required' => 'Nama barang wajib diisi.',
            'category_id.required' => 'Pilih kategori barang.',
            'category_id.exists' => 'Kategori yang dipilih tidak valid.',
            'stock.required' => 'Jumlah stok awal wajib diisi.',
            'unit_id.required_without' => 'Satuan barang wajib dipilih.',
            'unit.required_without' => 'Satuan barang wajib diisi.',
            'min_stock.required' => 'Stok minimum wajib diisi.',
            'manufacturing_date.required' => 'Khusus kategori Cat / Painting, Tanggal Pembuatan (Manufacturing Date) wajib diisi.',
        ];

        $validated = $request->validate($rules, $messages);

        if (!empty($validated['unit_id'])) {
            $unitObj = Unit::find($validated['unit_id']);
            if ($unitObj) {
                $validated['unit'] = $unitObj->symbol;
            }
        } elseif (!empty($validated['unit'])) {
            $unitObj = Unit::where('symbol', $validated['unit'])->orWhere('name', $validated['unit'])->first();
            if ($unitObj) {
                $validated['unit_id'] = $unitObj->id;
                $validated['unit'] = $unitObj->symbol;
            }
        }

        $cleanName = trim($validated['name']);
        $existsInDb = Item::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanName)])->exists();
        if ($existsInDb) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambah barang: Nama barang "' . $cleanName . '" sudah terdaftar di sistem! Silakan gunakan nama lain atau perbarui stok barang yang sudah ada.');
        }

        if (empty($validated['conversion_factor']) || (float)$validated['conversion_factor'] <= 0) {
            $validated['conversion_factor'] = 1;
        }
        if (!auth()->user()?->canViewPrices()) {
            $validated['unit_price'] = 0;
        } else {
            if (!isset($validated['unit_price']) || $validated['unit_price'] === null || $validated['unit_price'] === '') {
                $validated['unit_price'] = 0;
            }
        }
        if (empty($validated['wholesale_unit'])) {
            $validated['wholesale_unit'] = null;
        }

        try {
            $item = Item::create($validated);

            // Log initial stock if stock > 0
            if ($item->stock > 0) {
                StockLog::create([
                    'item_id' => $item->id,
                    'user_id' => auth()->id() ?? 1,
                    'type' => 'in',
                    'quantity' => $item->stock,
                    'recipient_or_provider' => 'Stok Awal Sistem',
                    'notes' => 'Pencatatan stok awal barang baru: ' . $item->name,
                ]);
            }

            ActivityLog::record(
                activity: 'Tambah Barang Baru',
                description: 'Menambahkan barang baru "' . $item->name . '"' . ($item->code_item ? ' (Kode: ' . $item->code_item . ')' : '') . ' dengan stok awal ' . format_quantity($item->stock) . ' ' . $item->unit . '.',
                module: 'Stok Barang'
            );

            return redirect()->route('items.index')->with('success', 'Barang baru "' . $item->name . '" berhasil ditambahkan!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambah barang baru: ' . $e->getMessage());
        }
    }

    /**
     * Update specified item in database (UPDATE).
     */
    public function update(Request $request, Item $item)
    {
        foreach (['stock', 'min_stock', 'conversion_factor'] as $field) {
            if ($request->has($field) && $request->input($field) !== null) {
                $request->merge([$field => str_replace(',', '.', (string)$request->input($field))]);
            }
        }

        if ($request->has('unit_price') && $request->input('unit_price') !== null) {
            $cleanPrice = str_replace(['Rp', 'rp', '.', ' '], '', str_replace(',', '.', (string)$request->input('unit_price')));
            $request->merge(['unit_price' => $cleanPrice ?: 0]);
        }

        $selectedCat = Category::find($request->category_id);
        $isCatCategory = $selectedCat && str_contains(strtolower($selectedCat->name), 'cat');

        $rules = [
            'code_item' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'stock' => 'required|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'unit_id' => 'required_without:unit|nullable|exists:units,id',
            'unit' => 'required_without:unit_id|nullable|string|max:50',
            'min_stock' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'wholesale_unit' => 'nullable|string|max:50',
            'conversion_factor' => 'nullable|numeric|min:0.001',
            'manufacturing_date' => $isCatCategory ? 'required|date' : 'nullable|date',
            'expiry_date' => 'nullable|date',
        ];

        $messages = [
            'name.required' => 'Nama barang wajib diisi.',
            'category_id.required' => 'Pilih kategori barang.',
            'stock.required' => 'Jumlah stok wajib diisi.',
            'unit_id.required_without' => 'Satuan barang wajib dipilih.',
            'unit.required_without' => 'Satuan barang wajib diisi.',
            'min_stock.required' => 'Stok minimum wajib diisi.',
            'manufacturing_date.required' => 'Khusus kategori Cat / Painting, Tanggal Pembuatan (Manufacturing Date) wajib diisi.',
        ];

        $validated = $request->validate($rules, $messages);

        if (!empty($validated['unit_id'])) {
            $unitObj = Unit::find($validated['unit_id']);
            if ($unitObj) {
                $validated['unit'] = $unitObj->symbol;
            }
        } elseif (!empty($validated['unit'])) {
            $unitObj = Unit::where('symbol', $validated['unit'])->orWhere('name', $validated['unit'])->first();
            if ($unitObj) {
                $validated['unit_id'] = $unitObj->id;
                $validated['unit'] = $unitObj->symbol;
            }
        }

        $cleanName = trim($validated['name']);
        $existsInDb = Item::where('id', '!=', $item->id)
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($cleanName)])
            ->exists();
        if ($existsInDb) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui barang: Nama barang "' . $cleanName . '" sudah digunakan oleh barang lain di sistem.');
        }

        if (empty($validated['conversion_factor']) || (float)$validated['conversion_factor'] <= 0) {
            $validated['conversion_factor'] = 1;
        }
        if (!auth()->user()?->canViewPrices()) {
            // Keep existing unit_price untouched so editing item details does not wipe existing price
            unset($validated['unit_price']);
        } else {
            if (!isset($validated['unit_price']) || $validated['unit_price'] === null || $validated['unit_price'] === '') {
                $validated['unit_price'] = 0;
            }
        }
        if (empty($validated['wholesale_unit'])) {
            $validated['wholesale_unit'] = null;
        }

        try {
            $oldStock = $item->stock;
            $item->update($validated);

            // Log stock adjustment if stock changed
            if ($oldStock != $item->stock) {
                $diff = (float)$item->stock - (float)$oldStock;
                StockLog::create([
                    'item_id' => $item->id,
                    'user_id' => auth()->id(),
                    'type' => $diff > 0 ? 'in' : 'out',
                    'quantity' => abs($diff),
                    'recipient_or_provider' => 'Penyesuaian Manual',
                    'notes' => 'Penyesuaian stok dari ' . format_quantity($oldStock) . ' ke ' . format_quantity($item->stock) . '.',
                ]);
            }

            ActivityLog::record(
                activity: 'Perbarui Data Barang',
                description: 'Mengubah data barang "' . $item->name . '"' . ($item->code_item ? ' (Kode: ' . $item->code_item . ')' : '') . '.',
                module: 'Stok Barang'
            );

            return redirect()->route('items.index')->with('success', 'Data barang "' . $item->name . '" berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui barang: ' . $e->getMessage());
        }
    }

    /**
     * Remove specified item from database (DELETE).
     */
    public function destroy(Item $item)
    {
        try {
            $name = $item->name;
            $code = $item->code_item;
            $item->delete();

            ActivityLog::record(
                activity: 'Hapus Barang',
                description: 'Menghapus barang "' . $name . '"' . ($code ? ' (Kode: ' . $code . ')' : '') . ' dari sistem.',
                module: 'Stok Barang'
            );

            return redirect()->route('items.index')->with('success', 'Barang "' . $name . '" berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus barang: ' . $e->getMessage());
        }
    }

    /**
     * Export Inventory Stock to Excel with Category Filtering and structured formatting.
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        if ($user && $user->isQc()) {
            return redirect()->route('material-in.index')->with('error', 'Akses dibatasi: Akun QC hanya memiliki izin untuk mengakses Material Masuk.');
        }

        try {
            $service = new \App\Services\ItemStockExportService();
            return $service->export($request);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengekspor data stok: ' . $e->getMessage());
        }
    }

    /**
     * Download Excel template format for importing data.
     */
    public function downloadTemplate()
    {
        try {
            return Excel::download(new ItemTemplateExport, 'Template_Import_Stok_Barang.xlsx');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengunduh template Excel: ' . $e->getMessage());
        }
    }

    /**
     * Import items list from Excel file.
     */
    public function import(Request $request)
    {
        // Increase maximum execution time and memory for processing large Excel files
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ], [
            'file.required' => 'Silakan pilih file Excel terlebih dahulu.',
            'file.file' => 'File yang diunggah tidak valid.',
            'file.mimes' => 'Format file harus berupa .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file tidak boleh melebihi 10 MB.',
        ]);

        try {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $importer = new ItemImport($originalName);

            Excel::import($importer, $file);

            ActivityLog::record(
                activity: 'Import Excel Stok Barang',
                description: 'Meng-import ' . $importer->getImportedCount() . ' item barang ke stok gudang dari file Excel (' . $originalName . ') [No. Ref: ' . $importer->getBatchRef() . ']. Log mutasi stok otomatis tercatat di Pencatatan Gudang.',
                module: 'Stok Barang'
            );

            return redirect()->back()->with('success', 'Data barang berhasil di-import (' . $importer->getImportedCount() . ' item) dan log mutasi otomatis tercatat di Pencatatan Gudang!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal meng-import data: ' . $e->getMessage());
        }
    }

    /**
     * Export Purchase Request (FPBJ) to Excel format matching Image 2.
     */
    public function exportPurchaseRequest(Request $request)
    {
        if ($request->isMethod('get') && !$request->has('items')) {
            return redirect()->route('items.index');
        }
        $request->validate([
            'document_no' => 'nullable|string|max:100',
            'required_date' => 'required|date',
            'notes_global' => 'nullable|string|max:1000',
            'warehouse_user' => 'nullable|string|max:100',
            'scm_manager' => 'nullable|string|max:100',
            'ops_director' => 'nullable|string|max:100',
            'items' => 'required|array|min:1',
            'items.*.code_item' => 'nullable|string',
            'items.*.name' => 'required|string',
            'items.*.request_qty' => 'required|numeric|min:0.001',
            'items.*.unit' => 'required|string',
            'items.*.stock' => 'required|numeric|min:0',
            'items.*.min_stock' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        try {
            $service = new \App\Services\PurchaseRequestExportService();

            ActivityLog::record(
                activity: 'Export FPBJ (Purchase Request)',
                description: 'Mengunduh Formulir Permintaan Pengadaan Barang & Jasa (FPBJ) Excel [No: ' . ($request->document_no ?: 'Auto') . '].',
                module: 'Stok Barang'
            );

            return $service->export($request->all());
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat file FPBJ Excel: ' . $e->getMessage());
        }
    }

}
