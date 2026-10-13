<?php

namespace App\Http\Controllers;

use App\Models\CompanyProfile;
use App\Models\DailyStockOpname;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StockOpnameController extends Controller
{
    /**
     * Display the main Stock Opname page.
     */
    public function index()
    {
        return view('pages.stock-opname.index', [
            'title' => 'Stock Opname Harian & Rolling Stock',
        ]);
    }

    /**
     * Export Daily Stock Opname to Excel (.xlsx).
     */
    public function exportExcel(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $shift = $request->get('shift', 'Shift Harian');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $isRecap = $request->get('mode') === 'recap';

        $query = DailyStockOpname::with(['item.category', 'countedByUser', 'closedByUser']);

        if ($isRecap) {
            if ($startDate) $query->whereDate('date', '>=', $startDate);
            if ($endDate) $query->whereDate('date', '<=', $endDate);
            $filename = "Rekap_Stock_Opname_{$startDate}_sd_{$endDate}.xlsx";
            $titleSheet = "REKAP STOCK OPNAME HARIAN";
            $subTitle = "Periode: " . Carbon::parse($startDate)->translatedFormat('d M Y') . " s/d " . Carbon::parse($endDate)->translatedFormat('d M Y');
        } else {
            $query->where('date', $date);
            $filename = "Stock_Opname_Harian_{$date}.xlsx";
            $titleSheet = "STOCK OPNAME HARIAN GUDANG";
            $subTitle = "Tanggal: " . Carbon::parse($date)->translatedFormat('l, d F Y');
        }

        $records = $query->orderBy('date', 'desc')->orderBy('item_id', 'asc')->get();
        $companyProfile = CompanyProfile::getProfile();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stock Opname');

        // Header Perusahaan
        $sheet->setCellValue('A1', strtoupper($companyProfile->name ?? 'PT MUDALAYA ENERGY INDONESIA'));
        $sheet->setCellValue('A2', $titleSheet);
        $sheet->setCellValue('A3', $subTitle);

        $sheet->getStyle('A1:A3')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(14);
        $sheet->getStyle('A2')->getFont()->setSize(12);

        // Header Tabel Kolom
        $headers = [
            'A5' => 'NO',
            'B5' => 'TANGGAL',
            'C5' => 'KODE ITEM',
            'D5' => 'NAMA BARANG',
            'E5' => 'KATEGORI',
            'F5' => 'SATUAN',
            'G5' => 'STOK AWAL',
            'H5' => 'STOK AKHIR (SORE)',
            'I5' => 'TOTAL TERPAKAI',
            'J5' => 'MUTASI SISTEM',
            'K5' => 'SELISIH',
            'L5' => 'STATUS SINKRONISASI',
            'M5' => 'PETUGAS',
            'N5' => 'CATATAN',
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Style Header Tabel
        $headerRange = 'A5:N5';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '275538'], // Brand Dark Green
            ],
        ]);
        $sheet->getRowDimension(5)->setRowHeight(25);

        // Populate Data Rows
        $row = 6;
        foreach ($records as $index => $item) {
            $statusLabel = match($item->status) {
                'synced' => 'Tersinkronisasi (Master)',
                'completed' => 'Selesai (Sesuai)',
                'discrepancy' => 'Ada Selisih',
                default => 'Pending Sore',
            };

            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $item->date ? $item->date->format('Y-m-d') : '-');
            $sheet->setCellValue('C' . $row, $item->item->code_item ?? '-');
            $sheet->setCellValue('D' . $row, $item->item->name ?? '-');
            $sheet->setCellValue('E' . $row, $item->item->category->name ?? 'General');
            $sheet->setCellValue('F' . $row, $item->item->unit ?? 'PCS');
            $sheet->setCellValue('G' . $row, (float)$item->initial_stock);
            $sheet->setCellValue('H' . $row, $item->final_stock !== null ? (float)$item->final_stock : '-');
            $sheet->setCellValue('I' . $row, (float)$item->used_qty);
            $sheet->setCellValue('J' . $row, (float)$item->system_out_qty);
            $sheet->setCellValue('K' . $row, (float)$item->difference);
            $sheet->setCellValue('L' . $row, $statusLabel);
            $sheet->setCellValue('M' . $row, $item->countedByUser->name ?? '-');
            $sheet->setCellValue('N' . $row, $item->notes ?? '-');

            // Alignment
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row . ':K' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('L' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Summary Total Row
        $lastDataRow = $row - 1;
        $sheet->setCellValue('A' . $row, 'TOTAL PEMAKAIAN');
        $sheet->mergeCells('A' . $row . ':F' . $row);
        $sheet->setCellValue('I' . $row, "=SUM(I6:I{$lastDataRow})");
        $sheet->setCellValue('J' . $row, "=SUM(J6:J{$lastDataRow})");
        $sheet->setCellValue('K' . $row, "=SUM(K6:K{$lastDataRow})");

        $sheet->getStyle('A' . $row . ':N' . $row)->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F3F4F6'],
            ],
        ]);
        $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Border Table
        $tableRange = 'A5:N' . $row;
        $sheet->getStyle($tableRange)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'D1D5DB'],
                ],
            ],
        ]);

        // Auto-fit Column Widths
        foreach (range('A', 'N') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Print View / Formulir Lembar Kerja Fisik Gudang.
     */
    public function print(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        $shift = 'Shift Harian';
        DailyStockOpname::initializeForDate($date, $shift);

        $records = DailyStockOpname::with(['item.category', 'countedByUser', 'closedByUser'])
            ->where('date', $date)
            ->orderBy('item_id', 'asc')
            ->get();

        $companyProfile = CompanyProfile::getProfile();

        return view('pages.stock-opname.print', [
            'date' => $date,
            'shift' => $shift,
            'records' => $records,
            'companyProfile' => $companyProfile,
        ]);
    }
}
