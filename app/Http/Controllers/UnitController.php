<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the units master data.
     */
    public function index(Request $request)
    {
        $query = Unit::withCount('items');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('symbol', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $units = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();

        $totalUnits = Unit::count();
        $totalActiveUnits = Unit::where('is_active', true)->count();
        $totalItemsWithUnit = Item::whereNotNull('unit_id')->count();

        return view('pages.units.index', [
            'title' => 'Master Data Satuan Barang',
            'units' => $units,
            'totalUnits' => $totalUnits,
            'totalActiveUnits' => $totalActiveUnits,
            'totalItemsWithUnit' => $totalItemsWithUnit,
        ]);
    }

    /**
     * Store a newly created unit in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:50|unique:units,symbol',
            'description' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Nama satuan wajib diisi.',
            'symbol.required' => 'Simbol / kode satuan wajib diisi.',
            'symbol.unique' => 'Simbol satuan ini sudah terdaftar di sistem.',
        ]);

        try {
            $validated['symbol'] = strtoupper(trim($validated['symbol']));
            $validated['name'] = trim($validated['name']);
            $validated['is_active'] = true;

            $unit = Unit::create($validated);

            ActivityLog::record(
                'Tambah Satuan Barang',
                "Menambahkan master satuan baru: {$unit->name} ({$unit->symbol})",
                'Satuan Barang'
            );

            return redirect()->route('units.index')->with('success', "Master Satuan '{$unit->name} ({$unit->symbol})' berhasil ditambahkan!");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal menambah satuan: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified unit in storage.
     */
    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'required|string|max:50|unique:units,symbol,' . $unit->id,
            'description' => 'nullable|string|max:500',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama satuan wajib diisi.',
            'symbol.required' => 'Simbol / kode satuan wajib diisi.',
            'symbol.unique' => 'Simbol satuan ini sudah digunakan oleh satuan lain.',
        ]);

        try {
            $oldSymbol = $unit->symbol;
            $newSymbol = strtoupper(trim($validated['symbol']));

            $unit->name = trim($validated['name']);
            $unit->symbol = $newSymbol;
            $unit->description = $validated['description'] ?? null;
            if ($request->has('is_active')) {
                $unit->is_active = (bool) $request->is_active;
            }
            $unit->save();

            ActivityLog::record(
                'Ubah Satuan Barang',
                "Memperbarui master satuan: {$oldSymbol} -> {$unit->symbol} ({$unit->name})",
                'Satuan Barang'
            );

            return redirect()->route('units.index')->with('success', "Master Satuan '{$unit->name} ({$unit->symbol})' berhasil diperbarui! Seluruh data barang dan transaksi otomatis mengikuti pembaruan ini.");
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui satuan: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified unit from storage.
     */
    public function destroy(Unit $unit)
    {
        try {
            $itemCount = $unit->items()->count();
            if ($itemCount > 0) {
                return redirect()->back()->with('error', "Satuan '{$unit->symbol}' tidak dapat dihapus karena masih digunakan oleh {$itemCount} item barang di sistem gudang!");
            }

            $name = $unit->name;
            $symbol = $unit->symbol;
            $unit->delete();

            ActivityLog::record(
                'Hapus Satuan Barang',
                "Menghapus master satuan: {$name} ({$symbol})",
                'Satuan Barang'
            );

            return redirect()->route('units.index')->with('success', "Satuan '{$name} ({$symbol})' berhasil dihapus!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus satuan: ' . $e->getMessage());
        }
    }
}
