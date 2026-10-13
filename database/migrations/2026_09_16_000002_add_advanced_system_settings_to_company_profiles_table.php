<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('company_profiles', 'cat_fefo_alert_days')) {
                $table->unsignedInteger('cat_fefo_alert_days')->default(60)->after('cat_shelf_life_months')->comment('Ambang hari peringatan FEFO menjelang expired cat (default 60 hari)');
            }
            if (!Schema::hasColumn('company_profiles', 'date_format')) {
                $table->string('date_format', 30)->default('d/m/Y')->after('cat_fefo_alert_days')->comment('Format penanggalan global sistem');
            }
            if (!Schema::hasColumn('company_profiles', 'time_format')) {
                $table->string('time_format', 30)->default('H:i')->after('date_format')->comment('Format jam/waktu global sistem');
            }
            if (!Schema::hasColumn('company_profiles', 'slow_moving_days')) {
                $table->unsignedInteger('slow_moving_days')->default(90)->after('time_format')->comment('Ambang batas hari kategori Slow Moving');
            }
            if (!Schema::hasColumn('company_profiles', 'dead_stock_days')) {
                $table->unsignedInteger('dead_stock_days')->default(180)->after('slow_moving_days')->comment('Ambang batas hari kategori Dead Stock');
            }
            if (!Schema::hasColumn('company_profiles', 'default_per_page')) {
                $table->unsignedInteger('default_per_page')->default(15)->after('dead_stock_days')->comment('Default jumlah baris data tabel per halaman');
            }
            if (!Schema::hasColumn('company_profiles', 'signer_1_name')) {
                $table->string('signer_1_name', 100)->nullable()->after('default_per_page')->comment('Nama Penandatangan 1 (Dibuat Oleh)');
            }
            if (!Schema::hasColumn('company_profiles', 'signer_1_title')) {
                $table->string('signer_1_title', 100)->nullable()->default('Staff Gudang / Inventory Control')->after('signer_1_name')->comment('Jabatan Penandatangan 1');
            }
            if (!Schema::hasColumn('company_profiles', 'signer_2_name')) {
                $table->string('signer_2_name', 100)->nullable()->after('signer_1_title')->comment('Nama Penandatangan 2 (Diperiksa Oleh)');
            }
            if (!Schema::hasColumn('company_profiles', 'signer_2_title')) {
                $table->string('signer_2_title', 100)->nullable()->default('Supervisor WMS / QC')->after('signer_2_name')->comment('Jabatan Penandatangan 2');
            }
            if (!Schema::hasColumn('company_profiles', 'signer_3_name')) {
                $table->string('signer_3_name', 100)->nullable()->after('signer_2_title')->comment('Nama Penandatangan 3 (Disetujui Oleh)');
            }
            if (!Schema::hasColumn('company_profiles', 'signer_3_title')) {
                $table->string('signer_3_title', 100)->nullable()->default('Kepala Gudang / Project Manager')->after('signer_3_name')->comment('Jabatan Penandatangan 3');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $cols = [
                'cat_fefo_alert_days',
                'date_format',
                'time_format',
                'slow_moving_days',
                'dead_stock_days',
                'default_per_page',
                'signer_1_name',
                'signer_1_title',
                'signer_2_name',
                'signer_2_title',
                'signer_3_name',
                'signer_3_title',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('company_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
