<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class StockLogController extends Controller
{
    /**
     * Display listing of warehouse stock movement logs (Pencatatan Gudang).
     */
    public function index(Request $request)
    {
        $query = StockLog::with(['item', 'user']);

        // Filter by Transaction Type (in/out)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Search Keyword (Item name, code, recipient/provider, ref number, notes)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('item', function($iq) use ($search) {
                    $iq->where('name', 'like', "%{$search}%")
                       ->orWhere('code_item', 'like', "%{$search}%");
                })
                ->orWhere('recipient_or_provider', 'like', "%{$search}%")
                ->orWhere('reference_number', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter by Date Range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $perPage = (int) $request->get('per_page', 15);
        if ($perPage <= 0) $perPage = 15;
        $logs = $query->latest()->paginate($perPage)->withQueryString();
        $items = Item::orderBy('name')->get();

        // Calculate Summary Metrics
        $totalLogsCount = StockLog::count();
        $totalInQty = StockLog::where('type', 'in')->sum('quantity');
        $totalOutQty = StockLog::where('type', 'out')->sum('quantity');

        return view('pages.logs.index', [
            'title' => 'Pencatatan Gudang - Log Mutasi Barang',
            'logs' => $logs,
            'items' => $items,
            'totalLogsCount' => $totalLogsCount,
            'totalInQty' => $totalInQty,
            'totalOutQty' => $totalOutQty,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Store a newly created stock movement log (Barang Masuk / Keluar) and update item stock.
     */
    public function store(Request $request)
    {
        if ($request->has('quantity')) {
            $request->merge([
                'quantity' => str_replace(',', '.', (string) $request->input('quantity')),
            ]);
        }

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:in,out',
            'quantity' => 'required|numeric|gt:0',
            'recipient_or_provider' => 'required|string|max:255',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'created_at' => 'nullable|date',
        ], [
            'item_id.required' => 'Pilih barang yang mengalami mutasi stok.',
            'item_id.exists' => 'Barang yang dipilih tidak valid.',
            'type.required' => 'Pilih jenis mutasi (Masuk atau Keluar).',
            'quantity.required' => 'Jumlah barang wajib diisi.',
            'quantity.gt' => 'Jumlah barang harus lebih dari 0.',
            'recipient_or_provider.required' => 'Nama Penyedia / Penerima barang wajib diisi.',
        ]);

        try {
            DB::transaction(function() use ($validated, $request) {
                $item = Item::findOrFail($validated['item_id']);
                $fmtQty = format_quantity($validated['quantity']);
                $fmtStock = format_quantity($item->stock);

                // Validation check for outgoing stock
                if ($validated['type'] === 'out' && $item->stock < $validated['quantity']) {
                    throw new \Exception("Stok fisik {$item->name} tidak mencukupi! (Stok saat ini: {$fmtStock} {$item->unit}, Permintaan: {$fmtQty} {$item->unit})");
                }

                // Update physical item stock
                if ($validated['type'] === 'in') {
                    $item->increment('stock', $validated['quantity']);
                } else {
                    $item->decrement('stock', $validated['quantity']);
                }

                // Record stock log
                $logData = [
                    'item_id' => $item->id,
                    'user_id' => auth()->id(),
                    'type' => $validated['type'],
                    'quantity' => (float) $validated['quantity'],
                    'recipient_or_provider' => $validated['recipient_or_provider'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ];

                if (!empty($validated['created_at'])) {
                    $logData['created_at'] = $validated['created_at'];
                }

                StockLog::create($logData);

                $typeLabel = $validated['type'] === 'in' ? 'Barang Masuk' : 'Barang Keluar';
                ActivityLog::record(
                    activity: "Pencatatan {$typeLabel}",
                    description: "Mencatat transaksi {$typeLabel} untuk barang \"{$item->name}\" sebanyak {$fmtQty} {$item->unit} (" . ($validated['recipient_or_provider']) . ").",
                    module: 'Pencatatan Gudang'
                );
            });

            $typeLabel = $request->type === 'in' ? 'Barang Masuk' : 'Barang Keluar';
            return redirect()->route('logs.index')->with('success', "Pencatatan mutasi {$typeLabel} berhasil disimpan dan stok fisik barang otomatis diperbarui!");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal mencatat mutasi gudang: ' . $e->getMessage());
        }
    }

    /**
     * Download PDF Bukti Transaksi (Surat Jalan / Bukti Serah Terima).
     */
    public function downloadPdf(StockLog $log)
    {
        try {
            $log->load(['item.category', 'user']);

            ActivityLog::record(
                activity: 'Cetak PDF Surat Jalan / Bukti Transaksi',
                description: 'Mengunduh dokumen PDF ' . ($log->type == 'in' ? 'Bukti Penerimaan' : 'Surat Jalan') . ' No. Ref: ' . ($log->reference_number ?? $log->id) . '.',
                module: 'Pencatatan Gudang'
            );

            $pdf = Pdf::loadView('pdf.surat-jalan', [
                'log' => $log,
                'company' => \App\Models\CompanyProfile::getProfile(),
            ])->setPaper('a4', 'portrait');

            $fileName = ($log->type == 'in' ? 'Bukti_Penerimaan_' : 'Surat_Jalan_') . ($log->reference_number ?? $log->id) . '.pdf';
            return $pdf->download($fileName);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membuat dokumen PDF: ' . $e->getMessage());
        }
    }
}
