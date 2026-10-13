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
            if (!Schema::hasColumn('company_profiles', 'auto_backup_enabled')) {
                $table->boolean('auto_backup_enabled')->default(true)->after('sidebar_subtitle')->comment('Status auto backup database (aktif/nonaktif)');
            }
            if (!Schema::hasColumn('company_profiles', 'auto_backup_frequency_value')) {
                $table->unsignedInteger('auto_backup_frequency_value')->default(7)->after('auto_backup_enabled')->comment('Nilai interval auto backup (contoh: 7, 14, 1)');
            }
            if (!Schema::hasColumn('company_profiles', 'auto_backup_frequency_unit')) {
                $table->string('auto_backup_frequency_unit', 20)->default('days')->after('auto_backup_frequency_value')->comment('Satuan interval: days atau months');
            }
            if (!Schema::hasColumn('company_profiles', 'auto_backup_max_files')) {
                $table->unsignedInteger('auto_backup_max_files')->default(10)->after('auto_backup_frequency_unit')->comment('Batas maksimal retensi file backup di server');
            }
            if (!Schema::hasColumn('company_profiles', 'last_backup_at')) {
                $table->timestamp('last_backup_at')->nullable()->after('auto_backup_max_files')->comment('Waktu terakhir kali backup berhasil dibuat');
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
                'auto_backup_enabled',
                'auto_backup_frequency_value',
                'auto_backup_frequency_unit',
                'auto_backup_max_files',
                'last_backup_at',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('company_profiles', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
