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
        Schema::table('daily_stock_opnames', function (Blueprint $table) {
            $table->string('adjustment_status', 30)->default('none')->after('status')->comment('none, pending, approved, rejected');
            $table->foreignId('adjusted_by')->nullable()->after('adjustment_status')->constrained('users')->onDelete('set null')->comment('Web Admin yang menyetujui penyesuaian');
            $table->timestamp('adjusted_at')->nullable()->after('adjusted_by')->comment('Waktu persetujuan penyesuaian');
            $table->text('adjustment_notes')->nullable()->after('adjusted_at')->comment('Catatan/berita acara penyesuaian stok master');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_stock_opnames', function (Blueprint $table) {
            $table->dropForeign(['adjusted_by']);
            $table->dropColumn(['adjustment_status', 'adjusted_by', 'adjusted_at', 'adjustment_notes']);
        });
    }
};
