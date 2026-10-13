<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Find duplicate item names (case-insensitive & trimmed)
        $duplicates = DB::table('items')
            ->select(DB::raw('LOWER(TRIM(name)) as name_clean'))
            ->groupBy(DB::raw('LOWER(TRIM(name))'))
            ->havingRaw('COUNT(*) > 1')
            ->pluck('name_clean');

        foreach ($duplicates as $nameClean) {
            $items = DB::table('items')
                ->whereRaw('LOWER(TRIM(name)) = ?', [$nameClean])
                ->orderBy('id', 'asc')
                ->get();

            if ($items->count() <= 1) {
                continue;
            }

            $primaryItem = $items->first();
            $duplicateItems = $items->slice(1);

            foreach ($duplicateItems as $dup) {
                // Add stock to primary item
                DB::table('items')->where('id', $primaryItem->id)->increment('stock', $dup->stock);

                // Re-link foreign key references
                DB::table('stock_logs')->where('item_id', $dup->id)->update(['item_id' => $primaryItem->id]);
                DB::table('material_ins')->where('item_id', $dup->id)->update(['item_id' => $primaryItem->id]);
                DB::table('material_outs')->where('item_id', $dup->id)->update(['item_id' => $primaryItem->id]);
                DB::table('daily_stock_opnames')->where('item_id', $dup->id)->update(['item_id' => $primaryItem->id]);

                // Delete duplicate item
                DB::table('items')->where('id', $dup->id)->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Non-reversible data merge
    }
};
