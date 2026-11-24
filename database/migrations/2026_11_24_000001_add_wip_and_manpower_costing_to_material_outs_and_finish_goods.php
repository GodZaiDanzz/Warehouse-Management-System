<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for WIP stage tracking, manpower costing, and HPP calculation.
     */
    public function up(): void
    {
        // 1. Tambah kolom default hourly rate di company_profiles jika belum ada
        Schema::table('company_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('company_profiles', 'default_manpower_rate')) {
                $table->decimal('default_manpower_rate', 12, 2)->default(45000)->after('default_per_page')->comment('Standar tarif upah manpower per jam (Rp)');
            }
        });

        // 2. Tambah kolom akumulasi HPP di finish_goods terlebih dahulu sebelum foreignId di material_outs
        Schema::table('finish_goods', function (Blueprint $table) {
            if (!Schema::hasColumn('finish_goods', 'material_cost')) {
                $table->decimal('material_cost', 15, 2)->default(0)->after('quantity')->comment('Total akumulasi biaya material part & consumable');
            }
            if (!Schema::hasColumn('finish_goods', 'manpower_cost')) {
                $table->decimal('manpower_cost', 15, 2)->default(0)->after('material_cost')->comment('Total akumulasi biaya tenaga kerja langsung (man power)');
            }
            if (!Schema::hasColumn('finish_goods', 'total_hpp')) {
                $table->decimal('total_hpp', 15, 2)->default(0)->after('manpower_cost')->comment('Total HPP Final (material_cost + manpower_cost)');
            }
            if (!Schema::hasColumn('finish_goods', 'hpp_per_unit')) {
                $table->decimal('hpp_per_unit', 15, 2)->default(0)->after('total_hpp')->comment('HPP per satuan unit barang jadi');
            }
        });

        // 3. Tambah kolom tahapan WIP & manpower di material_outs
        Schema::table('material_outs', function (Blueprint $table) {
            if (!Schema::hasColumn('material_outs', 'wip_stage')) {
                $table->string('wip_stage', 50)->nullable()->after('project_id')->comment('Tahapan WIP: Cutting, Welding, Painting, Packing');
            }
            if (!Schema::hasColumn('material_outs', 'manpower_hours')) {
                $table->decimal('manpower_hours', 8, 2)->default(0)->after('wip_stage')->comment('Total jam kerja (man-hours) pengerjaan tahapan ini');
            }
            if (!Schema::hasColumn('material_outs', 'manpower_rate')) {
                $table->decimal('manpower_rate', 12, 2)->default(0)->after('manpower_hours')->comment('Tarif upah per jam kerja saat transaksi');
            }
            if (!Schema::hasColumn('material_outs', 'manpower_cost')) {
                $table->decimal('manpower_cost', 15, 2)->default(0)->after('manpower_rate')->comment('Total biaya manpower (hours x rate)');
            }
            if (!Schema::hasColumn('material_outs', 'finish_good_id')) {
                $table->foreignId('finish_good_id')->nullable()->after('manpower_cost')->constrained('finish_goods')->nullOnDelete()->comment('Terkait produk jadi yang dihasilkan');
            }

            $table->index(['project_id', 'wip_stage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_outs', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'wip_stage']);
            $table->dropForeign(['finish_good_id']);
            $table->dropColumn([
                'wip_stage',
                'manpower_hours',
                'manpower_rate',
                'manpower_cost',
                'finish_good_id',
            ]);
        });

        Schema::table('finish_goods', function (Blueprint $table) {
            $table->dropColumn([
                'material_cost',
                'manpower_cost',
                'total_hpp',
                'hpp_per_unit',
            ]);
        });

        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn('default_manpower_rate');
        });
    }
};
