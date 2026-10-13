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
        Schema::create('material_ins', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique()->comment('Nomor Transaksi Penerimaan Material');
            $table->date('date')->comment('Tanggal Penerimaan Barang');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->integer('quantity')->comment('Jumlah Barang Diterima');
            $table->string('unit', 50)->default('Pcs')->comment('Satuan Barang');
            $table->string('supplier')->comment('Nama Vendor / Supplier Penyedia');
            $table->string('po_number')->nullable()->comment('Nomor PO / Surat Jalan Supplier');
            $table->string('received_by')->comment('Nama Petugas Gudang yang Menerima');
            $table->text('notes')->nullable()->comment('Catatan Kondisi / Kelengkapan Material');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_ins');
    }
};
