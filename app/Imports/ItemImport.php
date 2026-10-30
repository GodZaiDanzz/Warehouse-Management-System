<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Item;
use App\Models\StockLog;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class ItemImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    protected string $originalFileName;
    protected string $batchRef;
    protected int $importedCount = 0;

    public function __construct(string $originalFileName = 'File_Import.xlsx')
    {
        $this->originalFileName = $originalFileName;
        $this->batchRef = 'IMP-' . date('Ymd-His');
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getBatchRef(): string
    {
        return $this->batchRef;
    }

    public function collection(Collection $rows)
    {
        // Cache existing categories in memory: ['name_lowercase' => id]
        $categoriesMap = Category::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();

        $userId = auth()->id();
        $timestamp = now();

        DB::transaction(function() use ($rows, &$categoriesMap, $userId, $timestamp) {
            foreach ($rows as $row) {
                // Normalize keys into cleaned snake_case strings
                $cleanedRow = [];
                foreach ($row as $key => $value) {
                    $normalizedKey = strtolower(trim(preg_replace('/[^a-zA-Z0-9]/', '_', (string)$key)));
                    $cleanedRow[$normalizedKey] = is_string($value) ? trim($value) : $value;
                }

                $codeItem = $cleanedRow['code_item'] 
                    ?? $cleanedRow['kode_barang'] 
                    ?? $cleanedRow['kode'] 
                    ?? $cleanedRow['code'] 
                    ?? null;

                $name = $cleanedRow['name'] 
                    ?? $cleanedRow['nama_barang'] 
                    ?? $cleanedRow['nama'] 
                    ?? null;

                $brand = $cleanedRow['brand'] 
                    ?? $cleanedRow['merk'] 
                    ?? $cleanedRow['brand_merk'] 
                    ?? null;
                $brandClean = !empty($brand) ? trim((string)$brand) : null;

                if (empty($name)) {
                    continue;
                }

                $nameClean = trim((string)$name);
                $nameLower = strtolower($nameClean);

                // Ignore Excel watermark / footer / metadata rows
                if (preg_match('/(belajar\s*office|tutorial\s*ms\s*office|aplikasi\s*stok\s*barang\s*excel|www\.belajar|visit|link\s*artikel|powerpoint\s*dan\s*acess)/i', $nameLower)) {
                    continue;
                }

                $categoryName = trim((string)($cleanedRow['category'] 
                    ?? $cleanedRow['kategori'] 
                    ?? $cleanedRow['category_name'] 
                    ?? 'General'));

                $catKey = strtolower($categoryName);

                // Find or create Category with memory caching
                if (!isset($categoriesMap[$catKey])) {
                    $category = Category::create([
                        'name' => $categoryName,
                        'description' => 'Disimpan otomatis via Import Excel',
                    ]);
                    $categoriesMap[$catKey] = $category->id;
                }
                $categoryId = $categoriesMap[$catKey];

                $stock = $cleanedRow['stock'] 
                    ?? $cleanedRow['jumlah_stok'] 
                    ?? $cleanedRow['stok'] 
                    ?? 0;

                $unit = $cleanedRow['unit'] 
                    ?? $cleanedRow['satuan'] 
                    ?? 'Pcs';

                $minStock = $cleanedRow['min_stock'] 
                    ?? $cleanedRow['stok_minimum'] 
                    ?? $cleanedRow['min_stok'] 
                    ?? 0;

                $unitPrice = $cleanedRow['unit_price'] 
                    ?? $cleanedRow['harga_satuan'] 
                    ?? $cleanedRow['harga_beli'] 
                    ?? $cleanedRow['harga'] 
                    ?? $cleanedRow['price'] 
                    ?? $cleanedRow['cost'] 
                    ?? null;

                $unitPriceVal = null;
                if ($unitPrice !== null && $unitPrice !== '') {
                    $cleanPriceStr = str_replace(['Rp', 'rp', '.', ' '], '', str_replace(',', '.', (string) $unitPrice));
                    if (is_numeric($cleanPriceStr)) {
                        $unitPriceVal = max(0, (float) $cleanPriceStr);
                    }
                }

                $stockStr = str_replace(',', '.', (string) $stock);
                $importedStock = max(0, (float) $stockStr);

                $minStockStr = str_replace(',', '.', (string) $minStock);
                $minStockVal = max(0, (float) $minStockStr);

                $nameClean = trim((string) $name);
                $nameLower = strtolower($nameClean);
                $codeItemStr = !empty($codeItem) ? (string) $codeItem : null;

                $item = null;
                if (!empty($codeItemStr)) {
                    $item = Item::where('code_item', $codeItemStr)->first();
                }

                if (!$item && !empty($nameLower)) {
                    $item = Item::whereRaw('LOWER(TRIM(name)) = ?', [$nameLower])->first();
                }

                if ($item) {
                    $item->stock += $importedStock;
                    if ($categoryId) $item->category_id = $categoryId;
                    if ($brandClean) $item->brand = $brandClean;
                    if (!empty($unit)) $item->unit = (string) $unit;
                    if ($minStockVal > 0) $item->min_stock = $minStockVal;
                    if ($unitPriceVal !== null) $item->unit_price = $unitPriceVal;
                    $item->save();
                } else {
                    $item = Item::create([
                        'code_item' => $codeItemStr,
                        'category_id' => $categoryId,
                        'name' => $nameClean,
                        'brand' => $brandClean,
                        'stock' => $importedStock,
                        'unit' => (string) $unit,
                        'unit_price' => $unitPriceVal ?? 0,
                        'min_stock' => $minStockVal,
                    ]);
                }

                // Record stock log under Pencatatan Gudang
                StockLog::create([
                    'item_id' => $item->id,
                    'user_id' => $userId,
                    'type' => 'in',
                    'quantity' => $importedStock > 0 ? $importedStock : 1,
                    'recipient_or_provider' => 'Import File Excel',
                    'reference_number' => $this->batchRef,
                    'notes' => 'Import data stok barang "' . $item->name . '" (Kode: ' . $item->code_item . ') via Excel [' . $this->originalFileName . ']',
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);

                $this->importedCount++;
            }
        });
    }
}
