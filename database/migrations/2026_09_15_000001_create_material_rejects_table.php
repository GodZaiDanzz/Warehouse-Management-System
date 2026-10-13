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
        Schema::create('material_rejects', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 50)->unique();
            $table->foreignId('material_in_id')->nullable()->constrained('material_ins')->nullOnDelete();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->date('date');
            $table->string('supplier', 255);
            $table->string('po_number', 100)->nullable();
            $table->integer('quantity')->default(1);
            $table->string('unit', 50)->default('PCS');
            $table->string('damage_type', 100)->comment('Cacat Fisik / Pecah, Tidak Sesuai Spek, Kadaluarsa / Expired, Kemasan Bocor / Rusak, Karat / Korosi, Lainnya');
            $table->string('disposition', 50)->default('karantina')->comment('karantina, retur_supplier, replacement, scrap');
            $table->string('disposition_status', 50)->default('pending')->comment('pending, in_progress, completed');
            $table->text('description')->nullable()->comment('Deskripsi detail kerusakan / cacat');
            $table->string('evidence_photo', 255)->nullable()->comment('Foto bukti kerusakan');
            $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('resolution_notes')->nullable()->comment('Catatan penyelesaian retur / pergantian barang');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_rejects');
    }
};
