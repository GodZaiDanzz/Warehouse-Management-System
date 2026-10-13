<?php

namespace App\Services;

use App\Models\CompanyProfile;
use App\Models\Item;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PurchaseRequestExportService
{
    /**
     * Generate and stream download Excel Purchase Request (FPBJ) matching Image 2 layout 100%.
     */
    public function export(array $data): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('FPBJ Permintaan Pengadaan');
        $sheet->setShowGridLines(true);

        // Company Profile
        $company = CompanyProfile::getProfile();
        $companyName = strtoupper($company->name ?? 'PT MUDALAYA ENERGY INDONESIA');

        // Document Number & Metadata
        $documentNo = !empty($data['document_no']) ? trim($data['document_no']) : ('095/FPBJ/MGI/MEI/' . Carbon::now()->format('m/Y'));
        $requiredDateRaw = !empty($data['required_date']) ? $data['required_date'] : Carbon::now()->addDays(3)->format('Y-m-d');
        
        // Format Tanggal Diperlukan: "Senin, 14 September 2026"
        Carbon::setLocale('id');
        $requiredDateCarbon = Carbon::parse($requiredDateRaw);
        $requiredDateFmt = $requiredDateCarbon->translatedFormat('l, d F Y');

        $notesGlobal = !empty($data['notes_global']) ? trim($data['notes_global']) : '';
        $warehouseUser = !empty($data['warehouse_user']) ? trim($data['warehouse_user']) : (auth()->user()->name ?? 'Rafi Idzhad Rasyidin');
        $scmManager = !empty($data['scm_manager']) ? trim($data['scm_manager']) : 'Nur Bakti';
        $opsDirector = !empty($data['ops_director']) ? trim($data['ops_director']) : 'Dedi Herdiyana';
        $submissionDateFmt = format_date(Carbon::now());

        // Items payload
        $itemsData = $data['items'] ?? [];

        // Setup Column Widths
        $sheet->getColumnDimension('A')->setWidth(5);   // No
        $sheet->getColumnDimension('B')->setWidth(16);  // Kode Barang
        $sheet->getColumnDimension('C')->setWidth(30);  // Nama Barang/Jasa
        $sheet->getColumnDimension('D')->setWidth(16);  // Data Teknik
        $sheet->getColumnDimension('E')->setWidth(26);  // Tanggal Diperlukan
        $sheet->getColumnDimension('F')->setWidth(10);  // Permintaan Qty
        $sheet->getColumnDimension('G')->setWidth(10);  // Permintaan UoM
        $sheet->getColumnDimension('H')->setWidth(10);  // Data Stock Qty
        $sheet->getColumnDimension('I')->setWidth(10);  // Data Stock UoM
        $sheet->getColumnDimension('J')->setWidth(10);  // Minimum Stock Qty
        $sheet->getColumnDimension('K')->setWidth(10);  // Minimum Stock UoM
        $sheet->getColumnDimension('L')->setWidth(32);  // Keterangan

        // Global Font Setup
        $spreadsheet->getDefaultStyle()->getFont()->setName('Arial')->setSize(9);

        // ---------------------------------------------------------------------
        // ROW 1 to 4: HEADER SECTION (Logos, Title, Doc No, F4 Badge, Notes)
        // ---------------------------------------------------------------------
        
        // Header Row Heights
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(22);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(18);

        // ---------------------------------------------------------------------
        // 2 COMPANY LOGOS IN TOP-LEFT CORNER (Col A & B, Rows 1-4)
        // Matching Company Official Format 100%
        // ---------------------------------------------------------------------
        $logo1Path = !empty($company->logo_path) ? public_path(ltrim($company->logo_path, '/\\')) : null;
        if (!$logo1Path || !file_exists($logo1Path)) {
            $fallback1 = public_path('uploads/company/logo_1789092534.png');
            if (file_exists($fallback1)) {
                $logo1Path = $fallback1;
            }
        }

        $logo2Path = !empty($company->secondary_logo_path) ? public_path(ltrim($company->secondary_logo_path, '/\\')) : null;
        if (!$logo2Path || !file_exists($logo2Path)) {
            $fallback2 = public_path('uploads/company/logo_2_1789373097.png');
            if (file_exists($fallback2)) {
                $logo2Path = $fallback2;
            }
        }

        if ($logo1Path && file_exists($logo1Path)) {
            try {
                $drawing1 = new Drawing();
                $drawing1->setName('Logo Perusahaan 1');
                $drawing1->setDescription('Logo PT Mudalaya Energy Indonesia');
                $drawing1->setPath($logo1Path);
                $drawing1->setCoordinates('A1');
                $drawing1->setHeight(65);
                $drawing1->setOffsetX(4);
                $drawing1->setOffsetY(6);
                $drawing1->setWorksheet($sheet);
            } catch (\Throwable $e) {}
        }

        if ($logo2Path && file_exists($logo2Path)) {
            try {
                $drawing2 = new Drawing();
                $drawing2->setName('Logo Perusahaan 2');
                $drawing2->setDescription('Logo PT Mutiara Global Industry');
                $drawing2->setPath($logo2Path);
                $drawing2->setCoordinates('B1');
                $drawing2->setHeight(65);
                $drawing2->setOffsetX(42);
                $drawing2->setOffsetY(6);
                $drawing2->setWorksheet($sheet);
            } catch (\Throwable $e) {}
        }

        // Title Header (Merged C1:J2)
        $sheet->mergeCells('C1:J1');
        $sheet->setCellValue('C1', 'FORMULIR PERMINTAAN PENGADAAN BARANG & JASA');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setUnderline(true)->setSize(11);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('C2:J2');
        $sheet->setCellValue('C2', 'NOMOR : ' . $documentNo);
        $sheet->getStyle('C2')->getFont()->setBold(true)->setSize(10);
        $sheet->getStyle('C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // Right Header: F4 Badge (Col K) & Note list (Col L)
        $sheet->mergeCells('K1:K3');
        $sheet->setCellValue('K1', "F4");
        $sheet->getStyle('K1')->getFont()->setBold(true)->setSize(22);
        $sheet->getStyle('K1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('K1:K3')->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

        // Note List in L1:L6
        $sheet->setCellValue('L1', 'Note :');
        $sheet->setCellValue('L2', 'F1 untuk PPC');
        $sheet->setCellValue('L3', 'F2 untuk Facility');
        $sheet->setCellValue('L4', 'F3 untuk Logistik');
        $sheet->setCellValue('L5', 'F4 untuk Warehouse');
        $sheet->setCellValue('L6', 'F5 untuk General Affair (GA)');
        $sheet->getStyle('L1')->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle('L2:L6')->getFont()->setSize(7.5);

        // Statement Line (Row 5)
        $sheet->setCellValue('A5', 'Dengan ini mengajukan permohonan untuk pengadaan barang/jasa sebagai berikut :');
        $sheet->getStyle('A5')->getFont()->setItalic(true)->setSize(9);

        // ---------------------------------------------------------------------
        // MAIN TABLE HEADERS (Row 6 & 7)
        // ---------------------------------------------------------------------
        $sheet->getRowDimension(6)->setRowHeight(18);
        $sheet->getRowDimension(7)->setRowHeight(18);

        // Single Column Merged Headers (Row 6-7)
        $sheet->mergeCells('A6:A7');
        $sheet->setCellValue('A6', "No");

        $sheet->mergeCells('B6:B7');
        $sheet->setCellValue('B6', "Kode Barang");

        $sheet->mergeCells('C6:C7');
        $sheet->setCellValue('C6', "Nama Barang/Jasa");

        $sheet->mergeCells('D6:D7');
        $sheet->setCellValue('D6', "Data Teknik");

        $sheet->mergeCells('E6:E7');
        $sheet->setCellValue('E6', "Tanggal Diperlukan");

        // Grouped Header: Permintaan (F6:G6) -> Qty (F7) | UoM (G7)
        $sheet->mergeCells('F6:G6');
        $sheet->setCellValue('F6', "Permintaan");
        $sheet->setCellValue('F7', "Qty");
        $sheet->setCellValue('G7', "UoM");

        // Grouped Header: Data Stock (H6:I6) -> Qty (H7) | UoM (I7)
        $sheet->mergeCells('H6:I6');
        $sheet->setCellValue('H6', "Data Stock");
        $sheet->setCellValue('H7', "Qty");
        $sheet->setCellValue('I7', "UoM");

        // Grouped Header: Minimum Stock (J6:K6) -> Qty (J7) | UoM (K7)
        $sheet->mergeCells('J6:K6');
        $sheet->setCellValue('J6', "Minimum Stock");
        $sheet->setCellValue('J7', "Qty");
        $sheet->setCellValue('K7', "UoM");

        // Single Column Merged Header: Keterangan (L6:L7)
        $sheet->mergeCells('L6:L7');
        $sheet->setCellValue('L6', "Keterangan");

        // Style Main Table Header (Gray Fill + Bold + Centered)
        $tableHeaderRange = 'A6:L7';
        $sheet->getStyle($tableHeaderRange)->getFont()->setBold(true)->setSize(9);
        $sheet->getStyle($tableHeaderRange)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle($tableHeaderRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
        $sheet->getStyle($tableHeaderRange)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ---------------------------------------------------------------------
        // DATA ROWS (Starting Row 8)
        // ---------------------------------------------------------------------
        $startRow = 8;
        $currentRow = $startRow;

        if (!empty($itemsData) && is_array($itemsData)) {
            foreach ($itemsData as $index => $itemRow) {
                $no = $index + 1;
                $itemName = trim($itemRow['name'] ?? '');
                if (empty($itemName)) continue;

                // Lookup or resolve Kode Barang
                $itemCode = trim($itemRow['code_item'] ?? ($itemRow['code'] ?? ''));
                if (empty($itemCode) && !empty($itemRow['id'])) {
                    try {
                        $dbItem = Item::find($itemRow['id']);
                        if ($dbItem && !empty($dbItem->code_item)) {
                            $itemCode = $dbItem->code_item;
                        }
                    } catch (\Throwable $e) {}
                }

                $reqQty = isset($itemRow['request_qty']) ? (float)$itemRow['request_qty'] : 1;
                $reqUom = trim($itemRow['unit'] ?? 'Pcs');
                $stockQty = isset($itemRow['stock']) ? (float)$itemRow['stock'] : 0;
                $minStockQty = isset($itemRow['min_stock']) ? (float)$itemRow['min_stock'] : 0;
                $itemNotes = !empty($itemRow['notes']) ? trim($itemRow['notes']) : 'Digunakan untuk semua project';

                $sheet->getRowDimension($currentRow)->setRowHeight(32);

                $sheet->setCellValue("A{$currentRow}", $no);
                $sheet->setCellValue("B{$currentRow}", $itemCode);
                $sheet->setCellValue("C{$currentRow}", $itemName);
                $sheet->setCellValue("D{$currentRow}", ''); // Data teknik placeholder
                $itemImage = !empty($itemRow['image']) ? $itemRow['image'] : (!empty($itemRow['photo']) ? $itemRow['photo'] : null);
                if ($itemImage && file_exists(public_path(ltrim($itemImage, '/\\')))) {
                    try {
                        $itemDrawing = new Drawing();
                        $itemDrawing->setName('Teknik ' . $no);
                        $itemDrawing->setPath(public_path(ltrim($itemImage, '/\\')));
                        $itemDrawing->setCoordinates("D{$currentRow}");
                        $itemDrawing->setHeight(28);
                        $itemDrawing->setOffsetX(15);
                        $itemDrawing->setOffsetY(2);
                        $itemDrawing->setWorksheet($sheet);
                    } catch (\Throwable $e) {}
                }
                $sheet->setCellValue("E{$currentRow}", $requiredDateFmt);
                $sheet->setCellValue("F{$currentRow}", $reqQty);
                $sheet->setCellValue("G{$currentRow}", $reqUom);
                $sheet->setCellValue("H{$currentRow}", $stockQty);
                $sheet->setCellValue("I{$currentRow}", $reqUom);
                $sheet->setCellValue("J{$currentRow}", $minStockQty);
                $sheet->setCellValue("K{$currentRow}", $reqUom);
                $sheet->setCellValue("L{$currentRow}", $itemNotes);

                // Row Alignments
                $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("E{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("J{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("K{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("L{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

                $currentRow++;
            }
        }

        // Ensure minimum 4 rows for clean table look matching Image
        while ($currentRow < ($startRow + 4)) {
            $no = $currentRow - $startRow + 1;
            $sheet->getRowDimension($currentRow)->setRowHeight(32);
            $sheet->setCellValue("A{$currentRow}", $no);
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $currentRow++;
        }

        $endTableDataRow = $currentRow - 1;
        $sheet->getStyle("A{$startRow}:L{$endTableDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ---------------------------------------------------------------------
        // KETERANGAN BOX (Below Table)
        // ---------------------------------------------------------------------
        $notesStartRow = $currentRow;
        $sheet->getRowDimension($notesStartRow)->setRowHeight(20);
        $sheet->mergeCells("A{$notesStartRow}:L{$notesStartRow}");
        $sheet->setCellValue("A{$notesStartRow}", "Keterangan :");
        $sheet->getStyle("A{$notesStartRow}")->getFont()->setSize(9);

        $notesBodyRow = $notesStartRow + 1;
        $notesBodyEndRow = $notesStartRow + 2;
        $sheet->mergeCells("A{$notesBodyRow}:L{$notesBodyEndRow}");
        $sheet->setCellValue("A{$notesBodyRow}", $notesGlobal);
        $sheet->getStyle("A{$notesBodyRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle("A{$notesStartRow}:L{$notesBodyEndRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);

        // ---------------------------------------------------------------------
        // SIGNATURE SECTION (Grid matching Image)
        // ---------------------------------------------------------------------
        $sigRow1 = $notesBodyEndRow + 1; // "Diajukan Oleh :"
        $sigRow2 = $sigRow1 + 1;         // Subheaders: Warehouse | Manager Produksi/SCM | Catatan Pemeriksa
        $sigRow3 = $sigRow2 + 1;         // Signature space 1
        $sigRow4 = $sigRow3 + 1;         // Signature space 2
        $sigRow5 = $sigRow4 + 1;         // Name row
        $sigRow6 = $sigRow5 + 1;         // Date row

        $sheet->getRowDimension($sigRow1)->setRowHeight(18);
        $sheet->getRowDimension($sigRow2)->setRowHeight(18);
        $sheet->getRowDimension($sigRow3)->setRowHeight(16);
        $sheet->getRowDimension($sigRow4)->setRowHeight(16);
        $sheet->getRowDimension($sigRow5)->setRowHeight(16);
        $sigRow6Val = $sigRow6;
        $sheet->getRowDimension($sigRow6Val)->setRowHeight(16);

        // Section Title 1: Diajukan Oleh :
        $sheet->mergeCells("A{$sigRow1}:L{$sigRow1}");
        $sheet->setCellValue("A{$sigRow1}", "Diajukan Oleh :");
        $sheet->getStyle("A{$sigRow1}")->getFont()->setSize(8.5);

        // Subheaders
        $sheet->mergeCells("A{$sigRow2}:C{$sigRow2}");
        $sheet->setCellValue("A{$sigRow2}", "Warehouse");

        $sheet->mergeCells("D{$sigRow2}:F{$sigRow2}");
        $sheet->setCellValue("D{$sigRow2}", "Manager Produksi / SCM");

        $sheet->mergeCells("G{$sigRow2}:L{$sigRow2}");
        $sheet->setCellValue("G{$sigRow2}", "Catatan Pemeriksa :");

        $sheet->getStyle("A{$sigRow2}:L{$sigRow2}")->getFont()->setBold(true)->setSize(8.5);
        $sheet->getStyle("A{$sigRow2}:F{$sigRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("G{$sigRow2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A{$sigRow2}:L{$sigRow2}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');

        // Merged Signature Spaces
        $sheet->mergeCells("A{$sigRow3}:C{$sigRow4}");
        $sheet->mergeCells("D{$sigRow3}:F{$sigRow4}");
        $sheet->mergeCells("G{$sigRow3}:L{$sigRow6Val}");

        // Names & Dates
        $sheet->mergeCells("A{$sigRow5}:C{$sigRow5}");
        $sheet->setCellValue("A{$sigRow5}", $warehouseUser);

        $sheet->mergeCells("D{$sigRow5}:F{$sigRow5}");
        $sheet->setCellValue("D{$sigRow5}", $scmManager);

        $sheet->mergeCells("A{$sigRow6Val}:C{$sigRow6Val}");
        $sheet->setCellValue("A{$sigRow6Val}", $submissionDateFmt);

        $sheet->mergeCells("D{$sigRow6Val}:F{$sigRow6Val}");
        $sheet->setCellValue("D{$sigRow6Val}", $submissionDateFmt);

        $sheet->getStyle("A{$sigRow5}:F{$sigRow6Val}")->getFont()->setSize(8);
        $sheet->getStyle("A{$sigRow5}:F{$sigRow6Val}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

        // Section Title 2: Disetujui Oleh :
        $sigRow7 = $sigRow6Val + 1;
        $sigRow8 = $sigRow7 + 1; // Direktur Operational | Catatan Persetujuan
        $sigRow9 = $sigRow8 + 1;
        $sigRow10 = $sigRow9 + 1;
        $sigRow11 = $sigRow10 + 1;
        $sigRow12 = $sigRow11 + 1;

        $sheet->getRowDimension($sigRow7)->setRowHeight(18);
        $sheet->getRowDimension($sigRow8)->setRowHeight(18);
        $sheet->getRowDimension($sigRow9)->setRowHeight(16);
        $sheet->getRowDimension($sigRow10)->setRowHeight(16);
        $sheet->getRowDimension($sigRow11)->setRowHeight(16);
        $sheet->getRowDimension($sigRow12)->setRowHeight(16);

        $sheet->mergeCells("A{$sigRow7}:L{$sigRow7}");
        $sheet->setCellValue("A{$sigRow7}", "Disetujui Oleh :");
        $sheet->getStyle("A{$sigRow7}")->getFont()->setSize(8.5);

        $sheet->mergeCells("A{$sigRow8}:C{$sigRow8}");
        $sheet->setCellValue("A{$sigRow8}", "Direktur Operational");

        $sheet->mergeCells("D{$sigRow8}:L{$sigRow8}");
        $sheet->setCellValue("D{$sigRow8}", "Catatan Persetujuan :");

        $sheet->getStyle("A{$sigRow8}:L{$sigRow8}")->getFont()->setBold(true)->setSize(8.5);
        $sheet->getStyle("A{$sigRow8}:C{$sigRow8}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("D{$sigRow8}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A{$sigRow8}:L{$sigRow8}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');

        $sheet->mergeCells("A{$sigRow9}:C{$sigRow10}");
        $sheet->mergeCells("D{$sigRow9}:L{$sigRow12}");

        $sheet->mergeCells("A{$sigRow11}:C{$sigRow11}");
        $sheet->setCellValue("A{$sigRow11}", $opsDirector);

        $sheet->mergeCells("A{$sigRow12}:C{$sigRow12}");
        $sheet->setCellValue("A{$sigRow12}", $submissionDateFmt);

        $sheet->getStyle("A{$sigRow11}:C{$sigRow12}")->getFont()->setSize(8);
        $sheet->getStyle("A{$sigRow11}:C{$sigRow12}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);

        // Apply Borders to Signature Section
        $sheet->getStyle("A{$sigRow2}:L{$sigRow6Val}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$sigRow8}:L{$sigRow12}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Bottom Footer (Lembar ke-1)
        $footerRow = $sigRow12 + 1;
        $sheet->setCellValue("L{$footerRow}", "Lembar ke-1");
        $sheet->getStyle("L{$footerRow}")->getFont()->setBold(true)->setSize(8);
        $sheet->getStyle("L{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Outer Document Border (Double Border / Medium Border matching Image)
        $sheet->getStyle("A1:L{$sigRow12}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

        // Stream output Excel download
        $fileName = 'FPBJ_Permintaan_Pengadaan_' . date('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
