<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CompanyProfile;
use App\Models\FinishGood;
use App\Models\Item;
use App\Models\MaterialOut;
use App\Models\Project;
use App\Models\StockLog;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $currentTab = $request->get('tab', 'finish_goods');
        if (!in_array($currentTab, ['finish_goods', 'cutting', 'vendor', 'welding', 'painting'])) {
            $currentTab = 'finish_goods';
        }

        $stageKey = match($currentTab) {
            'cutting' => 'Cutting',
            'welding' => 'Welding',
            'painting' => 'Painting',
            'vendor' => 'Vendor Eksternal',
            default => null,
        };

        // Tab counts for badge notifications
        $wipCounts = [
            'finish_goods' => FinishGood::count(),
            'cutting' => MaterialOut::where('wip_stage', 'Cutting')->count(),
            'vendor' => MaterialOut::whereIn('wip_stage', ['Vendor Eksternal', 'Vendor'])->count(),
            'welding' => MaterialOut::where('wip_stage', 'Welding')->count(),
            'painting' => MaterialOut::where('wip_stage', 'Painting')->count(),
        ];

        $projects = Project::orderBy('name')->get();
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        $itemsList = Item::select(['id', 'name', 'code_item', 'stock', 'unit'])
            ->where('stock', '>', 0)
            ->orderBy('name')
            ->get();

        $finishGoods = collect();
        $wipRecords = collect();
        $stageStats = [];

        if ($currentTab === 'finish_goods') {
            $query = FinishGood::with(['project', 'creator', 'materialOuts.item', 'materialOuts.user']);

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
        } else {
            $wQuery = MaterialOut::with(['item.category', 'projectRelation', 'user']);
            if ($currentTab === 'vendor') {
                $wQuery->whereIn('wip_stage', ['Vendor Eksternal', 'Vendor']);
            } else {
                $wQuery->where('wip_stage', $stageKey);
            }

            if ($request->filled('search')) {
                $s = trim($request->search);
                $wQuery->where(function ($q) use ($s) {
                    $q->where('reference_number', 'like', "%{$s}%")
                        ->orWhere('pic', 'like', "%{$s}%")
                        ->orWhere('notes', 'like', "%{$s}%")
                        ->orWhereHas('item', function ($iq) use ($s) {
                            $iq->where('name', 'like', "%{$s}%")
                                ->orWhere('code_item', 'like', "%{$s}%");
                        })
                        ->orWhereHas('projectRelation', function ($pq) use ($s) {
                            $pq->where('name', 'like', "%{$s}%")
                                ->orWhere('po_number', 'like', "%{$s}%");
                        });
                });
            }

            if ($request->filled('project_id')) {
                $wQuery->where('project_id', $request->project_id);
            }

            if ($request->filled('date_from')) {
                $wQuery->whereDate('date', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $wQuery->whereDate('date', '<=', $request->date_to);
            }

            $wipRecords = $wQuery->latest('date')->latest('id')->paginate(15)->withQueryString();

            $stageStats = [
                'total_items' => (clone $wQuery)->count(),
                'total_qty' => (clone $wQuery)->sum('quantity'),
            ];
        }

        $stats = [
            'total_items' => FinishGood::count(),
            'in_storage_count' => FinishGood::where('status', 'In Storage')->count(),
            'delivered_count' => FinishGood::where('status', 'Delivered')->count(),
            'qc_passed_count' => FinishGood::where('qc_status', 'Passed')->count(),
            'total_quantity' => FinishGood::sum('quantity'),
            'total_hpp' => FinishGood::sum('total_hpp'),
        ];

        return view('pages.finish-goods.index', compact(
            'finishGoods', 'stats', 'projects', 'units',
            'currentTab', 'stageKey', 'wipCounts', 'wipRecords', 'stageStats', 'itemsList'
        ));
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
            'material_cost' => ['nullable', 'numeric', 'min:0'],
            'auto_calculate_wip' => ['nullable', 'boolean'],
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

        // Kalkulasi Otomatis Biaya Material dari Material Out Proyek
        if ($request->boolean('auto_calculate_wip')) {
            $wipOuts = \App\Models\MaterialOut::with('item')
                ->where('project_id', $validated['project_id'])
                ->whereNull('finish_good_id')
                ->get();

            $calcMaterial = $wipOuts->sum(fn($m) => (float)$m->quantity * (float)($m->item?->unit_price ?? 0));
            $validated['material_cost'] = $calcMaterial;
        }

        $matCost = (float) ($validated['material_cost'] ?? 0);
        $totalHpp = $matCost;
        $qty = (float) $validated['quantity'];

        $validated['material_cost'] = $matCost;
        $validated['manpower_cost'] = 0;
        $validated['total_hpp'] = $totalHpp;
        $validated['hpp_per_unit'] = $qty > 0 ? ($totalHpp / $qty) : 0;
        unset($validated['auto_calculate_wip']);

        $fg = FinishGood::create($validated);

        if ($request->boolean('auto_calculate_wip')) {
            \App\Models\MaterialOut::where('project_id', $validated['project_id'])
                ->whereNull('finish_good_id')
                ->update(['finish_good_id' => $fg->id]);
        }
        $project = Project::find($validated['project_id']);
        $projectName = $project ? $project->name : 'Project';

        ActivityLog::record(
            activity: 'Tambah Barang Jadi',
            description: "Mencatat barang jadi '{$fg->item_name}' ({$fg->quantity} {$fg->unit}) untuk {$projectName}",
            module: 'Finish Good'
        );

        return redirect()->back()->with('success', "Barang Jadi '{$fg->item_name}' berhasil dicatat!");
    }

    public function storeWip(Request $request)
    {
        $validated = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'wip_stage' => ['required', 'in:Cutting,Welding,Painting,Packing,Vendor,Vendor Eksternal'],
            'date' => ['required', 'date'],
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit' => ['required', 'string', 'max:50'],
            'pic' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'project_id.required' => 'Silakan pilih project tujuan.',
            'item_id.required' => 'Silakan pilih material yang digunakan.',
            'quantity.required' => 'Jumlah material wajib diisi.',
            'pic.required' => 'Nama PIC pelaksana wajib diisi.',
        ]);

        $item = Item::findOrFail($validated['item_id']);
        if ((float) $item->stock < (float) $validated['quantity']) {
            return redirect()->back()->with('error', "Stok barang '{$item->name}' tidak mencukupi! Sisa stok saat ini: " . format_quantity($item->stock) . " {$item->unit}.");
        }

        $project = Project::findOrFail($validated['project_id']);

        DB::transaction(function() use ($validated, $item, $project) {
            $refNo = 'OUT-WIP-' . date('Ymd') . '-' . sprintf('%04d', rand(1000, 9999));

            MaterialOut::create([
                'reference_number' => $refNo,
                'date' => $validated['date'],
                'item_id' => $item->id,
                'quantity' => $validated['quantity'],
                'unit' => strtoupper(trim($validated['unit'])),
                'project' => $project->name,
                'project_id' => $project->id,
                'pic' => trim($validated['pic']),
                'wip_stage' => $validated['wip_stage'],
                'notes' => $validated['notes'] ?? "Material WIP Tahap {$validated['wip_stage']} Proyek {$project->name}",
                'user_id' => Auth::id(),
            ]);

            $item->decrement('stock', $validated['quantity']);

            StockLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'recipient_or_provider' => trim($validated['pic']),
                'reference_number' => $refNo,
                'notes' => "Pengeluaran WIP [Tahap: {$validated['wip_stage']}] Proyek {$project->name}",
            ]);
        });

        $formattedQty = format_quantity($validated['quantity']);
        ActivityLog::record(
            activity: 'Catat WIP ' . $validated['wip_stage'],
            description: "Mencatat {$formattedQty} {$validated['unit']} {$item->name} untuk WIP {$validated['wip_stage']} Proyek {$project->name}",
            module: 'WIP & Finish Goods'
        );

        $tab = match(strtolower($validated['wip_stage'])) {
            'cutting' => 'cutting',
            'welding' => 'welding',
            'painting' => 'painting',
            'vendor eksternal', 'vendor' => 'vendor',
            default => 'finish_goods',
        };
        return redirect()->route('finish-goods.index', ['tab' => $tab, 'project_id' => $project->id])
            ->with('success', "Berhasil mencatat material untuk WIP Tahap {$validated['wip_stage']} Proyek {$project->name}! Stok '{$item->name}' otomatis terpotong {$formattedQty} {$validated['unit']}.");
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
            'material_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($validated['status'] === 'Delivered' && empty($validated['delivery_date'])) {
            $validated['delivery_date'] = $finishGood->delivery_date ? $finishGood->delivery_date->toDateString() : now()->toDateString();
        }

        $matCost = isset($validated['material_cost']) ? (float) $validated['material_cost'] : (float) $finishGood->material_cost;
        $totalHpp = $matCost;
        $qty = (float) ($validated['quantity'] ?? $finishGood->quantity);

        $validated['material_cost'] = $matCost;
        $validated['total_hpp'] = $totalHpp;
        $validated['hpp_per_unit'] = $qty > 0 ? ($totalHpp / $qty) : 0;

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
            'H5' => 'BIAYA MATERIAL (RP)',
            'I5' => 'TOTAL HPP (RP)',
            'J5' => 'HPP SATUAN (RP)',
            'K5' => 'TGL SELESAI PRODUKSI',
            'L5' => 'STATUS QC',
            'M5' => 'STATUS PENGIRIMAN',
            'N5' => 'TGL PENGIRIMAN',
            'O5' => 'NO SURAT JALAN / DO',
            'P5' => 'CATATAN',
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
        $sheet->getStyle('A5:P5')->applyFromArray($headerStyle);

        $rowIdx = 6;
        foreach ($records as $index => $item) {
            $sheet->setCellValue('A' . $rowIdx, $index + 1);
            $sheet->setCellValue('B' . $rowIdx, $item->project->name ?? '-');
            $sheet->setCellValue('C' . $rowIdx, $item->project->po_number ?? '-');
            $sheet->setCellValue('D' . $rowIdx, $item->item_name);
            $sheet->setCellValue('E' . $rowIdx, $item->item_code ?? '-');
            $sheet->setCellValue('F' . $rowIdx, (float)$item->quantity);
            $sheet->setCellValue('G' . $rowIdx, $item->unit);
            $sheet->setCellValue('H' . $rowIdx, (float)($item->material_cost ?? 0));
            $sheet->setCellValue('I' . $rowIdx, (float)($item->total_hpp ?? 0));
            $sheet->setCellValue('J' . $rowIdx, (float)($item->hpp_per_unit ?? 0));
            $sheet->setCellValue('K' . $rowIdx, $item->production_date ? $item->production_date->format('d/m/Y') : '-');
            $sheet->setCellValue('L' . $rowIdx, $item->qc_status);
            $sheet->setCellValue('M' . $rowIdx, $item->status === 'Delivered' ? 'Terkirim ke Klien' : 'Tersimpan di Gudang');
            $sheet->setCellValue('N' . $rowIdx, $item->delivery_date ? $item->delivery_date->format('d/m/Y') : '-');
            $sheet->setCellValue('O' . $rowIdx, $item->delivery_order_number ?? '-');
            $sheet->setCellValue('P' . $rowIdx, $item->notes ?? '-');

            $sheet->getStyle("A{$rowIdx}:P{$rowIdx}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("A{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$rowIdx}:J{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$rowIdx}:J{$rowIdx}")->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle("K{$rowIdx}:O{$rowIdx}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowIdx++;
        }

        foreach (range('A', 'P') as $col) {
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
