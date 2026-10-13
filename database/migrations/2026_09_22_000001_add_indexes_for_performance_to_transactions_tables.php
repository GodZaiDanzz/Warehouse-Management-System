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
        Schema::table('material_outs', function (Blueprint $table) {
            $table->index('date', 'material_outs_date_index');
            $table->index('project_id', 'material_outs_project_id_index');
            $table->index('deleted_at', 'material_outs_deleted_at_index');
        });

        Schema::table('material_ins', function (Blueprint $table) {
            $table->index('date', 'material_ins_date_index');
            $table->index('qc_status', 'material_ins_qc_status_index');
            $table->index('deleted_at', 'material_ins_deleted_at_index');
        });

        Schema::table('stock_logs', function (Blueprint $table) {
            $table->index('type', 'stock_logs_type_index');
            $table->index('created_at', 'stock_logs_created_at_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('material_outs', function (Blueprint $table) {
            $table->dropIndex('material_outs_date_index');
            $table->dropIndex('material_outs_project_id_index');
            $table->dropIndex('material_outs_deleted_at_index');
        });

        Schema::table('material_ins', function (Blueprint $table) {
            $table->dropIndex('material_ins_date_index');
            $table->dropIndex('material_ins_qc_status_index');
            $table->dropIndex('material_ins_deleted_at_index');
        });

        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropIndex('stock_logs_type_index');
            $table->dropIndex('stock_logs_created_at_index');
        });
    }
};
