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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->nullable()->comment('Kode Proyek / Referensi');
            $table->string('name')->comment('Nama Proyek / Pekerjaan');
            $table->string('client')->nullable()->comment('Klien / Owner Proyek');
            $table->string('location')->nullable()->comment('Lokasi Proyek');
            $table->enum('status', ['Planning', 'Ongoing', 'Completed', 'On Hold'])->default('Ongoing');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
