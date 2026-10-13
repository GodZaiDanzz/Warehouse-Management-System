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
        Schema::create('material_outs', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique()->comment('Nomor Transaksi Pengeluaran Material');
            $table->date('date')->comment('Tanggal Keluar');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('quantity')->comment('Jumlah Barang Keluar');
            $table->string('unit', 50)->default('Pcs')->comment('Satuan Barang');
            $table->string('project')->comment('Nama / Lokasi Project');
            $table->string('pic')->comment('PIC - Orang yang melakukan request untuk mengambil material');
            $table->text('notes')->nullable()->comment('Catatan Tambahan');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_outs');
    }
};
