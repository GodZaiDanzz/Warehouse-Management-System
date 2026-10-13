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
        Schema::create('daily_stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index()->comment('Tanggal Opname Harian');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('initial_stock')->default(0)->comment('Stok Awal Pagi (Rolling dari hari sebelumnya atau Master Stok)');
            $table->integer('final_stock')->nullable()->comment('Stok Akhir Sore / Penutupan Fisik');
            $table->integer('used_qty')->default(0)->comment('Total Terpakai = initial_stock - final_stock');
            $table->integer('system_out_qty')->default(0)->comment('Total Pengeluaran Sistem (Material Out) pada tanggal ini');
            $table->integer('difference')->default(0)->comment('Selisih = used_qty - system_out_qty');
            $table->string('status', 30)->default('pending')->comment('pending (menunggu input sore), completed (sudah diinput sore), discrepancy (ada selisih), synced (tutup buku & sinkron master)');
            $table->text('notes')->nullable()->comment('Catatan atau keterangan selisih');
            $table->foreignId('counted_by')->nullable()->constrained('users')->onDelete('set null')->comment('Petugas yang melakukan opname');
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null')->comment('Petugas yang menutup buku');
            $table->timestamp('closed_at')->nullable()->comment('Waktu tutup buku dan sinkronisasi ke master');
            $table->timestamps();

            // Unique constraint: Satu item hanya memiliki 1 record opname per hari
            $table->unique(['date', 'item_id'], 'unique_date_item_opname');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_stock_opnames');
    }
};
