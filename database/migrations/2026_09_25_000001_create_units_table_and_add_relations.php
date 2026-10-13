<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create units table
        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('symbol', 50)->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // 2. Seed initial canonical units
        $defaultUnits = [
            ['name' => 'Pieces', 'symbol' => 'PCS', 'description' => 'Satuan hitungan satuan barang / pcs'],
            ['name' => 'Kilogram', 'symbol' => 'KG', 'description' => 'Satuan berat kilogram'],
            ['name' => 'Gram', 'symbol' => 'GRAM', 'description' => 'Satuan berat gram'],
            ['name' => 'Liter', 'symbol' => 'LITER', 'description' => 'Satuan volume zat cair liter'],
            ['name' => 'Mililiter', 'symbol' => 'ML', 'description' => 'Satuan volume zat cair mililiter'],
            ['name' => 'Meter', 'symbol' => 'METER', 'description' => 'Satuan panjang meter'],
            ['name' => 'Centimeter', 'symbol' => 'CM', 'description' => 'Satuan panjang centimeter'],
            ['name' => 'Milimeter', 'symbol' => 'MM', 'description' => 'Satuan panjang milimeter'],
            ['name' => 'Lembar', 'symbol' => 'LEMBAR', 'description' => 'Satuan lembaran pelat / kertas'],
            ['name' => 'Batang', 'symbol' => 'BATANG', 'description' => 'Satuan batangan pipa / besi'],
            ['name' => 'Roll', 'symbol' => 'ROLL', 'description' => 'Satuan gulungan roll'],
            ['name' => 'Tabung', 'symbol' => 'TABUNG', 'description' => 'Satuan tabung gas / silinder'],
            ['name' => 'Botol', 'symbol' => 'BOTOL', 'description' => 'Satuan botol'],
            ['name' => 'Box', 'symbol' => 'BOX', 'description' => 'Satuan kotak / box'],
            ['name' => 'Pack', 'symbol' => 'PACK', 'description' => 'Satuan bungkusan pack'],
            ['name' => 'Pasang', 'symbol' => 'PASANG', 'description' => 'Satuan pasang / pair'],
            ['name' => 'Lusin', 'symbol' => 'LUSIN', 'description' => 'Satuan lusin (12 pcs)'],
            ['name' => 'Set', 'symbol' => 'SET', 'description' => 'Satuan rangkaian / set perlengkapan'],
            ['name' => 'Pail', 'symbol' => 'PAIL', 'description' => 'Satuan ember cat / pail'],
            ['name' => 'Can', 'symbol' => 'CAN', 'description' => 'Satuan kaleng / can'],
            ['name' => 'Drum', 'symbol' => 'DRUM', 'description' => 'Satuan drum oli / zat kimia'],
        ];

        $now = now();
        foreach ($defaultUnits as $u) {
            DB::table('units')->updateOrInsert(
                ['symbol' => $u['symbol']],
                [
                    'name' => $u['name'],
                    'description' => $u['description'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 3. Add unit_id foreign key columns if not present
        if (!Schema::hasColumn('items', 'unit_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->foreignId('unit_id')->nullable()->after('stock')->constrained('units')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('material_ins', 'unit_id')) {
            Schema::table('material_ins', function (Blueprint $table) {
                $table->foreignId('unit_id')->nullable()->after('quantity')->constrained('units')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('material_outs', 'unit_id')) {
            Schema::table('material_outs', function (Blueprint $table) {
                $table->foreignId('unit_id')->nullable()->after('quantity')->constrained('units')->nullOnDelete();
            });
        }

        if (Schema::hasTable('material_rejects') && !Schema::hasColumn('material_rejects', 'unit_id')) {
            Schema::table('material_rejects', function (Blueprint $table) {
                $table->foreignId('unit_id')->nullable()->after('quantity')->constrained('units')->nullOnDelete();
            });
        }

        // 4. Map existing items to unit_id
        $units = DB::table('units')->get()->keyBy(function ($item) {
            return strtoupper(trim($item->symbol));
        });

        // Helper aliases
        $aliases = [
            'KG' => 'KG',
            'PCS' => 'PCS',
            'BTG' => 'BATANG',
            'BATANG' => 'BATANG',
            'M' => 'METER',
            'MTR' => 'METER',
            'METER' => 'METER',
            'CM' => 'CM',
            'MM' => 'MM',
            'L' => 'LITER',
            'LTR' => 'LITER',
            'LITER' => 'LITER',
            'BOTOL' => 'BOTOL',
            'BTL' => 'BOTOL',
            'TABUNG' => 'TABUNG',
            'TBNG' => 'TABUNG',
            'PACK' => 'PACK',
            'PAK' => 'PACK',
            'PASANG' => 'PASANG',
            'PAIR' => 'PASANG',
            'ROLL' => 'ROLL',
            'SET' => 'SET',
            'LUSIN' => 'LUSIN',
            'DZ' => 'LUSIN',
            'LEMBAR' => 'LEMBAR',
            'LBR' => 'LEMBAR',
        ];

        $items = DB::table('items')->get();
        foreach ($items as $item) {
            $rawUnit = strtoupper(trim((string) $item->unit));
            $targetSymbol = $aliases[$rawUnit] ?? $rawUnit;

            if (isset($units[$targetSymbol])) {
                $unitRecord = $units[$targetSymbol];
                DB::table('items')->where('id', $item->id)->update([
                    'unit_id' => $unitRecord->id,
                    'unit' => $unitRecord->symbol,
                ]);
            }
        }

        // 5. Populate material_ins unit_id
        $materialIns = DB::table('material_ins')->get();
        foreach ($materialIns as $in) {
            $rawUnit = strtoupper(trim((string) $in->unit));
            $targetSymbol = $aliases[$rawUnit] ?? $rawUnit;

            $unitId = null;
            if (isset($units[$targetSymbol])) {
                $unitId = $units[$targetSymbol]->id;
            } elseif ($in->item_id) {
                $item = DB::table('items')->where('id', $in->item_id)->first();
                $unitId = $item?->unit_id;
            }

            if ($unitId) {
                $unitRec = DB::table('units')->where('id', $unitId)->first();
                DB::table('material_ins')->where('id', $in->id)->update([
                    'unit_id' => $unitId,
                    'unit' => $unitRec ? $unitRec->symbol : $in->unit,
                ]);
            }
        }

        // 6. Populate material_outs unit_id
        $materialOuts = DB::table('material_outs')->get();
        foreach ($materialOuts as $out) {
            $rawUnit = strtoupper(trim((string) $out->unit));
            $targetSymbol = $aliases[$rawUnit] ?? $rawUnit;

            $unitId = null;
            if (isset($units[$targetSymbol])) {
                $unitId = $units[$targetSymbol]->id;
            } elseif ($out->item_id) {
                $item = DB::table('items')->where('id', $out->item_id)->first();
                $unitId = $item?->unit_id;
            }

            if ($unitId) {
                $unitRec = DB::table('units')->where('id', $unitId)->first();
                DB::table('material_outs')->where('id', $out->id)->update([
                    'unit_id' => $unitId,
                    'unit' => $unitRec ? $unitRec->symbol : $out->unit,
                ]);
            }
        }

        // 7. Populate material_rejects unit_id if table exists
        if (Schema::hasTable('material_rejects')) {
            $rejects = DB::table('material_rejects')->get();
            foreach ($rejects as $rej) {
                $rawUnit = strtoupper(trim((string) $rej->unit));
                $targetSymbol = $aliases[$rawUnit] ?? $rawUnit;

                $unitId = null;
                if (isset($units[$targetSymbol])) {
                    $unitId = $units[$targetSymbol]->id;
                } elseif ($rej->item_id) {
                    $item = DB::table('items')->where('id', $rej->item_id)->first();
                    $unitId = $item?->unit_id;
                }

                if ($unitId) {
                    $unitRec = DB::table('units')->where('id', $unitId)->first();
                    DB::table('material_rejects')->where('id', $rej->id)->update([
                        'unit_id' => $unitId,
                        'unit' => $unitRec ? $unitRec->symbol : $rej->unit,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('material_rejects', 'unit_id')) {
            Schema::table('material_rejects', function (Blueprint $table) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            });
        }

        if (Schema::hasColumn('material_outs', 'unit_id')) {
            Schema::table('material_outs', function (Blueprint $table) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            });
        }

        if (Schema::hasColumn('material_ins', 'unit_id')) {
            Schema::table('material_ins', function (Blueprint $table) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            });
        }

        if (Schema::hasColumn('items', 'unit_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            });
        }

        Schema::dropIfExists('units');
    }
};
