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
        Schema::table('material_ins', function (Blueprint $table) {
            $table->string('qc_status', 30)->default('approved')->after('notes')->comment('pending_qc, approved, rejected');
            $table->foreignId('qc_by')->nullable()->after('qc_status')->constrained('users')->onDelete('set null');
            $table->timestamp('qc_at')->nullable()->after('qc_by');
            $table->text('qc_notes')->nullable()->after('qc_at');
            $table->date('manufacturing_date')->nullable()->after('qc_notes')->comment('Tanggal pembuatan barang/cat');
            $table->integer('wholesale_qty')->nullable()->after('quantity')->comment('Jumlah input dalam satuan grosir');
            $table->string('wholesale_unit', 50)->nullable()->after('wholesale_qty')->comment('Satuan grosir yang digunakan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_ins', function (Blueprint $table) {
            $table->dropForeign(['qc_by']);
            $table->dropColumn([
                'qc_status',
                'qc_by',
                'qc_at',
                'qc_notes',
                'manufacturing_date',
                'wholesale_qty',
                'wholesale_unit',
            ]);
        });
    }
};
