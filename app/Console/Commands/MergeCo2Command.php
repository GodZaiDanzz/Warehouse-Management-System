<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\DailyStockOpname;
use App\Models\Item;
use App\Models\MaterialIn;
use App\Models\MaterialOut;
use App\Models\StockLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeCo2Command extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:merge-co2 {--dry-run : Simulate merge without committing to database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menyatukan data CO2 ke data GAS CO2';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        $this->info('Mencari data item target (GAS CO2) dan sumber (CO2)...');
        $targetItem = Item::where('name', 'GAS CO2')->first();

        if (!$targetItem) {
            $this->error('Item target "GAS CO2" tidak ditemukan!');
            return Command::FAILURE;
        }

        // Cari item sumber: tepat bernama 'CO2'
        $sourceItem = Item::where('id', '!=', $targetItem->id)
            ->where('name', 'CO2')
            ->first();

        if (!$sourceItem) {
            $this->warn('Item sumber "CO2" tidak ditemukan. Mungkin data sudah disatukan ke GAS CO2 sebelumnya.');
            return Command::SUCCESS;
        }

        $rows = [
            ['Sumber', $sourceItem->id, $sourceItem->code_item, $sourceItem->name, $sourceItem->stock, $sourceItem->unit],
            ['Target', $targetItem->id, $targetItem->code_item, $targetItem->name, $targetItem->stock, $targetItem->unit],
        ];

        $this->table(['Peran', 'ID', 'Kode', 'Nama', 'Stok Saat Ini', 'Satuan'], $rows);

        if ($isDryRun) {
            $this->warn('Mode DRY-RUN aktif. Tidak ada perubahan yang akan disimpan ke database.');
        }

        DB::beginTransaction();

        try {
            $this->line("\nMemproses penyatuan [{$sourceItem->code_item}] {$sourceItem->name} ke [{$targetItem->code_item}] {$targetItem->name}...");

            // 1. Pindahkan MaterialOut
            $outCount = MaterialOut::where('item_id', $sourceItem->id)->count();
            $outQtySum = MaterialOut::where('item_id', $sourceItem->id)->sum('quantity');
            MaterialOut::where('item_id', $sourceItem->id)->update(['item_id' => $targetItem->id]);
            $this->line(" - Memindahkan Material Out: {$outCount} transaksi (Total Qty: {$outQtySum}).");

            // 2. Pindahkan StockLog
            $logCount = StockLog::where('item_id', $sourceItem->id)->count();
            StockLog::where('item_id', $sourceItem->id)->update(['item_id' => $targetItem->id]);
            $this->line(" - Memindahkan Stock Log: {$logCount} riwayat log.");

            // 3. Pindahkan MaterialIn (jika ada)
            $inCount = MaterialIn::where('item_id', $sourceItem->id)->count();
            if ($inCount > 0) {
                MaterialIn::where('item_id', $sourceItem->id)->update(['item_id' => $targetItem->id]);
                $this->line(" - Memindahkan Material In: {$inCount} transaksi.");
            }

            // 4. Gabungkan DailyStockOpname tanpa bentrok unique constraint
            $sourceOpnames = DailyStockOpname::where('item_id', $sourceItem->id)->get();
            $mergedOpnames = 0;
            $movedOpnames = 0;

            foreach ($sourceOpnames as $srcOp) {
                $dateStr = $srcOp->date->format('Y-m-d');
                $shift = $srcOp->shift;

                $targetOp = DailyStockOpname::where('item_id', $targetItem->id)
                    ->where('date', $dateStr)
                    ->where('shift', $shift)
                    ->first();

                if ($targetOp) {
                    $targetOp->initial_stock = (float)$targetOp->initial_stock + (float)$srcOp->initial_stock;
                    $targetOp->system_out_qty = MaterialOut::where('item_id', $targetItem->id)
                        ->whereDate('date', $dateStr)
                        ->sum('quantity');

                    if ($targetOp->final_stock !== null) {
                        $targetOp->used_qty = (float)$targetOp->initial_stock - (float)$targetOp->final_stock;
                        $targetOp->difference = (float)$targetOp->used_qty - (float)$targetOp->system_out_qty;
                        if ($targetOp->status !== 'synced') {
                            $targetOp->status = ($targetOp->difference == 0) ? 'completed' : 'discrepancy';
                        }
                    } else {
                        $targetOp->used_qty = 0;
                        $targetOp->difference = 0;
                    }
                    $targetOp->save();
                    $srcOp->delete();
                    $mergedOpnames++;
                } else {
                    $srcOp->update(['item_id' => $targetItem->id]);
                    $movedOpnames++;
                }
            }
            $this->line(" - Sinkronisasi Stock Opname Harian: {$mergedOpnames} record digabungkan, {$movedOpnames} dipindahkan.");

            // 5. Update stok target
            $targetItem->stock = (float)$targetItem->stock + (float)$sourceItem->stock;
            $targetItem->save();
            $this->line(" - Memperbarui total stok target: {$targetItem->stock} {$targetItem->unit}.");

            // 6. Hapus item sumber lama
            $srcName = $sourceItem->name;
            $srcCode = $sourceItem->code_item;
            $sourceItem->delete();
            $this->line(" - Menghapus master barang duplikat: [{$srcCode}] {$srcName}.");

            // 7. Catat Activity Log
            ActivityLog::record(
                'Merge Item Data',
                "Penyatuan data item '{$srcName}' ({$srcCode}) ke '{$targetItem->name}' ({$targetItem->code_item}). Total transaksi keluar dipindahkan: {$outCount} (Qty: {$outQtySum}).",
                'Inventory',
                null
            );

            // Hitung metrik hasil penyatuan sebelum commit / rollback
            $finalOutCount = MaterialOut::where('item_id', $targetItem->id)->count();
            $finalOutSum = MaterialOut::where('item_id', $targetItem->id)->sum('quantity');
            $finalLogCount = StockLog::where('item_id', $targetItem->id)->count();

            if ($isDryRun) {
                DB::rollBack();
                $this->info("\nDry-run selesai dengan sukses. Data TIDAK diubah.");
            } else {
                DB::commit();
                $this->info("\nPenyatuan data berhasil dan telah dikomit ke database!");
            }

            // Tampilkan rangkuman hasil
            $this->table(['Metrik', 'Nilai'], [
                ['Item Target', "[{$targetItem->code_item}] {$targetItem->name}"],
                ['Stok Akhir', "{$targetItem->stock} {$targetItem->unit}"],
                ['Total Transaksi Material Out', "{$finalOutCount} transaksi"],
                ['Total Kuantitas Keluar', "{$finalOutSum} {$targetItem->unit}"],
                ['Total Stock Logs', "{$finalLogCount} logs"],
                ['Total Dipindahkan', "{$outCount} transaksi (Qty: {$outQtySum})"],
            ]);

            return Command::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Terjadi kesalahan saat menyatukan data: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }
}
