<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\CompanyProfile;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ItemStockExportService
{
    /**
     * Generate structured, beautifully styled Excel spreadsheet for inventory stock.
     */
    public function export(Request $request): StreamedResponse
    {
        $categoryParam = strtolower(trim((string) $request->get('category', '')));
        $categoryId = $request->get('category_id');

        $query = Item::with(['category', 'unitRelation']);
        $categoryLabel = 'Semua Kategori';
        $fileCategorySlug = 'Semua';

        // 1. Resolve Category Filter
        if (!empty($categoryId) && $categoryId !== 'all') {
            $catObj = Category::find($categoryId);
            if ($catObj) {
                if ($catObj->isCatUmbrella()) {
                    $catIds = Category::getCatCategoryIds();
                    $query->whereIn('category_id', $catIds);
                    $categoryLabel = 'Cat (Semua Varian Cat & Thinner)';
                    $fileCategorySlug = 'Cat_Semua';
                } else {
                    $query->where('category_id', $catObj->id);
                    $categoryLabel = $catObj->name;
                    $fileCategorySlug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $catObj->name);
                }
            }
        } elseif (!empty($categoryParam) && $categoryParam !== 'all') {
            if (in_array($categoryParam, ['cat', 'paint', 'cat_semua', 'cat-semua'])) {
                // Umbrella Cat: includes Cat, Primer, Second, Top Coat, Thinner
                $catIds = Category::getCatCategoryIds();
                $query->whereIn('category_id', $catIds);
                $categoryLabel = 'Cat (Semua Varian Cat & Thinner)';
                $fileCategorySlug = 'Cat_Semua';
            } elseif (str_contains($categoryParam, 'consumable')) {
                $cat = Category::where('name', 'like', '%consumable%')->first();
                if ($cat) {
                    $query->where('category_id', $cat->id);
                    $categoryLabel = $cat->name;
                    $fileCategorySlug = 'Consumable';
                }
            } elseif (str_contains($categoryParam, 'secondary')) {
                $cat = Category::where('name', 'like', '%secondary%')->first();
                if ($cat) {
                    $query->where('category_id', $cat->id);
                    $categoryLabel = $cat->name;
                    $fileCategorySlug = 'Secondary_Part';
                }
            } elseif (str_contains($categoryParam, 'gas')) {
                $cat = Category::where('name', 'like', '%gas%')->first();
                if ($cat) {
                    $query->where('category_id', $cat->id);
                    $categoryLabel = $cat->name;
                    $fileCategorySlug = 'Gas';
                }
            } else {
                $cat = Category::where('name', 'like', "%{$categoryParam}%")->first();
                if ($cat) {
                    $query->where('category_id', $cat->id);
                    $categoryLabel = $cat->name;
                    $fileCategorySlug = preg_replace('/[^a-zA-Z0-9_-]/', '_', $cat->name);
                }
            }
        }

        // 1.1 Resolve Storage Filter (STORAGE 1 vs STORAGE 2)
        $storageParam = trim((string) $request->get('storage', ''));
        if (!empty($storageParam)) {
            $query->inStorage($storageParam);
            $cleanStorageName = strtoupper($storageParam) === 'STORAGE 2' ? 'STORAGE 2' : 'STORAGE 1';
            $categoryLabel .= ' (Lokasi: ' . $cleanStorageName . ')';
            $fileCategorySlug = str_replace(' ', '_', $cleanStorageName) . '_' . $fileCategorySlug;
        }

        // Additional Optional Filters
        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code_item', 'like', "%{$s}%")
                    ->orWhere('location', 'like', "%{$s}%");
            });
        }

        if ($request->get('stock_status') === 'low') {
            $query->whereColumn('stock', '<=', 'min_stock');
        } elseif ($request->get('stock_status') === 'empty') {
            $query->where('stock', '<=', 0);
        } elseif ($request->get('stock_status') === 'safe') {
            $query->whereColumn('stock', '>', 'min_stock');
        }

        $items = $query->orderBy('category_id', 'asc')
            ->orderBy('name', 'asc')
            ->get();

        // 2. Setup Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stok Barang');
        $sheet->setShowGridLines(true);

        $company = CompanyProfile::getProfile();
        $companyName = strtoupper($company->name ?? 'PT MUDALAYA ENERGY INDONESIA');
        $userName = auth()->check() ? auth()->user()->name : 'Petugas Gudang';
        $exportTimestamp = Carbon::now()->format('d/m/Y H:i');

        // 3. Document Title & Header Info Block
        $sheet->setCellValue('A1', $companyName);
        $sheet->setCellValue('A2', 'LAPORAN KETERSEDIAAN STOK BARANG GUDANG & INVENTARIS');
        $sheet->setCellValue('A3', 'Kategori: ' . $categoryLabel . '  |  Tanggal Ekspor: ' . $exportTimestamp . ' WIB  |  Total: ' . $items->count() . ' Item  |  Operator: ' . $userName);

        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('275538');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('111827');
        $sheet->getStyle('A3')->getFont()->setSize(9)->getColor()->setRGB('4B5563');

        $sheet->getRowDimension(1)->setRowHeight(22);
        $sheet->getRowDimension(2)->setRowHeight(18);
        $sheet->getRowDimension(3)->setRowHeight(16);
        $sheet->getRowDimension(4)->setRowHeight(10); // Spacer

        // 4. Table Column Headers (Row 5)
        $headers = [
            'A5' => 'NO',
            'B5' => 'KODE BARANG',
            'C5' => 'NAMA BARANG',
            'D5' => 'KATEGORI',
            'E5' => 'LOKASI / RAK',
            'F5' => 'JUMLAH STOK',
            'G5' => 'SATUAN',
            'H5' => 'MIN. STOK',
            'I5' => 'STATUS STOK',
            'J5' => 'TERAKHIR DIPERBARUI',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerRange = 'A5:J5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(10)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('275538');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // 5. Populate Data Rows
        $currentRow = 6;
        $totalStockAccumulation = 0;

        foreach ($items as $index => $item) {
            $stk = (float) $item->stock;
            $minStk = (float) ($item->min_stock ?? 0);
            $totalStockAccumulation += $stk;

            // Determine status label & theme color
            if ($stk <= 0) {
                $statusText = 'Kosong';
                $statusColor = '991B1B'; // Dark Red
                $statusBg = 'FEE2E2';    // Light Red Fill
            } elseif ($stk <= $minStk) {
                $statusText = 'Warning Stok';
                $statusColor = '92400E'; // Dark Amber
                $statusBg = 'FEF3C7';    // Light Amber Fill
            } else {
                $statusText = 'Aman';
                $statusColor = '166534'; // Dark Green
                $statusBg = 'DCFCE7';    // Light Green Fill
            }

            $sheet->setCellValue('A' . $currentRow, $index + 1);
            $sheet->setCellValue('B' . $currentRow, $item->code_item ?: '-');
            $sheet->setCellValue('C' . $currentRow, $item->name);
            $sheet->setCellValue('D' . $currentRow, $item->category?->name ?? 'Uncategorized');
            $sheet->setCellValue('E' . $currentRow, $item->display_location ?: '-');
            $sheet->setCellValue('F' . $currentRow, $stk);
            $sheet->setCellValue('G' . $currentRow, strtoupper($item->unit ?: 'PCS'));
            $sheet->setCellValue('H' . $currentRow, $minStk);
            $sheet->setCellValue('I' . $currentRow, $statusText);
            $sheet->setCellValue('J' . $currentRow, $item->updated_at ? $item->updated_at->format('d/m/Y H:i') : '-');

            // Format numbers cleanly (use #,##0 for integers to prevent trailing commas in Indonesian Excel)
            $stkFormat = (fmod((float)$stk, 1) != 0) ? '#,##0.00' : '#,##0';
            $minStkFormat = (fmod((float)$minStk, 1) != 0) ? '#,##0.00' : '#,##0';

            $sheet->getStyle('F' . $currentRow)->getNumberFormat()->setFormatCode($stkFormat);
            $sheet->getStyle('H' . $currentRow)->getNumberFormat()->setFormatCode($minStkFormat);

            // Alignments
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('D' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('E' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('J' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Bold item name and stock
            $sheet->getStyle('C' . $currentRow)->getFont()->setBold(true);
            $sheet->getStyle('F' . $currentRow)->getFont()->setBold(true);

            // Status Badge Highlight
            $sheet->getStyle('I' . $currentRow)->getFont()->setBold(true)->getColor()->setRGB($statusColor);
            $sheet->getStyle('I' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB($statusBg);

            // Alternating Row Background (Zebra Striping)
            if ($index % 2 === 1) {
                $sheet->getStyle('A' . $currentRow . ':H' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
                $sheet->getStyle('J' . $currentRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F9FAFB');
            }

            $sheet->getRowDimension($currentRow)->setRowHeight(20);
            $currentRow++;
        }

        // 6. Summary Footer Row
        if ($items->count() > 0) {
            $sheet->setCellValue('A' . $currentRow, 'TOTAL BARANG: ' . $items->count() . ' ITEM');
            $sheet->mergeCells('A' . $currentRow . ':E' . $currentRow);
            $sheet->setCellValue('F' . $currentRow, '=SUM(F6:F' . ($currentRow - 1) . ')');
            $sheet->setCellValue('G' . $currentRow, 'TOTAL STOK');
            $sheet->setCellValue('H' . $currentRow, '');
            $sheet->setCellValue('I' . $currentRow, '');
            $sheet->setCellValue('J' . $currentRow, '');

            $summaryRange = 'A' . $currentRow . ':J' . $currentRow;
            $sheet->getStyle($summaryRange)->getFont()->setBold(true)->setSize(10);
            $sheet->getStyle($summaryRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F4F6');
            $sheet->getStyle('A' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('F' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $totalFormat = (fmod((float)$totalStockAccumulation, 1) != 0) ? '#,##0.00' : '#,##0';
            $sheet->getStyle('F' . $currentRow)->getNumberFormat()->setFormatCode($totalFormat);
            $sheet->getStyle('G' . $currentRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Table Borders
            $tableRange = 'A5:J' . $currentRow;
            $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D1D5DB');

            // Double underline on summary bottom
            $sheet->getStyle('A' . $currentRow . ':J' . $currentRow)->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE)->getColor()->setRGB('111827');
        } else {
            // Empty State Row
            $sheet->setCellValue('A6', 'Tidak ada data barang yang sesuai dengan filter kategori "' . $categoryLabel . '".');
            $sheet->mergeCells('A6:J6');
            $sheet->getStyle('A6')->getFont()->setItalic(true)->getColor()->setRGB('6B7280');
            $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A5:J6')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('D1D5DB');
            $sheet->getRowDimension(6)->setRowHeight(30);
        }

        // 7. Auto-fit Column Dimensions
        $columnWidths = [
            'A' => 6,   // NO
            'B' => 16,  // KODE BARANG
            'C' => 38,  // NAMA BARANG
            'D' => 20,  // KATEGORI
            'E' => 18,  // LOKASI / RAK
            'F' => 14,  // JUMLAH STOK
            'G' => 12,  // SATUAN
            'H' => 14,  // MIN. STOK
            'I' => 18,  // STATUS STOK
            'J' => 20,  // TERAKHIR DIPERBARUI
        ];

        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // 8. Record Activity Log
        if (auth()->check()) {
            ActivityLog::record(
                activity: 'Export Excel Stok Barang',
                description: 'Mengunduh laporan ketersediaan stok barang Excel [Filter: ' . $categoryLabel . ', Total: ' . $items->count() . ' item].',
                module: 'Stok Barang'
            );
        }

        // 9. Generate & Stream Download
        $writer = new Xlsx($spreadsheet);
        $fileName = 'Laporan_Stok_Barang_' . $fileCategorySlug . '_' . Carbon::now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
