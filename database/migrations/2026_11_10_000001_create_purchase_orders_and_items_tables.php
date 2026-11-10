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
        // 1. Tabel Utama Purchase Orders (PO)
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique()->comment('Nomor Purchase Order Resmi');
            $table->date('po_date')->comment('Tanggal Penerbitan PO');
            $table->string('supplier')->comment('Nama Vendor / Supplier Penyedia');
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete()->comment('Proyek Terkait (Opsional)');
            $table->enum('status', ['open', 'partial', 'completed', 'cancelled'])->default('open')->index()->comment('Status Pemenuhan PO');
            $table->date('eta_date')->nullable()->comment('Estimasi Tanggal Kedatangan Barang');
            $table->text('notes')->nullable()->comment('Catatan / Syarat Ketentuan PO');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->comment('Pembuat PO (Procurement)');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'po_date']);
            $table->index('supplier');
        });

        // 2. Tabel Rincian Barang dalam Purchase Order
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->decimal('quantity_ordered', 12, 2)->comment('Kuantitas yang Dipesan');
            $table->decimal('quantity_received', 12, 2)->default(0)->comment('Kuantitas yang Sudah Diterima Gudang');
            $table->string('unit', 50)->default('Pcs')->comment('Satuan Barang');
            $table->decimal('unit_price', 15, 2)->nullable()->default(0)->comment('Harga Satuan (Opsional)');
            $table->text('notes')->nullable()->comment('Catatan Spesifikasi Khusus Item');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['purchase_order_id', 'item_id']);
        });

        // 3. Tambahkan relasi ke tabel material_ins jika belum ada
        Schema::table('material_ins', function (Blueprint $table) {
            if (!Schema::hasColumn('material_ins', 'purchase_order_id')) {
                $table->foreignId('purchase_order_id')->nullable()->after('po_number')->constrained('purchase_orders')->nullOnDelete();
            }
            if (!Schema::hasColumn('material_ins', 'purchase_order_item_id')) {
                $table->foreignId('purchase_order_item_id')->nullable()->after('purchase_order_id')->constrained('purchase_order_items')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_ins', function (Blueprint $table) {
            if (Schema::hasColumn('material_ins', 'purchase_order_item_id')) {
                $table->dropForeign(['purchase_order_item_id']);
                $table->dropColumn('purchase_order_item_id');
            }
            if (Schema::hasColumn('material_ins', 'purchase_order_id')) {
                $table->dropForeign(['purchase_order_id']);
                $table->dropColumn('purchase_order_id');
            }
        });

        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
};

