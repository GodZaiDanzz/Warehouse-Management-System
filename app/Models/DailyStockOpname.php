<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyStockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'item_id',
        'shift',
        'initial_stock',
        'final_stock',
        'used_qty',
        'system_out_qty',
        'difference',
        'status',
        'notes',
        'counted_by',
        'closed_by',
        'closed_at',
        'adjustment_status',
        'adjusted_by',
        'adjusted_at',
        'adjustment_notes',
    ];

    protected $casts = [
        'date' => 'date',
        'closed_at' => 'datetime',
        'adjusted_at' => 'datetime',
        'initial_stock' => 'float',
        'final_stock' => 'float',
        'used_qty' => 'float',
        'system_out_qty' => 'float',
        'difference' => 'float',
    ];

    /**
     * Relasi ke Item Barang.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relasi ke User yang menghitung fisik.
     */
    public function countedByUser()
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    /**
     * Relasi ke User yang menutup buku / sinkronisasi.
     */
    public function closedByUser()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Relasi ke User yang menyetujui penyesuaian stok opname.
     */
    public function adjustedByUser()
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }

    /**
     * Recalculate usage and difference based on formula:
     * Stok Awal - Stok Akhir = Total Terpakai.
     * Selisih = Total Terpakai - Mutasi Keluar Sistem.
     */
    public function recalculate(): self
    {
        if ($this->final_stock !== null) {
            $this->used_qty = round((float)$this->initial_stock - (float)$this->final_stock, 4);
            $this->difference = round((float)$this->used_qty - (float)$this->system_out_qty, 4);

            if ($this->status !== 'synced') {
                $this->status = (abs($this->difference) < 0.0001) ? 'completed' : 'discrepancy';
                if (abs($this->difference) >= 0.0001 && $this->adjustment_status === 'none') {
                    $this->adjustment_status = 'pending';
                } elseif (abs($this->difference) < 0.0001) {
                    $this->adjustment_status = 'none';
                }
            }
        } else {
            $this->used_qty = 0;
            $this->difference = 0;
            if ($this->status !== 'synced') {
                $this->status = 'pending';
                $this->adjustment_status = 'none';
            }
        }

        return $this;
    }

    /**
     * Inisialisasi otomatis Stock Opname Harian (1 Shift Harian Gudang).
     */
    public static function initializeForDate(string $date, string $shift = 'Shift Harian'): void
    {
        $items = Item::all();
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        if (empty($shift)) {
            $shift = 'Shift Harian';
        }

        foreach ($items as $item) {
            $existing = self::where('date', $dateStr)
                ->where('item_id', $item->id)
                ->first();

            if (!$existing) {
                // Tentukan stok awal: Cari Opname kemarin yang terisi stok akhirnya, atau stok item master
                $prevOpname = self::where('item_id', $item->id)
                    ->where('date', '<', $dateStr)
                    ->whereNotNull('final_stock')
                    ->orderBy('date', 'desc')
                    ->first();
                $initialStock = $prevOpname ? $prevOpname->final_stock : $item->stock;

                // Hitung pengeluaran sistem (Material Out) pada tanggal ini
                $systemOut = MaterialOut::where('item_id', $item->id)
                    ->whereDate('date', $dateStr)
                    ->sum('quantity');

                self::create([
                    'date' => $dateStr,
                    'item_id' => $item->id,
                    'shift' => $shift,
                    'initial_stock' => $initialStock,
                    'final_stock' => null,
                    'used_qty' => 0,
                    'system_out_qty' => $systemOut,
                    'difference' => 0,
                    'status' => 'pending',
                ]);
            } else {
                $systemOut = MaterialOut::where('item_id', $item->id)
                    ->whereDate('date', $dateStr)
                    ->sum('quantity');

                if ($existing->system_out_qty != $systemOut && $existing->status !== 'synced') {
                    $existing->system_out_qty = $systemOut;
                    $existing->recalculate()->save();
                }
            }
        }
    }

    /**
     * Roll forward: Jika stok akhir hari ini diupdate, update stok awal hari berikutnya secara otomatis
     */
    public static function rollForward(int $itemId, string $date, string|float|int $shiftOrStock, ?float $newFinalStock = null): void
    {
        $dateStr = Carbon::parse($date)->format('Y-m-d');
        $finalStock = is_numeric($shiftOrStock) && $newFinalStock === null ? (float)$shiftOrStock : (float)($newFinalStock ?? 0);

        // Update stok awal opname di hari berikutnya jika sudah di-generate
        $nextDate = Carbon::parse($dateStr)->addDay()->format('Y-m-d');
        $nextOpname = self::where('date', $nextDate)
            ->where('item_id', $itemId)
            ->first();

        if ($nextOpname && $nextOpname->status !== 'synced') {
            $nextOpname->initial_stock = $finalStock;
            $nextOpname->recalculate()->save();
        }
    }
}
