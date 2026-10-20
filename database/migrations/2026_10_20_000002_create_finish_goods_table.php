<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finish_goods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('item_name')->comment('Nama Barang Jadi / Produk Fabrikasi');
            $table->string('item_code', 100)->nullable()->comment('Nomor Tag / Spool ID / Drawing No');
            $table->decimal('quantity', 15, 2)->default(1)->comment('Jumlah unit barang jadi');
            $table->string('unit', 50)->default('PCS')->comment('Satuan Barang Jadi');
            $table->date('production_date')->nullable()->comment('Tanggal Selesai Produksi');
            $table->enum('qc_status', ['Pending', 'Passed', 'Rejected'])->default('Pending')->comment('Status Inspeksi QC');
            $table->enum('status', ['In Storage', 'Delivered'])->default('In Storage')->comment('Status Penyimpanan / Pengiriman');
            $table->date('delivery_date')->nullable()->comment('Tanggal Pengiriman / Serah Terima');
            $table->string('delivery_order_number', 100)->nullable()->comment('Nomor Surat Jalan / Delivery Order');
            $table->text('notes')->nullable()->comment('Catatan Spesifikasi / Keterangan');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['project_id', 'status']);
            $table->index(['qc_status']);
            $table->index(['item_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finish_goods');
    }
};
