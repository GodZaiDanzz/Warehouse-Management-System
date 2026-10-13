<?php

namespace App\Services;

use App\Models\Category;
use App\Models\ImportStatus;
use App\Models\Item;
use App\Models\MaterialOut;
use App\Models\Project;
use App\Models\StockLog;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class FastMaterialOutImportService
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
        @set_time_limit(1800);
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

        // Smart Target Sheet Selection for Material Out (prefer 'PENGELUARAN', 'MATERIAL KELUAR', 'OUT', 'DATA KELUAR')
        $targetSheet = null;
        foreach ($sheetNames as $sName) {
            $sUpper = strtoupper(trim($sName));
            if ($sUpper === 'PENGELUARAN' || $sUpper === 'MATERIAL KELUAR' || $sUpper === 'MATERIAL OUT' || $sUpper === 'OUT') {
                $targetSheet = $sName;
                break;
            }
        }
        if (!$targetSheet) {
            foreach ($sheetNames as $sName) {
                $sUpper = strtoupper(trim($sName));
                if (str_contains($sUpper, 'PENGELUARAN') || str_contains($sUpper, 'KELUAR') || str_contains($sUpper, 'OUT')) {
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

        // Map items by normalized lower-case name and code_item
        $itemsMapByName = Item::all()->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(preg_replace('/\s+/', ' ', trim($name))) => $id])
            ->toArray();

        $itemsMapByCode = Item::all()->pluck('id', 'code_item')
            ->mapWithKeys(fn($id, $code) => [strtolower(trim($code)) => $id])
            ->toArray();

        $generalCat = Category::firstOrCreate(
            ['name' => 'CONSUMABLE- UMUM'],
            ['description' => 'Kategori Default']
        );

        // Map projects by normalized lower-case name
        $projectsMapByName = Project::all()->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(preg_replace('/\s+/', ' ', trim($name))) => $id])
            ->toArray();

        // Map units by upper-case symbol and name
        $unitsMap = \App\Models\Unit::all()->flatMap(fn($u) => [
            strtoupper(trim($u->symbol)) => $u->id,
            strtoupper(trim($u->name)) => $u->id,
        ])->toArray();

        // Name aliases / synonyms to merge known variants to canonical items
        $itemAliases = [
            'sarung tangan katun b5' => 'sarung tangan katun',
            'sarung tangan' => 'sarung tangan katun',
            'co2' => 'gas co2',
        ];

        // Smart Column Index Detection via streaming row iterator
        $dateCol = null;
        $nameCol = null;
        $qtyCol = null;
        $unitCol = null;
        $projectCol = null;
        $picCol = null;
        $headerRowIndex = null;

        $rowNum = 0;
        foreach ($sheet->getRowIterator(1, 50) as $row) {
            $rowNum++;
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $cIdx = 0;
            foreach ($cellIterator as $cell) {
                $val = strtoupper(trim((string)$cell->getValue()));

                if (($val === 'TANGGAL' || $val === 'DATE' || $val === 'TGL') && $dateCol === null) {
                    $dateCol = $cIdx;
                    $headerRowIndex = $rowNum;
                }
                if (($val === 'NAMA BARANG' || $val === 'NAMA ITEM' || $val === 'BARANG' || $val === 'NAMA') && $nameCol === null) {
                    $nameCol = $cIdx;
                    if ($headerRowIndex === null) $headerRowIndex = $rowNum;
                }
                if (($val === 'JUMLAH' || $val === 'QTY' || $val === 'KUANTITAS' || $val === 'OUT') && $qtyCol === null) {
                    $qtyCol = $cIdx;
                    if ($headerRowIndex === null) $headerRowIndex = $rowNum;
                }
                if (($val === 'SATUAN' || $val === 'UNIT') && $unitCol === null) {
                    $unitCol = $cIdx;
                }
                if (($val === 'PROJECT' || $val === 'LOKASI' || $val === 'PROYEK') && $projectCol === null) {
                    $projectCol = $cIdx;
                }
                if (($val === 'PIC' || $val === 'PPIC' || $val === 'REQUESTOR' || $val === 'PEMINTA' || $val === 'PENERIMA') && $picCol === null) {
                    $picCol = $cIdx;
                }
                $cIdx++;
            }
            if ($nameCol !== null && $qtyCol !== null && $dateCol !== null && $unitCol !== null && $projectCol !== null && $picCol !== null) {
                break;
            }
        }

        // Fallback column indexes if headers not detected
        if ($dateCol === null) $dateCol = 1;
        if ($nameCol === null) $nameCol = 2;
        if ($qtyCol === null) $qtyCol = 3;
        if ($unitCol === null) $unitCol = 4;
        if ($projectCol === null) $projectCol = 5;
        if ($picCol === null) $picCol = 6;

        $startRow = $headerRowIndex !== null ? ($headerRowIndex + 1) : 1;
        $successful = 0;
        $failed = 0;
        $errors = [];
        $timestamp = now();
        $batchRefPrefix = 'IMP-OUT-' . date('Ymd-His');
        $validDataRows = 0;
        $emptyStreak = 0;

        $materialOutBatch = [];
        $stockLogBatch = [];
        $itemStockUpdates = [];

        $flushBatch = function () use (&$materialOutBatch, &$stockLogBatch) {
            if (empty($materialOutBatch)) {
                return;
            }

            DB::transaction(function () use (&$materialOutBatch, &$stockLogBatch) {
                // Chunk into max 250 rows per query to guarantee zero chance of placeholder limit
                // (250 * 12 columns = 3,000 placeholders, well below MySQL limit of 65,535)
                foreach (array_chunk($materialOutBatch, 250) as $chunkOut) {
                    MaterialOut::insert($chunkOut);
                }
                foreach (array_chunk($stockLogBatch, 250) as $chunkStock) {
                    StockLog::insert($chunkStock);
                }
                $materialOutBatch = [];
                $stockLogBatch = [];
            });
        };

        foreach ($sheet->getRowIterator($startRow) as $row) {
            $rowNumber = $row->getRowIndex();
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            $rowArray = [];
            foreach ($cellIterator as $cell) {
                $rowArray[] = $cell->getValue();
            }

            $rawName = trim((string)($rowArray[$nameCol] ?? ''));
            $rawQty = $rowArray[$qtyCol] ?? 0;
            
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
            $qty = max(0, $cleanNum($rawQty, 0));

            // Skip empty rows or header repeats; stop if 50 consecutive empty rows
            if (empty($rawName) || strtoupper($rawName) === 'NAMA BARANG' || strtoupper($rawName) === 'NO' || strtoupper($rawName) === 'TOTAL BARANG KELUAR' || strtoupper($rawName) === 'TOTAL') {
                $emptyStreak++;
                if ($emptyStreak > 50) {
                    break;
                }
                continue;
            }

            if ($qty <= 0) {
                continue;
            }

            $emptyStreak = 0;
            $validDataRows++;

            try {
                $nameClean = preg_replace('/\s+/', ' ', trim($rawName));
                $nameKey = strtolower($nameClean);
                if (isset($itemAliases[$nameKey])) {
                    $nameKey = $itemAliases[$nameKey];
                }

                $itemId = $itemsMapByName[$nameKey] ?? ($itemsMapByCode[$nameKey] ?? null);

                if (!$itemId) {
                    $codeItem = 'ITM-' . strtoupper(substr(md5($nameClean), 0, 5));
                    $unitVal = $unitCol !== null ? strtoupper(trim((string)($rowArray[$unitCol] ?? 'PCS'))) : 'PCS';
                    $unitId = $unitsMap[$unitVal] ?? null;
                    $newItem = Item::create([
                        'category_id' => $generalCat->id,
                        'code_item' => $codeItem,
                        'name' => $nameClean,
                        'stock' => 0,
                        'unit_id' => $unitId,
                        'unit' => !empty($unitVal) ? $unitVal : 'PCS',
                        'min_stock' => 5,
                    ]);
                    $itemId = $newItem->id;
                    $itemsMapByName[$nameKey] = $itemId;
                }

                $rawDate = $rowArray[$dateCol] ?? null;
                $parsedDate = $this->parseImportDate($rawDate);

                $unit = $unitCol !== null ? strtoupper(trim((string)($rowArray[$unitCol] ?? 'PCS'))) : 'PCS';
                if (empty($unit)) $unit = 'PCS';
                $recordUnitId = $unitsMap[strtoupper($unit)] ?? null;

                $projectRaw = $projectCol !== null ? trim((string)($rowArray[$projectCol] ?? 'General Project')) : 'General Project';
                $projectClean = !empty($projectRaw) ? preg_replace('/\s+/', ' ', trim($projectRaw)) : 'General Project';
                if (empty($projectClean)) $projectClean = 'General Project';
                $projectKey = strtolower($projectClean);
                $projectId = $projectsMapByName[$projectKey] ?? null;

                if (!$projectId && !empty($projectClean) && $projectClean !== '-') {
                    $newProj = Project::create([
                        'name' => $projectClean,
                        'status' => 'Ongoing',
                        'created_by' => $this->userId,
                    ]);
                    $projectId = $newProj->id;
                    $projectsMapByName[$projectKey] = $projectId;
                }

                $pic = $picCol !== null ? trim((string)($rowArray[$picCol] ?? 'PIC')) : 'PIC';
                if (empty($pic)) $pic = 'PIC';

                $refNo = $batchRefPrefix . '-' . sprintf('%04d', $rowNumber);

                $outRecord = [
                    'reference_number' => $refNo,
                    'date' => $parsedDate,
                    'item_id' => $itemId,
                    'unit_id' => $recordUnitId,
                    'quantity' => $qty,
                    'unit' => $unit,
                    'project' => $projectClean,
                    'project_id' => $projectId,
                    'pic' => $pic,
                    'notes' => 'Import Excel Material Keluar',
                    'user_id' => $this->userId,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];

                $stockRecord = [
                    'item_id' => $itemId,
                    'user_id' => $this->userId,
                    'type' => 'out',
                    'quantity' => $qty,
                    'recipient_or_provider' => $pic,
                    'reference_number' => $refNo,
                    'notes' => "Import Excel Material Keluar (Project: {$projectClean})",
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];

                // Append to batch arrays only after full preparation succeeds
                $materialOutBatch[] = $outRecord;
                $stockLogBatch[] = $stockRecord;

                if (!isset($itemStockUpdates[$itemId])) {
                    $itemStockUpdates[$itemId] = 0;
                }
                $itemStockUpdates[$itemId] += $qty;

                $successful++;

                if (count($materialOutBatch) >= 500) {
                    $flushBatch();
                }
            } catch (\Exception $e) {
                $failed++;
                $errors[] = [
                    'row' => $rowNumber,
                    'code' => $rawName,
                    'reason' => $e->getMessage()
                ];
            }
        }

        // Flush any remaining records in batches
        $flushBatch();

        // Safely update stocks for modified items in a transaction
        if (!empty($itemStockUpdates)) {
            DB::transaction(function () use ($itemStockUpdates) {
                foreach ($itemStockUpdates as $itemId => $totalQtyOut) {
                    Item::where('id', $itemId)->decrement('stock', $totalQtyOut, ['updated_at' => now()]);
                }
            });
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

    private function parseImportDate($rawDate): string
    {
        if (empty($rawDate)) {
            return date('Y-m-d');
        }

        if (is_numeric($rawDate)) {
            try {
                return ExcelDate::excelToDateTimeObject((float)$rawDate)->format('Y-m-d');
            } catch (\Throwable $e) {
                // proceed
            }
        }

        $str = trim((string)$rawDate);

        if (preg_match('/^(\d{1,2})[\/\.\-](\d{1,2})[\/\.\-](\d{4})$/', $str, $matches)) {
            $day = sprintf('%02d', $matches[1]);
            $month = sprintf('%02d', $matches[2]);
            $year = $matches[3];
            if (checkdate((int)$month, (int)$day, (int)$year)) {
                return "{$year}-{$month}-{$day}";
            }
        }

        $monthsId = [
            'januari' => 'January', 'jan' => 'Jan',
            'februari' => 'February', 'feb' => 'Feb',
            'maret' => 'March', 'mar' => 'Mar',
            'april' => 'April', 'apr' => 'Apr',
            'mei' => 'May',
            'juni' => 'June', 'jun' => 'Jun',
            'juli' => 'July', 'jul' => 'Jul',
            'agustus' => 'August', 'ags' => 'Aug', 'agu' => 'Aug',
            'september' => 'September', 'sep' => 'Sep',
            'oktober' => 'October', 'okt' => 'Oct',
            'november' => 'November', 'nov' => 'Nov',
            'desember' => 'December', 'des' => 'Dec',
        ];

        $strClean = strtr(strtolower($str), $monthsId);

        try {
            return Carbon::parse($strClean)->format('Y-m-d');
        } catch (\Throwable $e) {
            $dTime = strtotime($strClean);
            if ($dTime !== false) {
                return date('Y-m-d', $dTime);
            }
        }

        return date('Y-m-d');
    }
}
