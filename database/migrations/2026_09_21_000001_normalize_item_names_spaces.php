<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $items = DB::table('items')->where('name', 'like', '%  %')->get();

        foreach ($items as $item) {
            $normalizedName = trim(preg_replace('/\s+/', ' ', $item->name));
            DB::table('items')->where('id', $item->id)->update(['name' => $normalizedName]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not reversible
    }
};
