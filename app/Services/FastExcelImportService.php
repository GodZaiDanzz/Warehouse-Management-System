<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ImportStatus;
use App\Models\Item;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class FastExcelImportService
{
    protected int $importStatusId;
    protected int $userId;

    public function __construct(int $importStatusId, int $userId)
    {
        $this->importStatusId = $importStatusId;
        $this->userId = $userId;
    }

    public function import(string $filePath): int
    {
        @set_time_limit(600);
        @ini_set('memory_limit', '1024M');

        $importStatus = ImportStatus::find($this->importStatusId);

        $reader = IOFactory::createReaderForFile($filePath);
        if (method_exists($reader, 'setReadDataOnly')) {
            $reader->setReadDataOnly(true);
        }

        $sheetNames = $reader->listWorksheetNames($filePath);
        if (empty($sheetNames)) {
            if ($importStatus) {
                $importStatus->update(['status' => 'completed', 'total_rows' => 0]);
            }
            return 0;
        }

        // Smart Target Sheet Selection for Items (prefer 'DATA BARANG', 'ITEMS', 'STOK', 'BARANG')
        $targetSheet = null;
        foreach ($sheetNames as $sName) {
            $sUpper = strtoupper(trim($sName));
            if ($sUpper === 'DATA BARANG' || $sUpper === 'ITEMS' || $sUpper === 'BARANG' || $sUpper === 'STOK') {
                $targetSheet = $sName;
                break;
            }
        }
        if (!$targetSheet) {
            foreach ($sheetNames as $sName) {
                $sUpper = strtoupper(trim($sName));
                if (str_contains($sUpper, 'BARANG') || str_contains($sUpper, 'ITEM') || str_contains($sUpper, 'STOK')) {
                    $targetSheet = $sName;
                    break;
                }
            }
        }
        if (!$targetSheet) {
            $targetSheet = $sheetNames[0];
        }

        $reader->setLoadSheetsOnly($targetSheet);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $categoriesMap = Category::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id])
            ->toArray();

        // Smart Column Index Detection
        $codeCol = null;
        $nameCol = null;
        $catCol = null;
        $stockCol = null;
        $unitCol = null;
        $minStockCol = null;
        $headerRowIndex = null;

        $rowNum = 0;
        foreach ($sheet->getRowIterator(1, 50) as $row) {
            $rowNum++;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cIdx = 0;
            foreach ($cellIterator as $cell) {
                $val = strtoupper(trim((string)$cell->getValue()));
                if (($val === 'KODE BARANG' || $val === 'KODE ITEM' || $val === 'KODE' || $val === 'CODE_ITEM' || $val === 'CODE') && $codeCol === null) {
                    $codeCol = $cIdx;
                    $headerRowIndex = $rowNum;
                }
                if (($val === 'NAMA BARANG' || $val === 'NAMA ITEM' || $val === 'NAMA' || $val === 'NAME') && $nameCol === null) {
                    $nameCol = $cIdx;
                    if ($headerRowIndex === null) $headerRowIndex = $rowNum;
                }
                if (($val === 'KELOMPOK' || $val === 'KATEGORI' || $val === 'CATEGORY') && $catCol === null) {
                    $catCol = $cIdx;
                }
                if (($val === 'PERSEDIAAN' || $val === 'STOCK' || $val === 'STOK' || $val === 'JUMLAH') && $stockCol === null) {
                    $stockCol = $cIdx;
                }
                if (($val === 'SATUAN' || $val === 'UNIT') && $unitCol === null) {
                    $unitCol = $cIdx;
                }
                if (($val === 'MINIMAL STOCK' || $val === 'STOK MINIMUM' || $val === 'MIN_STOCK') && $minStockCol === null) {
                    $minStockCol = $cIdx;
                }
                $cIdx++;
            }
            if ($codeCol !== null && $nameCol !== null && $catCol !== null && $stockCol !== null && $unitCol !== null && $minStockCol !== null) {
                break;
            }
        }

        // Fallbacks
        if ($nameCol === null) $nameCol = ($codeCol !== null && $codeCol === 0) ? 1 : 0;
        if ($catCol === null) $catCol = ($nameCol === 0) ? 1 : 2;
        if ($stockCol === null) $stockCol = ($nameCol === 0) ? 2 : 3;

        $startRow = $headerRowIndex !== null ? ($headerRowIndex + 1) : 1;
        $successful = 0;
        $failed = 0;
        $errors = [];
        $timestamp = now();
        $batchRef = 'IMP-Q-' . date('Ymd-His');
        $validDataRows = 0;
        $emptyStreak = 0;

        foreach ($sheet->getRowIterator($startRow) as $row) {
            $rowNumber = $row->getRowIndex();
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowArray = [];
            foreach ($cellIterator as $cell) {
                $rowArray[] = $cell->getValue();
            }

            $name = trim((string)($rowArray[$nameCol] ?? ''));
            $codeItem = ($codeCol !== null && isset($rowArray[$codeCol])) ? trim((string)$rowArray[$codeCol]) : null;

            // Ignore empty rows, headers or titles
            if (empty($name) || strtoupper($name) === 'NAMA BARANG' || strtoupper($name) === 'NAMA ITEM' || strtoupper($name) === 'NAMA' || strtoupper($name) === 'NAME' || strtoupper($name) === 'MINIMAL STOCK' || strtoupper($name) === 'NO') {
                $emptyStreak++;
                if ($emptyStreak > 50) {
                    break;
                }
                continue;
            }

            $emptyStreak = 0;
            $validDataRows++;

            try {
                DB::transaction(function () use ($rowArray, $codeItem, $name, $catCol, $stockCol, $unitCol, $minStockCol, &$categoriesMap, $batchRef, $timestamp) {
                    $categoryName = trim((string)($rowArray[$catCol] ?? 'CONSUMABLE- UMUM'));
                    if (empty($categoryName)) $categoryName = 'CONSUMABLE- UMUM';
                    $catKey = strtolower($categoryName);

                    if (!isset($categoriesMap[$catKey])) {
                        $cat = Category::create(['name' => $categoryName, 'description' => 'Import Excel']);
                        $categoriesMap[$catKey] = $cat->id;
                    }

                    $cleanNum = function ($val, $default = 0.0) {
                        if ($val === null || $val === '') return (float) $default;
                        $s = trim(str_replace(["\xc2\xa0", ' '], '', (string)$val));
                        if (preg_match('/^\d{1,3}(\.\d{3})+,\d+$/', $s)) {
                            $s = str_replace('.', '', $s);
                            $s = str_replace(',', '.', $s);
                        } elseif (preg_match('/^\d{1,3}(,\d{3})+\.\d+$/', $s)) {
                            $s = str_replace(',', '', $s);
                        } else {
                            $s = str_replace(',', '.', $s);
                        }
                        return is_numeric($s) ? (float)$s : (float)$default;
                    };

                    $rawStock = $rowArray[$stockCol] ?? 0;
                    $stock = max(0, $cleanNum($rawStock, 0));

                    $rawUnit = $unitCol !== null ? strtoupper(trim((string)($rowArray[$unitCol] ?? 'PCS'))) : 'PCS';
                    $unit = !empty($rawUnit) ? $rawUnit : 'PCS';

                    $rawMinStock = $minStockCol !== null ? $rowArray[$minStockCol] ?? 5 : 5;
                    $minStock = max(0, $cleanNum($rawMinStock, 5));

                    $nameClean = trim((string)$name);
                    $nameLower = strtolower($nameClean);
                    $codeItemStr = !empty($codeItem) ? (string)$codeItem : null;

                    $item = null;
                    if (!empty($codeItemStr)) {
                        $item = Item::where('code_item', $codeItemStr)->first();
                    }

                    if (!$item && !empty($nameLower)) {
                        $item = Item::whereRaw('LOWER(TRIM(name)) = ?', [$nameLower])->first();
                    }

                    if ($item) {
                        $item->stock += $stock;
                        $item->category_id = $categoriesMap[$catKey];
                        $item->unit = $unit;
                        $item->min_stock = $minStock;
                        $item->save();
                    } else {
                        $item = Item::create([
                            'code_item' => $codeItemStr,
                            'category_id' => $categoriesMap[$catKey],
                            'name' => $nameClean,
                            'stock' => $stock,
                            'unit' => $unit,
                            'min_stock' => $minStock,
                        ]);
                    }

                    StockLog::create([
                        'item_id' => $item->id,
                        'user_id' => $this->userId,
                        'type' => 'in',
                        'quantity' => $stock > 0 ? $stock : 1,
                        'recipient_or_provider' => 'System Import',
                        'reference_number' => $batchRef,
                        'notes' => 'Import Excel Data Barang',
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                });

                $successful++;
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'code' => $codeItem,
                    'reason' => $e->getMessage()
                ];
            }
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        if ($importStatus) {
            $importStatus->update([
                'total_rows' => $validDataRows,
                'processed_rows' => $validDataRows,
                'successful_rows' => $successful,
                'failed_rows' => $failed,
                'error_logs' => $errors,
                'status' => 'completed'
            ]);
        }

        return $successful;
    }
}
