<?php

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $categories = Category::all()->keyBy('id');

        $items = DB::table('items')
            ->whereNull('code_item')
            ->orWhere('code_item', '')
            ->orderBy('id', 'asc')
            ->get();

        $prefixCounters = [];

        foreach ($items as $item) {
            $prefix = 'MAT';
            if ($item->category_id && isset($categories[$item->category_id])) {
                $catName = strtolower($categories[$item->category_id]->name);
                if (str_contains($catName, 'consumable')) {
                    $prefix = 'CSM';
                } elseif (str_contains($catName, 'secondary')) {
                    $prefix = 'SEC';
                } elseif (str_contains($catName, 'cat') || str_contains($catName, 'paint') || str_contains($catName, 'thinner')) {
                    $prefix = 'CAT';
                } else {
                    $clean = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $categories[$item->category_id]->name));
                    $prefix = strlen($clean) >= 3 ? substr($clean, 0, 3) : 'MAT';
                }
            }

            if (!isset($prefixCounters[$prefix])) {
                $maxNum = 0;
                $existing = DB::table('items')
                    ->where('code_item', 'like', "{$prefix}-%")
                    ->pluck('code_item');

                foreach ($existing as $c) {
                    if (preg_match('/' . preg_quote($prefix, '/') . '-(\d+)/', (string)$c, $m)) {
                        $maxNum = max($maxNum, (int)$m[1]);
                    }
                }
                $prefixCounters[$prefix] = $maxNum;
            }

            $prefixCounters[$prefix]++;
            $code = sprintf('%s-%03d', $prefix, $prefixCounters[$prefix]);

            DB::table('items')->where('id', $item->id)->update(['code_item' => $code]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
