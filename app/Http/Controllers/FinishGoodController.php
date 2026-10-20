<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CompanyProfile;
use App\Models\FinishGood;
use App\Models\Project;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FinishGoodController extends Controller
{
    public function index(Request $request)
    {
        $query = FinishGood::with(['project', 'creator']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('item_name', 'like', "%{$s}%")
                    ->orWhere('item_code', 'like', "%{$s}%")
                    ->orWhere('delivery_order_number', 'like', "%{$s}%")
                    ->orWhere('notes', 'like', "%{$s}%")
                    ->orWhereHas('project', function ($pq) use ($s) {
                        $pq->where('name', 'like', "%{$s}%")
                            ->orWhere('po_number', 'like', "%{$s}%")
                            ->orWhere('client', 'like', "%{$s}%");
                    });
            });
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('qc_status')) {
            $query->where('qc_status', $request->qc_status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('production_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('production_date', '<=', $request->date_to);
        }

        $finishGoods = $query->latest('production_date')->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total_items' => FinishGood::count(),
            'in_storage_count' => FinishGood::where('status', 'In Storage')->count(),
            'delivered_count' => FinishGood::where('status', 'Delivered')->count(),
            'qc_passed_count' => FinishGood::where('qc_status', 'Passed')->count(),
            'total_quantity' => FinishGood::sum('quantity'),
        ];

        $projects = Project::orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();

        return view('pages.finish-goods.index', compact('finishGoods', 'stats', 'projects', 'units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'item_code' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', 'string', 'max:50'],
            'production_date' => ['nullable', 'date'],
            'qc_status' => ['required', 'in:Pending,Passed,Rejected'],
            'status' => ['required', 'in:In Storage,Delivered'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_order_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'project_id.required' => 'Project wajib dipilih.',
            'item_name.required' => 'Nama barang jadi wajib diisi.',
            'quantity.required' => 'Jumlah kuantitas wajib diisi.',
            'unit.required' => 'Satuan barang jadi wajib dipilih.',
        ]);

        $validated['created_by'] = Auth::id();
        if (empty($validated['production_date'])) {
            $validated['production_date'] = now()->toDateString();
        }

        if ($validated['status'] === 'Delivered' && empty($validated['delivery_date'])) {
            $validated['delivery_date'] = now()->toDateString();
        }

        $fg = FinishGood::create($validated);
        $project = Project::find($validated['project_id']);
        $projectName = $project ? $project->name : 'Project';

        ActivityLog::record(
            activity: 'Tambah Barang Jadi',
            description: "Mencatat barang jadi '{$fg->item_name}' ({$fg->quantity} {$fg->unit}) untuk {$projectName}",
            module: 'Finish Good'
        );

        return redirect()->back()->with('success', "Barang Jadi '{$fg->item_name}' berhasil dicatat!");
    }

    public function update(Request $request, FinishGood $finishGood)
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'item_name' => ['required', 'string', 'max:255'],
            'item_code' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['required', 'string', 'max:50'],
            'production_date' => ['nullable', 'date'],
            'qc_status' => ['required', 'in:Pending,Passed,Rejected'],
            'status' => ['required', 'in:In Storage,Delivered'],
            'delivery_date' => ['nullable', 'date'],
            'delivery_order_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'Delivered' && empty($validated['delivery_date'])) {
            $validated['delivery_date'] = $finishGood->delivery_date ? $finishGood->delivery_date->toDateString() : now()->toDateString();
        }

        $finishGood->update($validated);

        ActivityLog::record(
            activity: 'Update Barang Jadi',
            description: "Memperbarui data barang jadi '{$finishGood->item_name}'",
            module: 'Finish Good'
        );

        return redirect()->back()->with('success', "Data Barang Jadi '{$finishGood->item_name}' berhasil diperbarui!");
    }

    public function updateDelivery(Request $request, FinishGood $finishGood)
    {
        $validated = $request->validate([
            'delivery_date' => ['required', 'date'],
            'delivery_order_number' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ], [
            'delivery_date.required' => 'Tanggal pengiriman wajib diisi.',
            'delivery_order_number.required' => 'Nomor Surat Jalan / Delivery Order wajib diisi.',
        ]);

        $updateData = [
            'status' => 'Delivered',
            'delivery_date' => $validated['delivery_date'],
            'delivery_order_number' => $validated['delivery_order_number'],
        ];

        if ($request->filled('notes')) {
            $updateData['notes'] = ($finishGood->notes ? $finishGood->notes . "\n" : '') . "DO: " . $validated['notes'];
        }

        $finishGood->update($updateData);

        ActivityLog::record(
            activity: 'Pengiriman Barang Jadi',
            description: "Mencatat pengiriman barang jadi '{$finishGood->item_name}' No Surat Jalan: {$validated['delivery_order_number']}",
            module: 'Finish Good'
        );

        return redirect()->back()->with('success', "Pengiriman barang jadi '{$finishGood->item_name}' berhasil dicatat!");
    }

    public function destroy(FinishGood $finishGood)
    {
        $name = $finishGood->item_name;
        $finishGood->delete();

        ActivityLog::record(
            activity: 'Hapus Barang Jadi',
            description: "Menghapus data barang jadi '{$name}'",
            module: 'Finish Good'
        );

        return redirect()->back()->with('success', "Data Barang Jadi '{$name}' berhasil dihapus!");
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $query = FinishGood::with(['project', 'creator']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('item_name', 'like', "%{$s}%")
                    ->orWhere('item_code', 'like', "%{$s}%")
                    ->orWhere('delivery_order_number', 'like', "%{$s}%")
                    ->orWhereHas('project', function ($pq) use ($s) {
                        $pq->where('name', 'like', "%{$s}%")
                            ->orWhere('po_number', 'like', "%{$s}%");
                    });
            });
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('qc_status')) {
            $query->where('qc_status', $request->qc_status);
        }

        $records = $query->latest('production_date')->latest('id')->get();

        $company = CompanyProfile::getProfile();
        $companyName = strtoupper($company->name ?? 'PT MUDALAYA ENERGY INDONESIA');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Barang Jadi');

        // Header
        $sheet->setCellValue('A1', $companyName);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->setCellValue('A2', 'LAPORAN DATA BARANG JADI (FINISH GOODS)');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(12);
        $sheet->setCellValue('A3', 'Tanggal Unduh: ' . now()->translatedFormat('d F Y H:i'));
        $sheet->getStyle('A3')->getFont()->setItalic(true)->setSize(10);

        // Columns
        $headers = [
            'A5' => 'NO',
            'B5' => 'NAMA PROJECT',
            'C5' => 'NOMOR PO KLIEN',
            'D5' => 'NAMA BARANG JADI',
            'E5' => 'TAG / SPOOL / NO DRAWING',
            'F5' => 'JUMLAH',
            'G5' => 'SATUAN',
            'H5' => 'TGL SELESAI PRODUKSI',
            'I5' => 'STATUS QC',
            'J5' => 'STATUS PENGIRIMAN',
            'K5' => 'TGL PENGIRIMAN',
            'L5' => 'NO SURAT JALAN / DO',
            'M5' => 'CATATAN',
        ];

        foreach ($headers as $cell => $title) {
            $sheet->setCellValue($cell, $title);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ];
        $sheet->getStyle('A5:M5')->applyFromArray($headerStyle);

        $rowIdx = 6;
        foreach ($records as $index => $item) {
            $sheet->setCellValue('A' . $rowIdx, $index + 1);
            $sheet->setCellValue('B' . $rowIdx, $item->project->name ?? '-');
            $sheet->setCellValue('C' . $rowIdx, $item->project->po_number ?? '-');
            $sheet->setCellValue('D' . $rowIdx, $item->item_name);
            $sheet->setCellValue('E' . $rowIdx, $item->item_code ?? '-');
            $sheet->setCellValue('F' . $rowIdx, (float)$item->quantity);
            $sheet->setCellValue('G' . $rowIdx, $item->unit);
            $sheet->setCellValue('H' . $rowIdx, $item->production_date ? $item->production_date->format('d/m/Y') : '-');
            $sheet->setCellValue('I' . $rowIdx, $item->qc_status);
            $sheet->setCellValue('J' . $rowIdx, $item->status === 'Delivered' ? 'Terkirim ke Klien' : 'Tersimpan di Gudang');
            $sheet->setCellValue('K' . $rowIdx, $item->delivery_date ? $item->delivery_date->format('d/m/Y') : '-');
            $sheet->setCellValue('L' . $rowIdx, $item->delivery_order_number ?? '-');
            $sheet->setCellValue('M' . $rowIdx, $item->notes ?? '-');

            $sheet->getStyle("A{$rowIdx}:M{$rowIdx}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$rowIdx}:L{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowIdx++;
        }

        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan_Barang_Jadi_Finish_Goods_' . date('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
