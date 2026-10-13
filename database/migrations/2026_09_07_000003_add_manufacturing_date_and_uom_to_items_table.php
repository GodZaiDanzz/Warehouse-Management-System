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
        Schema::table('items', function (Blueprint $table) {
            $table->date('manufacturing_date')->nullable()->after('min_stock')->comment('Tanggal Pembuatan (wajib untuk Cat / Painting)');
            $table->string('wholesale_unit', 50)->nullable()->after('manufacturing_date')->comment('Satuan Grosir (Box, Dus, Lusin, dll)');
            $table->unsignedInteger('conversion_factor')->default(1)->after('wholesale_unit')->comment('Isi satuan terkecil per 1 satuan grosir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['manufacturing_date', 'wholesale_unit', 'conversion_factor']);
        });
    }
};
