<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\MaterialOut;
use App\Models\Project;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::withCount('materialOuts')
            ->withSum('materialOuts', 'quantity');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        $projects = $query->latest()->paginate(12)->withQueryString();

        $stats = [
            'total' => Project::count(),
            'total_material_qty' => MaterialOut::whereNotNull('project_id')->sum('quantity'),
            'total_transactions' => MaterialOut::whereNotNull('project_id')->count(),
            'unique_items' => MaterialOut::whereNotNull('project_id')->distinct('item_id')->count('item_id'),
        ];

        return view('pages.projects.index', compact('projects', 'stats'));
    }

    public function store(Request $request)
    {
        if ($request->has('tonnage') && $request->input('tonnage') !== null) {
            $request->merge(['tonnage' => str_replace(',', '.', (string)$request->input('tonnage'))]);
        }

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:100'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'client' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'tonnage' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama Project wajib diisi.',
            'tonnage.required' => 'Tonase project wajib diisi (minimal 0 Ton).',
            'tonnage.numeric' => 'Tonase project harus berupa angka yang valid.',
            'tonnage.min' => 'Tonase project tidak boleh bernilai negatif.',
            'start_date.date' => 'Format Tanggal Mulai tidak valid.',
            'end_date.date' => 'Format Tanggal Selesai tidak valid.',
        ]);

        $validated['created_by'] = Auth::id();
        if (empty($validated['status'])) {
            $validated['status'] = 'Ongoing';
        }

        if ($validated['status'] === 'Completed' && empty($validated['end_date'])) {
            $validated['end_date'] = now()->toDateString();
        }

        $project = Project::create($validated);

        ActivityLog::record(
            activity: 'Tambah Project Baru',
            description: "Menambahkan project baru '{$project->name}'" . ($project->location ? " (Tempat Fabrikasi: {$project->location})" : ''),
            module: 'Projects'
        );

        return redirect()->back()->with('success', "Project '{$project->name}' berhasil ditambahkan!");
    }

    public function show($id)
    {
        $project = Project::with(['finishGoods' => function($q) {
            $q->latest('production_date')->latest('id');
        }])->findOrFail($id);

        $baseQuery = MaterialOut::where(function($q) use ($project) {
            $q->where('project_id', $project->id)
              ->orWhere('project', $project->name);
        });

        $materialOuts = (clone $baseQuery)
            ->with(['item', 'user'])
            ->latest('date')
            ->paginate(15);

        $itemsList = Item::orderBy('name')->get();

        // Calculate discrete and decimal totals, and total material cost
        $allProjectOuts = (clone $baseQuery)
            ->leftJoin('items', 'material_outs.item_id', '=', 'items.id')
            ->select(
                'material_outs.quantity',
                'items.unit_price',
                DB::raw("UPPER(TRIM(COALESCE(material_outs.unit, items.unit, 'PCS'))) as item_unit")
            )
            ->get();

        $projectDiscreteQty = 0;
        $projectDecimalTotals = [];
        $totalMaterialCost = 0;
        foreach ($allProjectOuts as $apo) {
            $u = $apo->item_unit;
            $q = (float) $apo->quantity;
            $price = (float) ($apo->unit_price ?? 0);
            $totalMaterialCost += ($q * $price);

            if (is_decimal_unit($u)) {
                $projectDecimalTotals[$u] = ($projectDecimalTotals[$u] ?? 0) + $q;
            } else {
                $projectDiscreteQty += $q;
            }
        }
        $projectDecimalString = format_unit_breakdown($projectDecimalTotals);

        $summary = [
            'total_qty' => $projectDiscreteQty,
            'discrete_qty' => $projectDiscreteQty,
            'decimal_string' => $projectDecimalString,
            'total_transactions' => (clone $baseQuery)->count(),
            'unique_items' => (clone $baseQuery)->distinct('item_id')->count('item_id'),
            'total_cost' => $totalMaterialCost,
        ];

        $materialSummaryPerItem = (clone $baseQuery)
            ->select(
                'material_outs.item_id',
                'material_outs.unit',
                DB::raw('SUM(material_outs.quantity) as total_qty'),
                DB::raw('COUNT(*) as total_trans'),
                DB::raw('MAX(material_outs.date) as last_used_date')
            )
            ->with('item')
            ->groupBy('material_outs.item_id', 'material_outs.unit')
            ->orderByDesc('total_qty')
            ->get();

        $currentUser = Auth::user();
        $canViewPrices = $currentUser && $currentUser->canViewPrices();

        return view('pages.projects.show', compact('project', 'materialOuts', 'itemsList', 'summary', 'materialSummaryPerItem', 'canViewPrices'));
    }

    public function update(Request $request, Project $project)
    {
        if ($request->has('tonnage') && $request->input('tonnage') !== null) {
            $request->merge(['tonnage' => str_replace(',', '.', (string)$request->input('tonnage'))]);
        }

        $validated = $request->validate([
            'code' => ['nullable', 'string', 'max:100'],
            'po_number' => ['nullable', 'string', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'client' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],
            'tonnage' => ['required', 'numeric', 'min:0'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama Project wajib diisi.',
            'tonnage.required' => 'Tonase project wajib diisi (minimal 0 Ton).',
            'tonnage.numeric' => 'Tonase project harus berupa angka yang valid.',
            'tonnage.min' => 'Tonase project tidak boleh bernilai negatif.',
            'start_date.date' => 'Format Tanggal Mulai tidak valid.',
            'end_date.date' => 'Format Tanggal Selesai tidak valid.',
        ]);

        if (empty($validated['status'])) {
            $validated['status'] = $project->status ?? 'Ongoing';
        }

        if ($validated['status'] === 'Completed' && empty($validated['end_date'])) {
            $validated['end_date'] = $project->end_date ? $project->end_date->toDateString() : now()->toDateString();
        }

        $project->update($validated);

        ActivityLog::record(
            activity: 'Update Data Project',
            description: "Perbarui data project '{$project->name}'",
            module: 'Projects'
        );

        return redirect()->back()->with('success', "Data project '{$project->name}' berhasil diperbarui!");
    }

    public function toggleStatus(Request $request, Project $project)
    {
        $newStatus = ($project->status === 'Completed') ? 'Ongoing' : 'Completed';
        $updateData = ['status' => $newStatus];

        if ($newStatus === 'Completed') {
            $customEndDate = $request->input('end_date');
            $updateData['end_date'] = !empty($customEndDate) ? $customEndDate : ($project->end_date ? $project->end_date->toDateString() : now()->toDateString());
        }

        $project->update($updateData);

        $statusText = $newStatus === 'Completed' ? 'Selesai (Completed)' : 'Berjalan (Ongoing)';

        ActivityLog::record(
            activity: 'Ubah Status Project',
            description: "Mengubah status project '{$project->name}' menjadi {$statusText}",
            module: 'Projects'
        );

        return redirect()->back()->with('success', "Status project '{$project->name}' berhasil diubah menjadi {$statusText}!");
    }

    public function destroy(Project $project)
    {
        $name = $project->name;
        $project->delete();

        ActivityLog::record(
            activity: 'Hapus Project',
            description: "Menghapus project '{$name}'",
            module: 'Projects'
        );

        return redirect()->route('projects.index')->with('success', "Project '{$name}' berhasil dihapus!");
    }

    /**
     * Record material usage specifically for this project (automatically deducts stock)
     */
    public function addMaterial(Request $request, Project $project)
    {
        if ($request->has('quantity')) {
            $request->merge([
                'quantity' => (float) str_replace(',', '.', (string) $request->input('quantity'))
            ]);
        }

        $validated = $request->validate([
            'date' => ['required', 'date'],
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'unit' => ['required', 'string', 'max:50'],
            'pic' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ], [
            'item_id.required' => 'Pilih barang yang akan digunakan.',
            'quantity.gt' => 'Jumlah barang harus lebih dari 0.',
            'pic.required' => 'Nama PIC penerima wajib diisi.',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if ((float) $item->stock < (float) $validated['quantity']) {
            return redirect()->back()->with('error', "Stok barang '{$item->name}' tidak mencukupi! Stok saat ini: " . format_quantity($item->stock) . " {$item->unit}.");
        }

        DB::transaction(function() use ($validated, $item, $project) {
            $refNo = 'OUT-PRJ-' . date('Ymd') . '-' . sprintf('%04d', rand(1000, 9999));

            // Create Material Out linked to Project
            MaterialOut::create([
                'reference_number' => $refNo,
                'date' => $validated['date'],
                'item_id' => $item->id,
                'quantity' => $validated['quantity'],
                'unit' => strtoupper(trim($validated['unit'])),
                'project' => $project->name,
                'project_id' => $project->id,
                'pic' => trim($validated['pic']),
                'notes' => $validated['notes'] ?? "Penggunaan Material Project {$project->name}",
                'user_id' => Auth::id(),
            ]);

            // Deduct stock from warehouse
            $item->decrement('stock', $validated['quantity']);

            // Record Stock Log mutation
            StockLog::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'recipient_or_provider' => trim($validated['pic']),
                'reference_number' => $refNo,
                'notes' => "Penggunaan Material Project {$project->name}",
            ]);
        });

        $formattedQty = format_quantity($validated['quantity']);
        ActivityLog::record(
            activity: 'Penggunaan Material Project',
            description: "Mencatat penggunaan {$formattedQty} {$validated['unit']} {$item->name} untuk project '{$project->name}' (Stok terpotong otomatis)",
            module: 'Projects'
        );

        return redirect()->back()->with('success', "Material berhasil dicatat untuk project '{$project->name}'! Stok barang '{$item->name}' di gudang otomatis terpotong {$formattedQty} {$validated['unit']}.");
    }
}
