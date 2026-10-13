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
        Schema::table('material_rejects', function (Blueprint $table) {
            $table->index('date', 'material_rejects_date_index');
            $table->index('disposition_status', 'material_rejects_disposition_status_index');
            $table->index('supplier', 'material_rejects_supplier_index');
            $table->index(['date', 'disposition_status'], 'material_rejects_date_status_index');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index('created_at', 'activity_logs_created_at_index');
            $table->index('module', 'activity_logs_module_index');
            $table->index(['module', 'created_at'], 'activity_logs_module_created_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_rejects', function (Blueprint $table) {
            $table->dropIndex('material_rejects_date_index');
            $table->dropIndex('material_rejects_disposition_status_index');
            $table->dropIndex('material_rejects_supplier_index');
            $table->dropIndex('material_rejects_date_status_index');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_created_at_index');
            $table->dropIndex('activity_logs_module_index');
            $table->dropIndex('activity_logs_module_created_index');
        });
    }
};
