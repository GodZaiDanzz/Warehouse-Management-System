<?php

namespace App\Services;

use App\Models\CompanyProfile;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use PhpOffice\PhpSpreadsheet\Chart\Chart;
use PhpOffice\PhpSpreadsheet\Chart\DataSeries;
use PhpOffice\PhpSpreadsheet\Chart\DataSeriesValues;
use PhpOffice\PhpSpreadsheet\Chart\Layout;
use PhpOffice\PhpSpreadsheet\Chart\Legend;
use PhpOffice\PhpSpreadsheet\Chart\PlotArea;
use PhpOffice\PhpSpreadsheet\Chart\Title;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MaterialOutExcelExportService
{
    /**
     * Generate and stream download rich formatted Excel with grouped tables,
     * summary conclusion table, and embedded native Excel column chart.
     */
    public function export(
        Collection $records,
        ?string $startDate = null,
        ?string $endDate = null,
        string $module = 'all',
        ?string $customTitle = null
    ): StreamedResponse {
        $spreadsheet = new Spreadsheet();
        $sheetTitle = 'Laporan Material Keluar';
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($sheetTitle, 0, 31));

        // Company Profile
        $company = CompanyProfile::getProfile();
        $companyName = strtoupper($company->name ?? 'PT MUDALAYA ENERGY INDONESIA');

        // Determine Title
        if (!empty($customTitle)) {
            $reportTitle = strtoupper($customTitle);
        } else {
            $reportTitle = match (strtolower(trim($module))) {
                'consumable' => 'LAPORAN PEMAKAIAN MATERIAL CONSUMABLE',
                'secondary part' => 'LAPORAN PENGELUARAN SECONDARY PART GUDANG',
                'cat / painting', 'cat', 'painting' => 'LAPORAN PENGELUARAN CAT GUDANG',
                default => 'LAPORAN PENGELUARAN MATERIAL GUDANG',
            };
        }

        // Calculate Date Range & Days Span
        if (!empty($startDate) && !empty($endDate)) {
            $startCarbon = Carbon::parse($startDate);
            $endCarbon = Carbon::parse($endDate);
        } elseif ($records->isNotEmpty()) {
            $minDate = $records->min('date');
            $maxDate = $records->max('date');
            $startCarbon = $minDate ? Carbon::parse($minDate) : Carbon::now()->subDays(30);
            $endCarbon = $maxDate ? Carbon::parse($maxDate) : Carbon::now();
        } else {
            $startCarbon = Carbon::now()->subDays(30);
            $endCarbon = Carbon::now();
        }

        $daysSpan = max(1, $startCarbon->diffInDays($endCarbon) + 1);
        $startFmt = $startCarbon->translatedFormat('d F Y');
        $endFmt = $endCarbon->translatedFormat('d F Y');
        $periodeStr = "Periode: {$startFmt} s/d {$endFmt} ({$daysSpan} Hari)";

        // Global Title Header (matching user screenshot)
        $sheet->setCellValue('A1', $companyName);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setName('Calibri');

        $sheet->setCellValue('A2', $reportTitle);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12)->setName('Calibri');

        $sheet->setCellValue('A3', $periodeStr);
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10)->setName('Calibri');

        // Styles
        $thinBorderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        $headerStyle = [
            'font' => [
                'bold' => true,
                'size' => 10,
                'name' => 'Calibri',
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        // Row 5: Table Header
        $sheet->setCellValue('A5', 'NO');
        $sheet->setCellValue('B5', 'TANGGAL');
        $sheet->setCellValue('C5', 'NAMA BARANG');
        $sheet->setCellValue('D5', 'JUMLAH');
        $sheet->setCellValue('E5', 'SATUAN');
        $sheet->setCellValue('F5', 'PROJECT');
        $sheet->setCellValue('G5', 'PIC');

        $sheet->getStyle('A5:G5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(24);

        // Group records per Item so each material has its own separate block
        $grouped = $records->groupBy('item_id');

        // Sort items alphabetically
        $sortedItemGroups = $grouped->sortBy(function ($itemLogs) {
            return strtolower($itemLogs->first()->item->name ?? '');
        });

        $currentRow = 7;
        $summaryTableData = [];

        foreach ($sortedItemGroups as $itemId => $itemLogs) {
            $itemObj = $itemLogs->first()->item;
            $itemName = strtoupper($itemObj ? $itemObj->name : ('ITEM #' . $itemId));
            $unit = strtoupper($itemLogs->first()->unit ?? ($itemObj ? $itemObj->unit : 'PCS'));
            $itemTotalQty = $itemLogs->sum('quantity');

            // Subheader Row for Material Block (e.g. KACA PUTIH, KEDOK LAS)
            $sheet->setCellValue('A' . $currentRow, $itemName);
            $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(10)->setName('Calibri');
            $currentRow++;

            $startBlockRow = $currentRow;

            // Sort rows in this block chronologically
            $sortedLogs = $itemLogs->sortBy([
                ['date', 'asc'],
                ['id', 'asc'],
            ]);

            $index = 1;
            foreach ($sortedLogs as $row) {
                $dateStr = $row->date ? Carbon::parse($row->date)->format('d-M-y') : '-';

                $sheet->setCellValue('A' . $currentRow, $index);
                $sheet->setCellValue('B' . $currentRow, $dateStr);
                $sheet->setCellValue('C' . $currentRow, strtoupper($row->item?->name ?? $itemName));
                $sheet->setCellValue('D' . $currentRow, (float) $row->quantity);
                $sheet->setCellValue('E' . $currentRow, strtoupper($row->unit ?? $unit));
                $sheet->setCellValue('F' . $currentRow, $row->project ?? '-');
                $sheet->setCellValue('G' . $currentRow, $row->pic ?? '-');

                $currentRow++;
                $index++;
            }

            $endBlockRow = $currentRow - 1;

            // Apply borders & alignments to material table block in batch (high performance)
            if ($endBlockRow >= $startBlockRow) {
                $sheet->getStyle("A{$startBlockRow}:G{$endBlockRow}")->applyFromArray($thinBorderStyle);
                $sheet->getStyle("A{$startBlockRow}:G{$endBlockRow}")->getFont()->setSize(10)->setName('Calibri');
                $sheet->getStyle("A{$startBlockRow}:B{$endBlockRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("C{$startBlockRow}:C{$endBlockRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("D{$startBlockRow}:E{$endBlockRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle("F{$startBlockRow}:G{$endBlockRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            }

            // Calculate item conclusion metrics
            $topPic = $itemLogs->groupBy('pic')->sortByDesc(fn($g) => $g->sum('quantity'))->keys()->first() ?? '-';
            $topProject = $itemLogs->groupBy('project')->sortByDesc(fn($g) => $g->sum('quantity'))->keys()->first() ?? '-';
            $avgPerDay = round($itemTotalQty / $daysSpan, 1);
            $transCount = $itemLogs->count();

            if ($avgPerDay > 50 || $itemTotalQty > 1000) {
                $keterangan = "Akumulasi Sangat Tinggi ({$transCount}x transaksi). Mayoritas diambil oleh PIC '{$topPic}' untuk Project '{$topProject}'. Disarankan evaluasi & re-order stok.";
            } elseif ($avgPerDay > 10 || $itemTotalQty > 200) {
                $keterangan = "Penggunaan Sedang ({$transCount}x transaksi). Digunakan oleh PIC '{$topPic}' pada Project '{$topProject}'.";
            } else {
                $keterangan = "Penggunaan Normal ({$transCount}x transaksi).";
            }

            $summaryTableData[] = [
                'item_name' => $itemName,
                'total_qty' => (float) $itemTotalQty,
                'unit' => $unit,
                'time_span' => "{$daysSpan} HARI",
                'keterangan' => $keterangan,
            ];

            // 1 blank row spacing before next item block
            $currentRow++;
        }

        // Sort summary table by total_qty descending
        usort($summaryTableData, fn($a, $b) => $b['total_qty'] <=> $a['total_qty']);

        // ====================================
        // KESIMPULAN TABLE AT BOTTOM
        // ====================================
        $currentRow++;
        $sheet->setCellValue('A' . $currentRow, 'KESIMPULAN');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(11)->setName('Calibri');
        $currentRow++;

        $sheet->setCellValue('A' . $currentRow, 'Rata penggunaan / pemakaian consumable');
        $sheet->getStyle('A' . $currentRow)->getFont()->setBold(true)->setSize(10)->setName('Calibri');
        $currentRow++;

        $summaryHeaderRow = $currentRow;
        $sheet->setCellValue('A' . $currentRow, 'ITEM');
        $sheet->setCellValue('B' . $currentRow, 'QTY');
        $sheet->setCellValue('C' . $currentRow, 'SATUAN');
        $sheet->setCellValue('D' . $currentRow, 'WAKTU');
        $sheet->setCellValue('E' . $currentRow, 'KETERANGAN');
        $sheet->mergeCells("E{$currentRow}:G{$currentRow}");

        $sheet->getStyle("A{$currentRow}:G{$currentRow}")->applyFromArray($headerStyle);
        $currentRow++;

        $startSummaryBody = $currentRow;
        foreach ($summaryTableData as $sum) {
            $sheet->setCellValue('A' . $currentRow, $sum['item_name']);
            $sheet->setCellValue('B' . $currentRow, $sum['total_qty']);
            $sheet->setCellValue('C' . $currentRow, $sum['unit']);
            $sheet->setCellValue('D' . $currentRow, $sum['time_span']);
            $sheet->setCellValue('E' . $currentRow, $sum['keterangan']);
            $sheet->mergeCells("E{$currentRow}:G{$currentRow}");

            $currentRow++;
        }
        $endSummaryBody = $currentRow - 1;

        if ($endSummaryBody >= $startSummaryBody) {
            $sheet->getStyle("A{$startSummaryBody}:G{$endSummaryBody}")->getFont()->setSize(9)->setName('Calibri');
            $sheet->getStyle("A{$startSummaryBody}:A{$endSummaryBody}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("B{$startSummaryBody}:D{$endSummaryBody}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("E{$startSummaryBody}:G{$endSummaryBody}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
            $sheet->getStyle("A{$summaryHeaderRow}:G{$endSummaryBody}")->applyFromArray($thinBorderStyle);
            $sheet->getStyle("A{$summaryHeaderRow}:G{$endSummaryBody}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

            // ====================================
            // NATIVE EXCEL BAR CHART GENERATION
            // ====================================
            $itemCount = count($summaryTableData);
            if ($itemCount > 0) {
                // If there are many items, chart the top 15 so bars remain clearly legible
                $chartItemsCount = min(15, $itemCount);
                $endSummaryChartRow = $startSummaryBody + $chartItemsCount - 1;

                // Layout for data labels (numeric values directly above bars)
                $chartLayout = new Layout();
                $chartLayout->setShowVal(true);
                $chartLayout->setDLblPos('outEnd');

                $escapedSheetTitle = str_replace("'", "''", $sheetTitle);
                $categories = [
                    new DataSeriesValues(
                        DataSeriesValues::DATASERIES_TYPE_STRING,
                        "'{$escapedSheetTitle}'!\$A\${$startSummaryBody}:\$A\${$endSummaryChartRow}",
                        null,
                        $chartItemsCount
                    ),
                ];
                $values = [
                    new DataSeriesValues(
                        DataSeriesValues::DATASERIES_TYPE_NUMBER,
                        "'{$escapedSheetTitle}'!\$B\${$startSummaryBody}:\$B\${$endSummaryChartRow}",
                        null,
                        $chartItemsCount
                    ),
                ];
                $values[0]->setLabelLayout($chartLayout);

                $series = new DataSeries(
                    DataSeries::TYPE_BARCHART,
                    DataSeries::GROUPING_STANDARD,
                    range(0, count($values) - 1),
                    [],
                    $categories,
                    $values
                );
                $series->setPlotDirection(DataSeries::DIRECTION_COL); // Vertical column chart

                $plotArea = new PlotArea($chartLayout, [$series]);
                $legend = new Legend(Legend::POSITION_TOP, null, false);
                $title = new Title("GRAFIK AKUMULASI PEMAKAIAN MATERIAL ({$daysSpan} HARI)");

                $chart = new Chart(
                    'chart_material_out',
                    $title,
                    $legend,
                    $plotArea,
                    true,
                    DataSeries::EMPTY_AS_GAP,
                    null,
                    null
                );

                $chartStartRow = $endSummaryBody + 3;
                $chartEndRow = $chartStartRow + 18;

                $chart->setTopLeftPosition('A' . $chartStartRow);
                $chart->setBottomRightPosition('G' . $chartEndRow);

                $sheet->addChart($chart);
            }
        }

        // Set column dimensions matching screenshot
        $sheet->getColumnDimension('A')->setAutoSize(false)->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(14);
        $sheet->getColumnDimension('C')->setWidth(28);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(16);
        $sheet->getColumnDimension('F')->setWidth(42);
        $sheet->getColumnDimension('G')->setWidth(18);

        $cleanFilenameModule = preg_replace('/[^a-zA-Z0-9_-]/', '_', $module);
        $filename = 'Laporan_Material_Keluar_' . ($cleanFilenameModule !== 'all' ? $cleanFilenameModule . '_' : '') . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->setIncludeCharts(true);
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
