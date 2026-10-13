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
        Schema::table('material_outs', function (Blueprint $table) {
            $table->integer('wholesale_qty')->nullable()->after('quantity')->comment('Jumlah input dalam satuan grosir');
            $table->string('wholesale_unit', 50)->nullable()->after('wholesale_qty')->comment('Satuan grosir yang digunakan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_outs', function (Blueprint $table) {
            $table->dropColumn(['wholesale_qty', 'wholesale_unit']);
        });
    }
};
